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

    // 前期間の計算
    $period_days = (strtotime($end_date) - strtotime($start_date)) / 86400 + 1;
    $prev_end_date = date('Y-m-d', strtotime($start_date) - 86400);
    $prev_start_date = date('Y-m-d', strtotime($prev_end_date) - ($period_days - 1) * 86400);

    // WHERE句の構築
    function buildWhereClause($tenant_id, $start_date, $end_date, $member_ids, $team_ids, $product_ids, $search_text, $table_alias = 'sr')
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

    // パラメータのバインド
    function bindParams($stmt, $tenant_id, $start_date, $end_date, $member_ids, $team_ids, $product_ids)
    {
        $stmt->bindValue(':tenant_id', $tenant_id);
        $stmt->bindValue(':start_date', $start_date);
        $stmt->bindValue(':end_date', $end_date);

        foreach ($member_ids as $i => $member_id) {
            $stmt->bindValue(":member_id_$i", $member_id);
        }

        foreach ($product_ids as $i => $product_id) {
            $stmt->bindValue(":product_id_$i", $product_id);
        }
    }

    // ===== スコアカード: 商品別売上金額 =====
    $where_current = buildWhereClause($tenant_id, $start_date, $end_date, $member_ids, $team_ids, $product_ids, $search_text);
    $where_prev = buildWhereClause($tenant_id, $prev_start_date, $prev_end_date, $member_ids, $team_ids, $product_ids, $search_text);

    $sql_current = "
        SELECT SUM(sr.quantity * sr.unit_price) as total_sales
        FROM sales_records sr
        WHERE $where_current
    ";

    $sql_prev = "
        SELECT SUM(sr.quantity * sr.unit_price) as total_sales
        FROM sales_records sr
        WHERE $where_prev
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
        WHERE $where_current
    ";

    $sql_prev_count = "
        SELECT COUNT(*) as total_count
        FROM sales_records sr
        WHERE $where_prev
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
    $sql_current_profit = "
        SELECT SUM(sr.quantity * (sr.unit_price - COALESCE(p.cost, 0))) as total_profit
        FROM sales_records sr
        INNER JOIN products p ON sr.tenant_id = p.tenant_id AND sr.product_id = p.product_id
        WHERE $where_current
    ";

    $sql_prev_profit = "
        SELECT SUM(sr.quantity * (sr.unit_price - COALESCE(p.cost, 0))) as total_profit
        FROM sales_records sr
        INNER JOIN products p ON sr.tenant_id = p.tenant_id AND sr.product_id = p.product_id
        WHERE $where_prev
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

    if ($granularity === 'quarterly') {
        $sql_trend = "
            SELECT
                CONCAT(YEAR(sr.date), '-Q', QUARTER(sr.date)) as period,
                SUM(sr.quantity * sr.unit_price) as sales,
                SUM(sr.quantity * (sr.unit_price - COALESCE(p.cost, 0))) as profit
            FROM sales_records sr
            INNER JOIN products p ON sr.tenant_id = p.tenant_id AND sr.product_id = p.product_id
            WHERE $where_current
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
            INNER JOIN products p ON sr.tenant_id = p.tenant_id AND sr.product_id = p.product_id
            WHERE $where_current
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
            $row['period'] = $dto->format('Y-m-d') . ' (W' . $week . ')';
        }
    }

    // ===== 商品別売上/粗利 =====
    $sql_products = "
        SELECT
            sr.product_id,
            p.product_name,
            SUM(sr.quantity * sr.unit_price) as sales,
            SUM(sr.quantity * (sr.unit_price - COALESCE(p.cost, 0))) as profit,
            SUM(sr.quantity) as quantity,
            COUNT(*) as count
        FROM sales_records sr
        INNER JOIN products p ON sr.tenant_id = p.tenant_id AND sr.product_id = p.product_id
        WHERE $where_current
        GROUP BY sr.product_id, p.product_name
        ORDER BY sales DESC
    ";

    $stmt = $pdo->prepare($sql_products);
    bindParams($stmt, $tenant_id, $start_date, $end_date, $member_ids, $team_ids, $product_ids);
    $stmt->execute();
    $product_data = $stmt->fetchAll();

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
        'filters' => [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'prev_start_date' => $prev_start_date,
            'prev_end_date' => $prev_end_date
        ]
    ]);
} catch (PDOException $e) {
    error_log('Dashboard API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'サーバーエラーが発生しました。',
        'debug' => $e->getMessage()
    ]);
}
