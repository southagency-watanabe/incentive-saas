<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// ログインチェック（管理者もユーザーもOK）
requireLogin();

$pdo = getDB();
$tenant_id = $_SESSION['tenant_id'];

try {
    // フィルタパラメータの取得
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    $member_ids = isset($_GET['member_ids']) ? explode(',', $_GET['member_ids']) : [];
    $team_ids = isset($_GET['team_ids']) ? explode(',', $_GET['team_ids']) : [];
    $product_ids = isset($_GET['product_ids']) ? explode(',', $_GET['product_ids']) : [];
    $search_text = $_GET['search_text'] ?? '';

    // デフォルト期間: 今月
    if (!$start_date || !$end_date) {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
    }

    // チームと個人フィルタの統合ロジック
    $final_member_ids = [];
    $team_member_ids = [];

    if (!empty($team_ids)) {
        // 選択されたチームに所属するメンバーIDを取得
        $team_placeholders = implode(',', array_fill(0, count($team_ids), '?'));
        $stmt = $pdo->prepare("
            SELECT DISTINCT member_id
            FROM members
            WHERE tenant_id = ? AND team_id IN ($team_placeholders)
        ");
        $params = array_merge([$tenant_id], $team_ids);
        $stmt->execute($params);
        $team_member_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $final_member_ids = array_merge($final_member_ids, $team_member_ids);
    }

    if (!empty($member_ids)) {
        // 選択された個人のうち、チーム外のメンバーのみを追加
        foreach ($member_ids as $member_id) {
            if (!in_array($member_id, $team_member_ids)) {
                $final_member_ids[] = $member_id;
            }
        }
    }

    // 最終的なメンバーIDリストを使用（重複削除）
    $final_member_ids = array_unique($final_member_ids);

    // 以降のクエリでは $final_member_ids を使用し、team_ids は使わない
    $member_ids = $final_member_ids;
    $team_ids = []; // チームフィルタは使わない（すでにメンバーIDに変換済み）

    // 前期間の計算
    $period_days = (strtotime($end_date) - strtotime($start_date)) / 86400 + 1;
    $prev_end_date = date('Y-m-d', strtotime($start_date) - 86400);
    $prev_start_date = date('Y-m-d', strtotime($prev_end_date) - ($period_days - 1) * 86400);

    // WHERE句の構築（team_idsの条件は含めない - JOINの有無で分離）
    function buildWhereClause($tenant_id, $start_date, $end_date, $member_ids, $product_ids, $search_text, $table_alias = 'sr')
    {
        $where = ["$table_alias.tenant_id = :tenant_id"];
        $where[] = "$table_alias.date BETWEEN :start_date AND :end_date";
        $where[] = "$table_alias.approval_status = '承認済み'";

        if (!empty($member_ids)) {
            $placeholders = implode(',', array_map(fn($i) => ":member_id_$i", array_keys($member_ids)));
            $where[] = "$table_alias.member_id IN ($placeholders)";
        }

        if (!empty($product_ids)) {
            $placeholders = implode(',', array_map(fn($i) => ":product_id_$i", array_keys($product_ids)));
            $where[] = "$table_alias.product_id IN ($placeholders)";
        }

        return implode(' AND ', $where);
    }

    // チームフィルタ条件の構築
    function buildTeamFilter($team_ids) {
        if (empty($team_ids)) {
            return "";
        }
        $placeholders = implode(',', array_map(fn($i) => ":team_id_$i", array_keys($team_ids)));
        return " AND m.team_id IN ($placeholders)";
    }

    // パラメータのバインド
    function bindParams($stmt, $tenant_id, $start_date, $end_date, $member_ids, $team_ids, $product_ids)
    {
        $stmt->bindValue(':tenant_id', $tenant_id);
        $stmt->bindValue(':start_date', $start_date);
        $stmt->bindValue(':end_date', $end_date);

        foreach ($member_ids as $i => $member_id) {
            $stmt->bindValue(":member_id_$i", $member_id);
        }

        foreach ($team_ids as $i => $team_id) {
            $stmt->bindValue(":team_id_$i", $team_id);
        }

        foreach ($product_ids as $i => $product_id) {
            $stmt->bindValue(":product_id_$i", $product_id);
        }
    }

    // ===== スコアカード: 商品別売上金額 =====
    $where_current = buildWhereClause($tenant_id, $start_date, $end_date, $member_ids, $product_ids, $search_text);
    $where_prev = buildWhereClause($tenant_id, $prev_start_date, $prev_end_date, $member_ids, $product_ids, $search_text);
    $team_filter = buildTeamFilter($team_ids);

    $member_join = !empty($team_ids) ? "INNER JOIN members m ON sr.tenant_id = m.tenant_id AND sr.member_id = m.member_id" : "";

    $sql_current = "
        SELECT SUM(sr.quantity * sr.unit_price) as total_sales
        FROM sales_records sr
        $member_join
        WHERE $where_current $team_filter
    ";

    $sql_prev = "
        SELECT SUM(sr.quantity * sr.unit_price) as total_sales
        FROM sales_records sr
        $member_join
        WHERE $where_prev $team_filter
    ";

    $stmt = $pdo->prepare($sql_current);
    bindParams($stmt, $tenant_id, $start_date, $end_date, $member_ids, $team_ids, $product_ids);
    $stmt->execute();
    $current_sales = $stmt->fetch()['total_sales'] ?? 0;

    $stmt = $pdo->prepare($sql_prev);
    bindParams($stmt, $tenant_id, $prev_start_date, $prev_end_date, $member_ids, $team_ids, $product_ids);
    $stmt->execute();
    $prev_sales = $stmt->fetch()['total_sales'] ?? 0;

    $sales_diff = $current_sales - $prev_sales;
    $sales_diff_percent = $prev_sales > 0 ? ($sales_diff / $prev_sales) * 100 : 0;

    // ===== スコアカード: 商品別売上件数 =====
    $sql_current_count = "
        SELECT COUNT(*) as total_count
        FROM sales_records sr
        $member_join
        WHERE $where_current $team_filter
    ";

    $sql_prev_count = "
        SELECT COUNT(*) as total_count
        FROM sales_records sr
        $member_join
        WHERE $where_prev $team_filter
    ";

    $stmt = $pdo->prepare($sql_current_count);
    bindParams($stmt, $tenant_id, $start_date, $end_date, $member_ids, $team_ids, $product_ids);
    $stmt->execute();
    $current_count = $stmt->fetch()['total_count'] ?? 0;

    $stmt = $pdo->prepare($sql_prev_count);
    bindParams($stmt, $tenant_id, $prev_start_date, $prev_end_date, $member_ids, $team_ids, $product_ids);
    $stmt->execute();
    $prev_count = $stmt->fetch()['total_count'] ?? 0;

    $count_diff = $current_count - $prev_count;
    $count_diff_percent = $prev_count > 0 ? ($count_diff / $prev_count) * 100 : 0;

    // ===== スコアカード: 商品別粗利益 =====
    $profit_joins = "INNER JOIN products p ON sr.tenant_id = p.tenant_id AND sr.product_id = p.product_id";
    if (!empty($team_ids)) {
        $profit_joins .= " INNER JOIN members m ON sr.tenant_id = m.tenant_id AND sr.member_id = m.member_id";
    }

    $sql_current_profit = "
        SELECT SUM(sr.quantity * (sr.unit_price - COALESCE(p.cost, 0))) as total_profit
        FROM sales_records sr
        $profit_joins
        WHERE $where_current $team_filter
    ";

    $sql_prev_profit = "
        SELECT SUM(sr.quantity * (sr.unit_price - COALESCE(p.cost, 0))) as total_profit
        FROM sales_records sr
        $profit_joins
        WHERE $where_prev $team_filter
    ";

    $stmt = $pdo->prepare($sql_current_profit);
    bindParams($stmt, $tenant_id, $start_date, $end_date, $member_ids, $team_ids, $product_ids);
    $stmt->execute();
    $current_profit = $stmt->fetch()['total_profit'] ?? 0;

    $stmt = $pdo->prepare($sql_prev_profit);
    bindParams($stmt, $tenant_id, $prev_start_date, $prev_end_date, $member_ids, $team_ids, $product_ids);
    $stmt->execute();
    $prev_profit = $stmt->fetch()['total_profit'] ?? 0;

    $profit_diff = $current_profit - $prev_profit;
    $profit_diff_percent = $prev_profit > 0 ? ($profit_diff / $prev_profit) * 100 : 0;

    // ===== 期間推移データ =====
    $granularity = $_GET['granularity'] ?? 'monthly'; // daily, weekly, monthly, quarterly, yearly

    $date_format = match ($granularity) {
        'daily' => '%Y-%m-%d',
        'weekly' => '%Y-%u', // 週番号（月曜始まり）
        'monthly' => '%Y-%m',
        'quarterly' => 'CONCAT(YEAR(sr.date), "-Q", QUARTER(sr.date))',
        'yearly' => '%Y',
        default => '%Y-%m'
    };

    $trend_joins = "INNER JOIN products p ON sr.tenant_id = p.tenant_id AND sr.product_id = p.product_id";
    if (!empty($team_ids)) {
        $trend_joins .= " INNER JOIN members m ON sr.tenant_id = m.tenant_id AND sr.member_id = m.member_id";
    }

    if ($granularity === 'quarterly') {
        $sql_trend = "
            SELECT
                CONCAT(YEAR(sr.date), '-Q', QUARTER(sr.date)) as period,
                SUM(sr.quantity * sr.unit_price) as sales,
                SUM(sr.quantity * (sr.unit_price - COALESCE(p.cost, 0))) as profit
            FROM sales_records sr
            $trend_joins
            WHERE $where_current $team_filter
            GROUP BY period
            ORDER BY period ASC
        ";
    } else {
        $sql_trend = "
            SELECT
                DATE_FORMAT(sr.date, '$date_format') as period,
                SUM(sr.quantity * sr.unit_price) as sales,
                SUM(sr.quantity * (sr.unit_price - COALESCE(p.cost, 0))) as profit
            FROM sales_records sr
            $trend_joins
            WHERE $where_current $team_filter
            GROUP BY period
            ORDER BY period ASC
        ";
    }

    $stmt = $pdo->prepare($sql_trend);
    bindParams($stmt, $tenant_id, $start_date, $end_date, $member_ids, $team_ids, $product_ids);
    $stmt->execute();
    $trend_data = $stmt->fetchAll();

    // 週次の場合は週番号を日付に変換
    if ($granularity === 'weekly') {
        foreach ($trend_data as &$row) {
            list($year, $week) = explode('-', $row['period']);
            $dto = new DateTime();
            $dto->setISODate($year, $week);
            $row['period'] = $dto->format('Y-m-d'); // 週番号を削除
        }
    }

    // ===== 商品別売上/粗利 =====
    $product_joins = "INNER JOIN products p ON sr.tenant_id = p.tenant_id AND sr.product_id = p.product_id";
    if (!empty($team_ids)) {
        $product_joins .= " INNER JOIN members m ON sr.tenant_id = m.tenant_id AND sr.member_id = m.member_id";
    }

    $sql_products = "
        SELECT
            sr.product_id,
            p.product_name,
            SUM(sr.quantity * sr.unit_price) as sales,
            SUM(sr.quantity * (sr.unit_price - COALESCE(p.cost, 0))) as profit,
            SUM(sr.quantity) as quantity,
            COUNT(*) as count
        FROM sales_records sr
        $product_joins
        WHERE $where_current $team_filter
        GROUP BY sr.product_id, p.product_name
        ORDER BY sales DESC
    ";

    $stmt = $pdo->prepare($sql_products);
    bindParams($stmt, $tenant_id, $start_date, $end_date, $member_ids, $team_ids, $product_ids);
    $stmt->execute();
    $product_data = $stmt->fetchAll();

    // ===== 個人ランキング（売上・ポイント・前期間比較込み）TOP10 =====
    $where_ranking = "sr.tenant_id = :tenant_id AND sr.date BETWEEN :start_date AND :end_date AND sr.approval_status = '承認済み'";
    $where_prev_ranking = "sr.tenant_id = :tenant_id AND sr.date BETWEEN :prev_start_date AND :prev_end_date AND sr.approval_status = '承認済み'";

    // チームフィルタがある場合は適用
    if (!empty($team_ids)) {
        $team_placeholders = implode(',', array_map(fn($i) => ":team_id_$i", array_keys($team_ids)));
        $where_ranking .= " AND m.team_id IN ($team_placeholders)";
        $where_prev_ranking .= " AND m.team_id IN ($team_placeholders)";
    }

    // メンバーフィルタがある場合は適用
    if (!empty($member_ids)) {
        $member_placeholders = implode(',', array_map(fn($i) => ":rank_member_id_$i", array_keys($member_ids)));
        $where_ranking .= " AND sr.member_id IN ($member_placeholders)";
        $where_prev_ranking .= " AND sr.member_id IN ($member_placeholders)";
    }

    // 現在期間の個人ランキング
    $sql_member_ranking = "
        SELECT
            m.member_id,
            m.name as member_name,
            t.team_name,
            SUM(sr.quantity * sr.unit_price) as total_sales,
            SUM(sr.final_point) as total_points
        FROM sales_records sr
        INNER JOIN members m ON sr.tenant_id = m.tenant_id AND sr.member_id = m.member_id
        LEFT JOIN teams t ON m.tenant_id = t.tenant_id AND m.team_id = t.team_id
        WHERE $where_ranking
        GROUP BY m.member_id, m.name, t.team_name
        ORDER BY total_sales DESC
        LIMIT 10
    ";

    $stmt = $pdo->prepare($sql_member_ranking);
    $stmt->bindValue(':tenant_id', $tenant_id);
    $stmt->bindValue(':start_date', $start_date);
    $stmt->bindValue(':end_date', $end_date);
    foreach ($team_ids as $i => $team_id) {
        $stmt->bindValue(":team_id_$i", $team_id);
    }
    foreach ($member_ids as $i => $member_id) {
        $stmt->bindValue(":rank_member_id_$i", $member_id);
    }
    $stmt->execute();
    $member_ranking_current = $stmt->fetchAll();

    // 前期間の個人ランキングデータ
    $sql_member_ranking_prev = "
        SELECT
            m.member_id,
            SUM(sr.quantity * sr.unit_price) as total_sales,
            SUM(sr.final_point) as total_points
        FROM sales_records sr
        INNER JOIN members m ON sr.tenant_id = m.tenant_id AND sr.member_id = m.member_id
        WHERE $where_prev_ranking
        GROUP BY m.member_id
    ";

    $stmt = $pdo->prepare($sql_member_ranking_prev);
    $stmt->bindValue(':tenant_id', $tenant_id);
    $stmt->bindValue(':prev_start_date', $prev_start_date);
    $stmt->bindValue(':prev_end_date', $prev_end_date);
    foreach ($team_ids as $i => $team_id) {
        $stmt->bindValue(":team_id_$i", $team_id);
    }
    foreach ($member_ids as $i => $member_id) {
        $stmt->bindValue(":rank_member_id_$i", $member_id);
    }
    $stmt->execute();
    $member_ranking_prev_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 前期間データをmember_idでマッピング
    $prev_data_map = [];
    foreach ($member_ranking_prev_data as $prev) {
        $prev_data_map[$prev['member_id']] = $prev;
    }

    // 現在期間ランキングに前期間比較を追加
    $member_ranking = [];
    foreach ($member_ranking_current as $member) {
        $prev = $prev_data_map[$member['member_id']] ?? null;
        $prev_sales = $prev ? (float)$prev['total_sales'] : 0;
        $prev_points = $prev ? (float)$prev['total_points'] : 0;

        $sales_diff = (float)$member['total_sales'] - $prev_sales;
        $points_diff = (float)$member['total_points'] - $prev_points;

        $sales_diff_percent = $prev_sales > 0 ? ($sales_diff / $prev_sales) * 100 : 0;
        $points_diff_percent = $prev_points > 0 ? ($points_diff / $prev_points) * 100 : 0;

        $member_ranking[] = [
            'member_id' => $member['member_id'],
            'member_name' => $member['member_name'],
            'team_name' => $member['team_name'],
            'total_sales' => (float)$member['total_sales'],
            'total_points' => (float)$member['total_points'],
            'prev_sales' => $prev_sales,
            'prev_points' => $prev_points,
            'sales_diff' => $sales_diff,
            'points_diff' => $points_diff,
            'sales_diff_percent' => $sales_diff_percent,
            'points_diff_percent' => $points_diff_percent
        ];
    }

    // ===== チームランキング（売上・ポイント・前期間比較込み）TOP10 =====
    $where_team_ranking = "sr.tenant_id = :tenant_id AND sr.date BETWEEN :start_date AND :end_date AND sr.approval_status = '承認済み'";
    $where_team_prev_ranking = "sr.tenant_id = :tenant_id AND sr.date BETWEEN :prev_start_date AND :prev_end_date AND sr.approval_status = '承認済み'";

    // チームフィルタがある場合は適用
    if (!empty($team_ids)) {
        $team_placeholders = implode(',', array_map(fn($i) => ":team_id_team_$i", array_keys($team_ids)));
        $where_team_ranking .= " AND t.team_id IN ($team_placeholders)";
        $where_team_prev_ranking .= " AND t.team_id IN ($team_placeholders)";
    }

    // メンバーフィルタがある場合は適用（そのメンバーが所属するチームのみ）
    if (!empty($member_ids)) {
        $member_placeholders = implode(',', array_map(fn($i) => ":rank_team_member_id_$i", array_keys($member_ids)));
        $where_team_ranking .= " AND sr.member_id IN ($member_placeholders)";
        $where_team_prev_ranking .= " AND sr.member_id IN ($member_placeholders)";
    }

    // 現在期間のチームランキング
    $sql_team_ranking = "
        SELECT
            t.team_id,
            t.team_name,
            SUM(sr.quantity * sr.unit_price) as total_sales,
            SUM(sr.final_point) as total_points
        FROM sales_records sr
        INNER JOIN members m ON sr.tenant_id = m.tenant_id AND sr.member_id = m.member_id
        INNER JOIN teams t ON m.tenant_id = t.tenant_id AND m.team_id = t.team_id
        WHERE $where_team_ranking
        GROUP BY t.team_id, t.team_name
        ORDER BY total_sales DESC
        LIMIT 10
    ";

    $stmt = $pdo->prepare($sql_team_ranking);
    $stmt->bindValue(':tenant_id', $tenant_id);
    $stmt->bindValue(':start_date', $start_date);
    $stmt->bindValue(':end_date', $end_date);
    foreach ($team_ids as $i => $team_id) {
        $stmt->bindValue(":team_id_team_$i", $team_id);
    }
    foreach ($member_ids as $i => $member_id) {
        $stmt->bindValue(":rank_team_member_id_$i", $member_id);
    }
    $stmt->execute();
    $team_ranking_current = $stmt->fetchAll();

    // 前期間のチームランキングデータ
    $sql_team_ranking_prev = "
        SELECT
            t.team_id,
            SUM(sr.quantity * sr.unit_price) as total_sales,
            SUM(sr.final_point) as total_points
        FROM sales_records sr
        INNER JOIN members m ON sr.tenant_id = m.tenant_id AND sr.member_id = m.member_id
        INNER JOIN teams t ON m.tenant_id = t.tenant_id AND m.team_id = t.team_id
        WHERE $where_team_prev_ranking
        GROUP BY t.team_id
    ";

    $stmt = $pdo->prepare($sql_team_ranking_prev);
    $stmt->bindValue(':tenant_id', $tenant_id);
    $stmt->bindValue(':prev_start_date', $prev_start_date);
    $stmt->bindValue(':prev_end_date', $prev_end_date);
    foreach ($team_ids as $i => $team_id) {
        $stmt->bindValue(":team_id_team_$i", $team_id);
    }
    foreach ($member_ids as $i => $member_id) {
        $stmt->bindValue(":rank_team_member_id_$i", $member_id);
    }
    $stmt->execute();
    $team_ranking_prev_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 前期間データをteam_idでマッピング
    $team_prev_data_map = [];
    foreach ($team_ranking_prev_data as $prev) {
        $team_prev_data_map[$prev['team_id']] = $prev;
    }

    // 現在期間ランキングに前期間比較を追加
    $team_ranking = [];
    foreach ($team_ranking_current as $team) {
        $prev = $team_prev_data_map[$team['team_id']] ?? null;
        $prev_sales = $prev ? (float)$prev['total_sales'] : 0;
        $prev_points = $prev ? (float)$prev['total_points'] : 0;

        $sales_diff = (float)$team['total_sales'] - $prev_sales;
        $points_diff = (float)$team['total_points'] - $prev_points;

        $sales_diff_percent = $prev_sales > 0 ? ($sales_diff / $prev_sales) * 100 : 0;
        $points_diff_percent = $prev_points > 0 ? ($points_diff / $prev_points) * 100 : 0;

        $team_ranking[] = [
            'team_id' => $team['team_id'],
            'team_name' => $team['team_name'],
            'total_sales' => (float)$team['total_sales'],
            'total_points' => (float)$team['total_points'],
            'prev_sales' => $prev_sales,
            'prev_points' => $prev_points,
            'sales_diff' => $sales_diff,
            'points_diff' => $points_diff,
            'sales_diff_percent' => $sales_diff_percent,
            'points_diff_percent' => $points_diff_percent
        ];
    }

    // レスポンス
    echo json_encode([
        'success' => true,
        'score_cards' => [
            'sales' => [
                'current' => (float)$current_sales,
                'previous' => (float)$prev_sales,
                'diff' => (float)$sales_diff,
                'diff_percent' => (float)$sales_diff_percent
            ],
            'count' => [
                'current' => (int)$current_count,
                'previous' => (int)$prev_count,
                'diff' => (int)$count_diff,
                'diff_percent' => (float)$count_diff_percent
            ],
            'profit' => [
                'current' => (float)$current_profit,
                'previous' => (float)$prev_profit,
                'diff' => (float)$profit_diff,
                'diff_percent' => (float)$profit_diff_percent
            ]
        ],
        'trend' => $trend_data,
        'products' => $product_data,
        'rankings' => [
            'members' => $member_ranking,
            'teams' => $team_ranking
        ],
        'filters' => [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'prev_start_date' => $prev_start_date,
            'prev_end_date' => $prev_end_date
        ]
    ]);
} catch (Exception $e) {
    error_log('Dashboard API error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'サーバーエラーが発生しました。',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
