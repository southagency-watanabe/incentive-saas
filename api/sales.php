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
      // 一覧取得
      $filter = $_GET['filter'] ?? 'all';

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

      // フィルタ
      if ($filter === 'pending') {
        $sql .= " AND sr.approval_status = 'ユーザー確認待ち'";
      }

      $sql .= " ORDER BY sr.date DESC, sr.created_at DESC";

      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $sales = $stmt->fetchAll();

      // 合計金額計算
      $total = 0;
      foreach ($sales as $sale) {
        $total += $sale['quantity'] * $sale['unit_price'];
      }

      echo json_encode([
        'success' => true,
        'data' => $sales,
        'total' => $total
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

      // 有効なイベントを全て取得
      $stmt = $pdo->prepare("
                SELECT event_id, target_type, target_ids, multiplier
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

      // 各イベントをチェック
      foreach ($events as $event) {
        $is_applicable = false;

        if ($event['target_type'] === '全商品') {
          $is_applicable = true;
        } elseif ($event['target_type'] === '特定商品' && !empty($event['target_ids'])) {
          $target_ids = explode(',', $event['target_ids']);
          if (in_array($input['product_id'], $target_ids)) {
            $is_applicable = true;
          }
        }

        // 該当するイベントの中で最大倍率を適用
        if ($is_applicable && $event['multiplier'] > $event_multiplier) {
          $event_multiplier = $event['multiplier'];
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
                    note, approval_status
                ) VALUES (
                    :tenant_id, :date, :member_id, :product_id, :quantity,
                    :unit_price, :base_point, :event_multiplier, :final_point,
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
