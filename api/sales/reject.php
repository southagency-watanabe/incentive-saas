<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';

// 管理者権限チェック
requireAdmin();

// PUTリクエストのみ
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  exit;
}

$pdo = getDB();
$tenant_id = $_SESSION['tenant_id'];
$input = json_decode(file_get_contents('php://input'), true);

try {
  // 売上IDが指定されているか
  if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '売上IDが指定されていません。']);
    exit;
  }

  // 却下理由が必須
  if (empty($input['reject_reason'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '却下理由を入力してください。']);
    exit;
  }

  $sale_id = $_GET['id'];

  // 売上情報を取得
  $stmt = $pdo->prepare("
        SELECT id, approval_status 
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
    echo json_encode(['success' => false, 'message' => '売上が見つかりません。']);
    exit;
  }

  // 強制却下
  $stmt = $pdo->prepare("
        UPDATE sales_records 
        SET approval_status = '却下',
            approver = :approver,
            approved_at = NOW(),
            reject_reason = :reject_reason
        WHERE tenant_id = :tenant_id AND id = :id
    ");

  $stmt->execute([
    'approver' => $_SESSION['member_id'],
    'reject_reason' => $input['reject_reason'],
    'tenant_id' => $tenant_id,
    'id' => $sale_id
  ]);

  // 監査ログ
  $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
        VALUES (:tenant_id, '強制却下', 'sales_records', :row_id, :operator, :name, :details)
    ");
  $stmt->execute([
    'tenant_id' => $tenant_id,
    'row_id' => $sale_id,
    'operator' => $_SESSION['member_id'],
    'name' => $_SESSION['name'],
    'details' => json_encode([
      'sale_id' => $sale_id,
      'reject_reason' => $input['reject_reason']
    ], JSON_UNESCAPED_UNICODE)
  ]);

  echo json_encode([
    'success' => true,
    'message' => '売上を却下しました。'
  ]);
} catch (PDOException $e) {
  error_log('Sales reject error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
