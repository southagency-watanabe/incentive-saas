<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// ログインチェック（管理者もユーザーもOK）
requireLogin();

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$tenant_id = $_SESSION['tenant_id'];

try {
  switch ($method) {
    case 'GET':
      // イベントプレビュー
      if (isset($_GET['preview']) && $_GET['preview'] === 'true') {
        // 管理者のみ
        if ($_SESSION['role'] !== 'admin') {
          http_response_code(403);
          echo json_encode(['success' => false, 'message' => 'この操作は管理者のみ実行できます。']);
          exit;
        }

        $date = $_GET['date'] ?? null;
        $product_id = $_GET['product_id'] ?? null;

        if (empty($date) || empty($product_id)) {
          echo json_encode([
            'success' => true,
            'event_multiplier' => 1.0,
            'applied_event_id' => null,
            'applied_event_name' => null,
            'base_point' => 0
          ]);
          exit;
        }

        // 商品情報取得
        $stmt = $pdo->prepare("
          SELECT product_id, product_name, point, price
          FROM products
          WHERE tenant_id = :tenant_id AND product_id = :product_id AND status = '有効'
        ");
        $stmt->execute([
          'tenant_id' => $tenant_id,
          'product_id' => $product_id
        ]);
        $product = $stmt->fetch();

        if (!$product) {
          echo json_encode([
            'success' => true,
            'event_multiplier' => 1.0,
            'applied_event_id' => null,
            'applied_event_name' => null,
            'base_point' => 0
          ]);
          exit;
        }

        // 基準付与pt（数量は1として計算）
        $base_point = $product['point'];

        // イベント倍率計算（該当する有効イベントの最大倍率）
        $event_multiplier = 1.0;
        $applied_event_id = null;
        $applied_event_name = null;

        // 有効なイベントを全て取得
        $stmt = $pdo->prepare("
          SELECT event_id, event_name, repeat_type, days_of_week, day_of_month,
                 target_type, target_ids, multiplier
          FROM events
          WHERE tenant_id = :tenant_id
          AND status = '有効'
          AND start_date <= :date1
          AND end_date >= :date2
        ");
        $stmt->execute([
          'tenant_id' => $tenant_id,
          'date1' => $date,
          'date2' => $date
        ]);
        $events = $stmt->fetchAll();

        // 売上日のタイムスタンプと曜日を取得
        $sale_timestamp = strtotime($date);
        $sale_day_of_week = date('w', $sale_timestamp); // 0(日曜)～6(土曜)
        $sale_day_of_month = date('j', $sale_timestamp); // 1～31
        $last_day_of_month = date('t', $sale_timestamp); // その月の最終日

        // 曜日変換マップ（文字→数値）
        $day_name_to_number = [
          '日' => '0',
          '月' => '1',
          '火' => '2',
          '水' => '3',
          '木' => '4',
          '金' => '5',
          '土' => '6'
        ];

        // 各イベントをチェック
        foreach ($events as $event) {
          $is_applicable = false;

          // 繰り返し条件の判定
          $repeat_matches = false;
          switch ($event['repeat_type']) {
            case '単発':
              $repeat_matches = true; // 期間内ならすべて対象
              break;

            case '毎週':
              // 曜日が一致するかチェック
              if (!empty($event['days_of_week'])) {
                $target_days = explode(',', $event['days_of_week']);
                // 曜日文字を数値に変換
                $target_day_numbers = [];
                foreach ($target_days as $day_name) {
                  if (isset($day_name_to_number[$day_name])) {
                    $target_day_numbers[] = $day_name_to_number[$day_name];
                  }
                }
                $repeat_matches = in_array((string)$sale_day_of_week, $target_day_numbers);
              }
              break;

            case '毎月':
              // 日付が一致するかチェック
              if (!empty($event['day_of_month'])) {
                if ($event['day_of_month'] == 99) {
                  // 99は末日を表す
                  $repeat_matches = ($sale_day_of_month == $last_day_of_month);
                } else {
                  $repeat_matches = ($sale_day_of_month == $event['day_of_month']);
                }
              }
              break;
          }

          // 繰り返し条件を満たさない場合はスキップ
          if (!$repeat_matches) {
            continue;
          }

          // 対象商品の判定
          if ($event['target_type'] === '全商品') {
            $is_applicable = true;
          } elseif ($event['target_type'] === '特定商品' && !empty($event['target_ids'])) {
            $target_ids = explode(',', $event['target_ids']);
            if (in_array($product_id, $target_ids)) {
              $is_applicable = true;
            }
          }
          // '全アクション'、'特定アクション'は商品には適用しない

          // 該当するイベントの中で最大倍率を適用
          if ($is_applicable && $event['multiplier'] > $event_multiplier) {
            $event_multiplier = $event['multiplier'];
            $applied_event_id = $event['event_id'];
            $applied_event_name = $event['event_name'];
          }
        }

        echo json_encode([
          'success' => true,
          'event_multiplier' => $event_multiplier,
          'applied_event_id' => $applied_event_id,
          'applied_event_name' => $applied_event_name,
          'base_point' => $base_point
        ]);
        exit;
      }

      // 一覧取得
      $sql = "
                SELECT
                    sr.id,
                    sr.date,
                    sr.member_id,
                    m.name as member_name,
                    sr.product_id,
                    p.product_name,
                    sr.quantity,
                    sr.unit_price,
                    sr.base_point,
                    sr.event_multiplier,
                    sr.final_point,
                    sr.applied_event_id,
                    sr.applied_event_name,
                    sr.note,
                    sr.approval_status,
                    sr.approver,
                    sr.approved_at,
                    sr.reject_reason,
                    sr.created_at
                FROM sales_records sr
                INNER JOIN members m ON sr.tenant_id = m.tenant_id AND sr.member_id = m.member_id
                INNER JOIN products p ON sr.tenant_id = p.tenant_id AND sr.product_id = p.product_id
                WHERE sr.tenant_id = :tenant_id
            ";

      // 権限による制限
      $params = ['tenant_id' => $tenant_id];
      if ($_SESSION['role'] !== 'admin') {
        // ユーザーは自分の売上のみ
        $sql .= " AND sr.member_id = :member_id";
        $params['member_id'] = $_SESSION['member_id'];
      }

      // 期間フィルタ
      if (!empty($_GET['start_date'])) {
        $sql .= " AND DATE(sr.date) >= :start_date";
        $params['start_date'] = $_GET['start_date'];
      }
      if (!empty($_GET['end_date'])) {
        $sql .= " AND DATE(sr.date) <= :end_date";
        $params['end_date'] = $_GET['end_date'];
      }

      // メンバーフィルタ
      if (!empty($_GET['member_ids']) && is_array($_GET['member_ids'])) {
        $placeholders = [];
        foreach ($_GET['member_ids'] as $idx => $member_id) {
          $key = 'member_id_' . $idx;
          $placeholders[] = ':' . $key;
          $params[$key] = $member_id;
        }
        $sql .= " AND sr.member_id IN (" . implode(',', $placeholders) . ")";
      }

      // チームフィルタ（メンバーを通じてチームを絞る）
      if (!empty($_GET['team_ids']) && is_array($_GET['team_ids'])) {
        $placeholders = [];
        foreach ($_GET['team_ids'] as $idx => $team_id) {
          $key = 'team_id_' . $idx;
          $placeholders[] = ':' . $key;
          $params[$key] = $team_id;
        }
        $sql .= " AND m.team_id IN (" . implode(',', $placeholders) . ")";
      }

      // 商品フィルタ
      if (!empty($_GET['product_ids']) && is_array($_GET['product_ids'])) {
        $placeholders = [];
        foreach ($_GET['product_ids'] as $idx => $product_id) {
          $key = 'product_id_' . $idx;
          $placeholders[] = ':' . $key;
          $params[$key] = $product_id;
        }
        $sql .= " AND sr.product_id IN (" . implode(',', $placeholders) . ")";
      }

      // 承認状態フィルタ
      if (!empty($_GET['approval_statuses']) && is_array($_GET['approval_statuses'])) {
        $placeholders = [];
        foreach ($_GET['approval_statuses'] as $idx => $status) {
          $key = 'status_' . $idx;
          $placeholders[] = ':' . $key;
          $params[$key] = $status;
        }
        $sql .= " AND sr.approval_status IN (" . implode(',', $placeholders) . ")";
      }

      $sql .= " ORDER BY sr.date DESC, sr.created_at DESC";

      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $sales = $stmt->fetchAll();

      // 合計金額と合計付与PT計算
      $total = 0;
      $total_points = 0;
      foreach ($sales as $sale) {
        $total += $sale['quantity'] * $sale['unit_price'];
        $total_points += $sale['final_point'];
      }

      echo json_encode([
        'success' => true,
        'data' => $sales,
        'total' => $total,
        'total_points' => $total_points
      ]);
      break;

    case 'POST':
      // 新規登録（管理者のみ）
      if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'この操作は管理者のみ実行できます。']);
        exit;
      }

      $input = json_decode(file_get_contents('php://input'), true);

      // バリデーション
      if (empty($input['date']) || empty($input['member_id']) || empty($input['product_id']) || !isset($input['quantity']) || !isset($input['unit_price'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // 数値チェック
      if (!is_numeric($input['quantity']) || !is_numeric($input['unit_price'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '数量と単価は数値で入力してください。']);
        exit;
      }

      // 未来日付チェック
      if (strtotime($input['date']) > time()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '未来の日付は入力できません。']);
        exit;
      }

      // 商品情報取得
      $stmt = $pdo->prepare("
                SELECT product_id, product_name, point, price 
                FROM products 
                WHERE tenant_id = :tenant_id AND product_id = :product_id
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'product_id' => $input['product_id']
      ]);
      $product = $stmt->fetch();

      if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '商品が見つかりません。']);
        exit;
      }

      // 基準付与pt
      $base_point = $product['point'] * $input['quantity'];

      // イベント倍率計算（該当する有効イベントの最大倍率）
      $event_multiplier = 1.0;
      $applied_event_id = null;
      $applied_event_name = null;

      // 有効なイベントを全て取得
      $stmt = $pdo->prepare("
                SELECT event_id, event_name, repeat_type, days_of_week, day_of_month,
                       target_type, target_ids, multiplier
                FROM events
                WHERE tenant_id = :tenant_id
                AND status = '有効'
                AND start_date <= :date1
                AND end_date >= :date2
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'date1' => $input['date'],
        'date2' => $input['date']
      ]);
      $events = $stmt->fetchAll();

      // 売上日のタイムスタンプと曜日を取得
      $sale_timestamp = strtotime($input['date']);
      $sale_day_of_week = date('w', $sale_timestamp); // 0(日曜)～6(土曜)
      $sale_day_of_month = date('j', $sale_timestamp); // 1～31
      $last_day_of_month = date('t', $sale_timestamp); // その月の最終日

      // 曜日変換マップ（文字→数値）
      $day_name_to_number = [
        '日' => '0',
        '月' => '1',
        '火' => '2',
        '水' => '3',
        '木' => '4',
        '金' => '5',
        '土' => '6'
      ];

      // 各イベントをチェック
      foreach ($events as $event) {
        $is_applicable = false;

        // 繰り返し条件の判定
        $repeat_matches = false;
        switch ($event['repeat_type']) {
          case '単発':
            $repeat_matches = true; // 期間内ならすべて対象
            break;

          case '毎週':
            // 曜日が一致するかチェック
            if (!empty($event['days_of_week'])) {
              $target_days = explode(',', $event['days_of_week']);
              // 曜日文字を数値に変換
              $target_day_numbers = [];
              foreach ($target_days as $day_name) {
                if (isset($day_name_to_number[$day_name])) {
                  $target_day_numbers[] = $day_name_to_number[$day_name];
                }
              }
              $repeat_matches = in_array((string)$sale_day_of_week, $target_day_numbers);
            }
            break;

          case '毎月':
            // 日付が一致するかチェック
            if (!empty($event['day_of_month'])) {
              if ($event['day_of_month'] == 99) {
                // 99は末日を表す
                $repeat_matches = ($sale_day_of_month == $last_day_of_month);
              } else {
                $repeat_matches = ($sale_day_of_month == $event['day_of_month']);
              }
            }
            break;
        }

        // 繰り返し条件を満たさない場合はスキップ
        if (!$repeat_matches) {
          continue;
        }

        // 対象商品の判定
        if ($event['target_type'] === '全商品') {
          $is_applicable = true;
        } elseif ($event['target_type'] === '特定商品' && !empty($event['target_ids'])) {
          $target_ids = explode(',', $event['target_ids']);
          if (in_array($input['product_id'], $target_ids)) {
            $is_applicable = true;
          }
        }
        // '全アクション'、'特定アクション'は商品には適用しない

        // 該当するイベントの中で最大倍率を適用
        if ($is_applicable && $event['multiplier'] > $event_multiplier) {
          $event_multiplier = $event['multiplier'];
          $applied_event_id = $event['event_id'];
          $applied_event_name = $event['event_name'];
        }
      }

      // 最終付与pt
      $final_point = floor($base_point * $event_multiplier);

      // 単価変更チェック
      $note = $input['note'] ?? '';
      if ($input['unit_price'] != $product['price']) {
        $note = "[単価変更] 標準価格: ¥" . number_format($product['price']) . " → 変更後: ¥" . number_format($input['unit_price']) . "\n" . $note;
      }

      // 登録
      $stmt = $pdo->prepare("
                INSERT INTO sales_records (
                    tenant_id, date, member_id, product_id, quantity,
                    unit_price, base_point, event_multiplier, final_point,
                    applied_event_id, applied_event_name,
                    note, approval_status
                ) VALUES (
                    :tenant_id, :date, :member_id, :product_id, :quantity,
                    :unit_price, :base_point, :event_multiplier, :final_point,
                    :applied_event_id, :applied_event_name,
                    :note, 'ユーザー確認待ち'
                )
            ");

      $stmt->execute([
        'tenant_id' => $tenant_id,
        'date' => $input['date'],
        'member_id' => $input['member_id'],
        'product_id' => $input['product_id'],
        'quantity' => $input['quantity'],
        'unit_price' => $input['unit_price'],
        'base_point' => $base_point,
        'event_multiplier' => $event_multiplier,
        'final_point' => $final_point,
        'applied_event_id' => $applied_event_id,
        'applied_event_name' => $applied_event_name,
        'note' => $note
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
                VALUES (:tenant_id, '新規登録', 'sales_records', :row_id, :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'row_id' => $pdo->lastInsertId(),
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode($input, JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => '売上を登録しました。',
        'data' => [
          'base_point' => $base_point,
          'event_multiplier' => $event_multiplier,
          'final_point' => $final_point
        ]
      ]);
      break;

    case 'DELETE':
      // 削除（管理者のみ）
      if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'この操作は管理者のみ実行できます。']);
        exit;
      }

      $sale_id = $_GET['id'] ?? null;
      if (!$sale_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '売上IDが指定されていません。']);
        exit;
      }

      // 売上レコードの存在確認
      $stmt = $pdo->prepare("
        SELECT id, product_id, quantity, unit_price
        FROM sales_records
        WHERE tenant_id = :tenant_id AND id = :id
      ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'id' => $sale_id
      ]);
      $sale = $stmt->fetch();

      if (!$sale) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '売上レコードが見つかりません。']);
        exit;
      }

      // 削除実行
      $stmt = $pdo->prepare("
        DELETE FROM sales_records
        WHERE tenant_id = :tenant_id AND id = :id
      ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'id' => $sale_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
        VALUES (:tenant_id, '削除', 'sales_records', :row_id, :operator, :name, :details)
      ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'row_id' => $sale_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode($sale, JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => '売上レコードを削除しました。'
      ]);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  }
} catch (PDOException $e) {
  error_log('Sales API error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => 'サーバーエラーが発生しました。',
    'debug' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
  ]);
}
