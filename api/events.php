<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// 管理者権限チェック
requireAdmin();

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$tenant_id = $_SESSION['tenant_id'];

try {
  switch ($method) {
    case 'GET':
      // 一覧取得
      $stmt = $pdo->prepare("
                SELECT
                    event_id,
                    event_name,
                    repeat_type,
                    start_date,
                    end_date,
                    days_of_week,
                    day_of_month,
                    target_type,
                    target_ids,
                    multiplier,
                    approval_required,
                    status,
                    description,
                    publish_notice,
                    notice_title,
                    notice_body,
                    created_at,
                    updated_at
                FROM events
                WHERE tenant_id = :tenant_id
                ORDER BY event_id ASC
            ");

      $stmt->execute(['tenant_id' => $tenant_id]);
      $events = $stmt->fetchAll();

      // 各イベントに対象商品名またはアクション名を追加
      foreach ($events as &$event) {
        $event['target_names'] = [];
        $event['product_multipliers'] = []; // 商品別倍率

        if ($event['target_type'] === '特定商品') {
          // 商品カテゴリ別倍率を取得
          $stmt = $pdo->prepare("
            SELECT category, multiplier 
            FROM event_product_category_multipliers
            WHERE event_id = ? AND tenant_id = ?
          ");
          $stmt->execute([$event['event_id'], $tenant_id]);
          $categoryMultipliers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
          $event['product_category_multipliers'] = $categoryMultipliers;

          // 個別商品別倍率を取得
          $event['product_multipliers'] = [];
          if (!empty($event['target_ids'])) {
            $productIds = explode(',', $event['target_ids']);
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));

            $stmt = $pdo->prepare("
              SELECT product_name FROM products
              WHERE tenant_id = ? AND product_id IN ($placeholders)
            ");
            $stmt->execute(array_merge([$tenant_id], $productIds));
            $event['target_names'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // 商品別倍率を取得
            $stmt = $pdo->prepare("
              SELECT product_id, multiplier 
              FROM event_product_multipliers
              WHERE event_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$event['event_id'], $tenant_id]);
            $multipliers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $event['product_multipliers'] = $multipliers;
          }

        } elseif ($event['target_type'] === '特定アクション') {
          // カテゴリ別倍率を取得
          $stmt = $pdo->prepare("
            SELECT category, multiplier 
            FROM event_action_category_multipliers
            WHERE event_id = ? AND tenant_id = ?
          ");
          $stmt->execute([$event['event_id'], $tenant_id]);
          $categoryMultipliers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
          $event['category_multipliers'] = $categoryMultipliers;

          // 個別アクション別倍率を取得
          $event['action_multipliers'] = [];
          if (!empty($event['target_ids'])) {
            $actionIds = explode(',', $event['target_ids']);
            $placeholders = implode(',', array_fill(0, count($actionIds), '?'));

            $stmt = $pdo->prepare("
              SELECT action_name FROM actions
              WHERE tenant_id = ? AND action_id IN ($placeholders)
            ");
            $stmt->execute(array_merge([$tenant_id], $actionIds));
            $event['target_names'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // アクション別倍率を取得
            $stmt = $pdo->prepare("
              SELECT action_id, multiplier 
              FROM event_action_multipliers
              WHERE event_id = ? AND tenant_id = ?
            ");
            $stmt->execute([$event['event_id'], $tenant_id]);
            $multipliers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $event['action_multipliers'] = $multipliers;
          }
        }

        // approval_requiredを文字列に変換
        $event['approval_required'] = $event['approval_required'] == 1 ? '必要' : '不要';
      }

      echo json_encode([
        'success' => true,
        'data' => $events
      ]);
      break;

    case 'POST':
      // 新規登録
      $input = json_decode(file_get_contents('php://input'), true);

      // バリデーション
      if (empty($input['event_name']) || empty($input['repeat_type']) || empty($input['start_date']) || empty($input['end_date']) || empty($input['target_type']) || !isset($input['multiplier']) || empty($input['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // 繰り返しチェック
      if (!in_array($input['repeat_type'], ['単発', '毎週', '毎月'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '繰り返しは「単発」「毎週」「毎月」のいずれかを選択してください。']);
        exit;
      }

      // 対象タイプチェック
      if (!in_array($input['target_type'], ['全商品', '特定商品', '全アクション', '特定アクション'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '対象タイプを正しく選択してください。']);
        exit;
      }

      // 数値チェック
      if (!is_numeric($input['multiplier'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '倍率は数値で入力してください。']);
        exit;
      }

      // 繰り返し設定の検証
      if ($input['repeat_type'] === '毎週' && empty($input['days_of_week'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '繰り返しが「毎週」の場合は曜日を選択してください。']);
        exit;
      }

      if ($input['repeat_type'] === '毎月' && empty($input['day_of_month'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '繰り返しが「毎月」の場合は日付を入力してください。']);
        exit;
      }

      // 対象IDsの検証
      if (in_array($input['target_type'], ['特定商品', '特定アクション']) && empty($input['target_ids'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '対象を選択してください。']);
        exit;
      }

      // 告知公開の検証
      if (!empty($input['publish_notice']) && (empty($input['notice_title']) || empty($input['notice_body']))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '告知公開をONにする場合は、タイトルと本文を入力してください。']);
        exit;
      }

      // 新しいイベントID生成（EVT001形式）
      $stmt = $pdo->prepare("
                SELECT event_id FROM events 
                WHERE tenant_id = :tenant_id 
                ORDER BY event_id DESC LIMIT 1
            ");
      $stmt->execute(['tenant_id' => $tenant_id]);
      $lastEvent = $stmt->fetch();

      if ($lastEvent) {
        $lastNum = (int)substr($lastEvent['event_id'], 3);
        $newNum = $lastNum + 1;
      } else {
        $newNum = 1;
      }
      $event_id = 'EVT' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

      // 繰り返し設定に応じて値を設定
      $days_of_week = null;
      $day_of_month = null;

      if ($input['repeat_type'] === '毎週') {
        $days_of_week = $input['days_of_week'];
      } elseif ($input['repeat_type'] === '毎月') {
        $day_of_month = $input['day_of_month'];
      }

      // 対象IDsの設定
      $target_ids = null;
      if (in_array($input['target_type'], ['特定商品', '特定アクション'])) {
        $target_ids = $input['target_ids'];
      }

      // 登録
      $stmt = $pdo->prepare("
                INSERT INTO events (
                    event_id, tenant_id, event_name, repeat_type, start_date, end_date,
                    days_of_week, day_of_month, target_type, target_ids, multiplier,
                    approval_required, status, description, publish_notice, notice_title, notice_body
                ) VALUES (
                    :event_id, :tenant_id, :event_name, :repeat_type, :start_date, :end_date,
                    :days_of_week, :day_of_month, :target_type, :target_ids, :multiplier,
                    :approval_required, :status, :description, :publish_notice, :notice_title, :notice_body
                )
            ");

      $stmt->execute([
        'event_id' => $event_id,
        'tenant_id' => $tenant_id,
        'event_name' => $input['event_name'],
        'repeat_type' => $input['repeat_type'],
        'start_date' => $input['start_date'],
        'end_date' => $input['end_date'],
        'days_of_week' => $days_of_week,
        'day_of_month' => $day_of_month,
        'target_type' => $input['target_type'],
        'target_ids' => $target_ids,
        'multiplier' => $input['multiplier'],
        'approval_required' => $input['approval_required'] === '必要' ? 1 : 0,
        'status' => $input['status'],
        'description' => $input['description'] ?? null,
        'publish_notice' => !empty($input['publish_notice']) ? 1 : 0,
        'notice_title' => $input['notice_title'] ?? null,
        'notice_body' => $input['notice_body'] ?? null
      ]);

      // 商品別倍率を保存（特定商品の場合）
      if ($input['target_type'] === '特定商品' && !empty($input['product_multipliers'])) {
        foreach ($input['product_multipliers'] as $product_id => $multiplier) {
          // 数値チェック
          if (is_numeric($multiplier) && $multiplier > 0) {
            $stmt = $pdo->prepare("
              INSERT INTO event_product_multipliers (event_id, tenant_id, product_id, multiplier)
              VALUES (:event_id, :tenant_id, :product_id, :multiplier)
            ");
            $stmt->execute([
              'event_id' => $event_id,
              'tenant_id' => $tenant_id,
              'product_id' => $product_id,
              'multiplier' => $multiplier
            ]);
          }
        }
      }

      // カテゴリ別倍率を保存（特定アクションの場合）
      if ($input['target_type'] === '特定アクション' && !empty($input['category_multipliers'])) {
        foreach ($input['category_multipliers'] as $category => $multiplier) {
          // 数値チェック
          if (is_numeric($multiplier) && $multiplier > 0) {
            $stmt = $pdo->prepare("
              INSERT INTO event_action_category_multipliers (event_id, tenant_id, category, multiplier)
              VALUES (:event_id, :tenant_id, :category, :multiplier)
            ");
            $stmt->execute([
              'event_id' => $event_id,
              'tenant_id' => $tenant_id,
              'category' => $category,
              'multiplier' => $multiplier
            ]);
          }
        }
      }

      // アクション別倍率を保存（特定アクションの場合）
      if ($input['target_type'] === '特定アクション' && !empty($input['action_multipliers'])) {
        foreach ($input['action_multipliers'] as $action_id => $multiplier) {
          // 数値チェック
          if (is_numeric($multiplier) && $multiplier > 0) {
            $stmt = $pdo->prepare("
              INSERT INTO event_action_multipliers (event_id, tenant_id, action_id, multiplier)
              VALUES (:event_id, :tenant_id, :action_id, :multiplier)
            ");
            $stmt->execute([
              'event_id' => $event_id,
              'tenant_id' => $tenant_id,
              'action_id' => $action_id,
              'multiplier' => $multiplier
            ]);
          }
        }
      }

      // 告知を掲示板に投稿（publish_notice=trueの場合）
      if (!empty($input['publish_notice'])) {
        // 新しい掲示板ID生成
        $stmt = $pdo->prepare("
                    SELECT bulletin_id FROM bulletins 
                    WHERE tenant_id = :tenant_id 
                    ORDER BY bulletin_id DESC LIMIT 1
                ");
        $stmt->execute(['tenant_id' => $tenant_id]);
        $lastBulletin = $stmt->fetch();

        if ($lastBulletin) {
          $lastNum = (int)substr($lastBulletin['bulletin_id'], 1);
          $newNum = $lastNum + 1;
        } else {
          $newNum = 1;
        }
        $bulletin_id = 'B' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

        // 掲示板に投稿
        $stmt = $pdo->prepare("
                    INSERT INTO bulletins (
                        bulletin_id, tenant_id, type, title, body,
                        start_datetime, end_datetime, related_event_id,
                        pinned, status, author
                    ) VALUES (
                        :bulletin_id, :tenant_id, 'イベント告知', :title, :body,
                        :start_datetime, :end_datetime, :related_event_id,
                        0, '公開', :author
                    )
                ");

        $stmt->execute([
          'bulletin_id' => $bulletin_id,
          'tenant_id' => $tenant_id,
          'title' => $input['notice_title'],
          'body' => $input['notice_body'],
          'start_datetime' => $input['start_date'] . ' 00:00:00',
          'end_datetime' => $input['end_date'] . ' 23:59:59',
          'related_event_id' => $event_id,
          'author' => $_SESSION['member_id']
        ]);
      }

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
                VALUES (:tenant_id, '新規登録', 'events', :row_id, :operator, :name, :details)
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
        'message' => 'イベントを登録しました。',
        'event_id' => $event_id
      ]);
      break;

    case 'PUT':
      // 更新
      $input = json_decode(file_get_contents('php://input'), true);

      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'イベントIDが指定されていません。']);
        exit;
      }

      $event_id = $_GET['id'];

      // バリデーション（新規登録と同様）
      if (empty($input['event_name']) || empty($input['repeat_type']) || empty($input['start_date']) || empty($input['end_date']) || empty($input['target_type']) || !isset($input['multiplier']) || empty($input['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // 繰り返し設定に応じて値を設定
      $days_of_week = null;
      $day_of_month = null;

      if ($input['repeat_type'] === '毎週') {
        $days_of_week = $input['days_of_week'];
      } elseif ($input['repeat_type'] === '毎月') {
        $day_of_month = $input['day_of_month'];
      }

      // 対象IDsの設定
      $target_ids = null;
      if (in_array($input['target_type'], ['特定商品', '特定アクション'])) {
        $target_ids = $input['target_ids'];
      }

      // 更新
      $stmt = $pdo->prepare("
                UPDATE events SET
                    event_name = :event_name,
                    repeat_type = :repeat_type,
                    start_date = :start_date,
                    end_date = :end_date,
                    days_of_week = :days_of_week,
                    day_of_month = :day_of_month,
                    target_type = :target_type,
                    target_ids = :target_ids,
                    multiplier = :multiplier,
                    approval_required = :approval_required,
                    status = :status,
                    description = :description,
                    publish_notice = :publish_notice,
                    notice_title = :notice_title,
                    notice_body = :notice_body
                WHERE tenant_id = :tenant_id AND event_id = :event_id
            ");

      $stmt->execute([
        'event_name' => $input['event_name'],
        'repeat_type' => $input['repeat_type'],
        'start_date' => $input['start_date'],
        'end_date' => $input['end_date'],
        'days_of_week' => $days_of_week,
        'day_of_month' => $day_of_month,
        'target_type' => $input['target_type'],
        'target_ids' => $target_ids,
        'multiplier' => $input['multiplier'],
        'approval_required' => $input['approval_required'] === '必要' ? 1 : 0,
        'status' => $input['status'],
        'description' => $input['description'] ?? null,
        'publish_notice' => !empty($input['publish_notice']) ? 1 : 0,
        'notice_title' => $input['notice_title'] ?? null,
        'notice_body' => $input['notice_body'] ?? null,
        'tenant_id' => $tenant_id,
        'event_id' => $event_id
      ]);

      // 商品別倍率を更新（既存削除 + 新規挿入）
      // まず既存の商品別倍率を削除
      $stmt = $pdo->prepare("
        DELETE FROM event_product_multipliers 
        WHERE event_id = :event_id AND tenant_id = :tenant_id
      ");
      $stmt->execute([
        'event_id' => $event_id,
        'tenant_id' => $tenant_id
      ]);

      // 新しい商品別倍率を保存（特定商品の場合）
      if ($input['target_type'] === '特定商品' && !empty($input['product_multipliers'])) {
        foreach ($input['product_multipliers'] as $product_id => $multiplier) {
          // 数値チェック
          if (is_numeric($multiplier) && $multiplier > 0) {
            $stmt = $pdo->prepare("
              INSERT INTO event_product_multipliers (event_id, tenant_id, product_id, multiplier)
              VALUES (:event_id, :tenant_id, :product_id, :multiplier)
            ");
            $stmt->execute([
              'event_id' => $event_id,
              'tenant_id' => $tenant_id,
              'product_id' => $product_id,
              'multiplier' => $multiplier
            ]);
          }
        }
      }

      // カテゴリ別倍率を更新（既存削除 + 新規挿入）
      // まず既存のカテゴリ別倍率を削除
      $stmt = $pdo->prepare("
        DELETE FROM event_action_category_multipliers 
        WHERE event_id = :event_id AND tenant_id = :tenant_id
      ");
      $stmt->execute([
        'event_id' => $event_id,
        'tenant_id' => $tenant_id
      ]);

      // 新しいカテゴリ別倍率を保存（特定アクションの場合）
      if ($input['target_type'] === '特定アクション' && !empty($input['category_multipliers'])) {
        foreach ($input['category_multipliers'] as $category => $multiplier) {
          // 数値チェック
          if (is_numeric($multiplier) && $multiplier > 0) {
            $stmt = $pdo->prepare("
              INSERT INTO event_action_category_multipliers (event_id, tenant_id, category, multiplier)
              VALUES (:event_id, :tenant_id, :category, :multiplier)
            ");
            $stmt->execute([
              'event_id' => $event_id,
              'tenant_id' => $tenant_id,
              'category' => $category,
              'multiplier' => $multiplier
            ]);
          }
        }
      }

      // アクション別倍率を更新（既存削除 + 新規挿入）
      // まず既存のアクション別倍率を削除
      $stmt = $pdo->prepare("
        DELETE FROM event_action_multipliers 
        WHERE event_id = :event_id AND tenant_id = :tenant_id
      ");
      $stmt->execute([
        'event_id' => $event_id,
        'tenant_id' => $tenant_id
      ]);

      // 新しいアクション別倍率を保存（特定アクションの場合）
      if ($input['target_type'] === '特定アクション' && !empty($input['action_multipliers'])) {
        foreach ($input['action_multipliers'] as $action_id => $multiplier) {
          // 数値チェック
          if (is_numeric($multiplier) && $multiplier > 0) {
            $stmt = $pdo->prepare("
              INSERT INTO event_action_multipliers (event_id, tenant_id, action_id, multiplier)
              VALUES (:event_id, :tenant_id, :action_id, :multiplier)
            ");
            $stmt->execute([
              'event_id' => $event_id,
              'tenant_id' => $tenant_id,
              'action_id' => $action_id,
              'multiplier' => $multiplier
            ]);
          }
        }
      }

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
                VALUES (:tenant_id, '更新', 'events', :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(array_merge(['event_id' => $event_id], $input), JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'イベント情報を更新しました。'
      ]);
      break;

    case 'DELETE':
      // 削除
      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'イベントIDが指定されていません。']);
        exit;
      }

      $event_id = $_GET['id'];

      // 関連する掲示板投稿も削除
      $stmt = $pdo->prepare("
                DELETE FROM bulletins 
                WHERE tenant_id = :tenant_id AND related_event_id = :event_id
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'event_id' => $event_id
      ]);

      // イベント削除
      $stmt = $pdo->prepare("
                DELETE FROM events 
                WHERE tenant_id = :tenant_id AND event_id = :event_id
            ");

      $stmt->execute([
        'tenant_id' => $tenant_id,
        'event_id' => $event_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
                VALUES (:tenant_id, '削除', 'events', :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(['deleted_event_id' => $event_id], JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'イベントを削除しました。'
      ]);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  }
} catch (PDOException $e) {
  error_log('Events API error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
