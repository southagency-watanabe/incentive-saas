<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';

// 管理者権限チェック
requireAdmin();

// POSTリクエストのみ
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  exit;
}

$pdo = getDB();
$tenant_id = $_SESSION['tenant_id'];
$input = json_decode(file_get_contents('php://input'), true);

try {
  // 元の商品IDが指定されているか
  if (!isset($input['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '商品IDが指定されていません。']);
    exit;
  }

  $source_product_id = $input['product_id'];

  // 元の商品情報を取得
  $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE tenant_id = :tenant_id AND product_id = :product_id
    ");

  $stmt->execute([
    'tenant_id' => $tenant_id,
    'product_id' => $source_product_id
  ]);

  $source = $stmt->fetch();

  if (!$source) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => '商品が見つかりません。']);
    exit;
  }

  // 新しい商品ID生成（PRD001形式）
  $stmt = $pdo->prepare("
        SELECT product_id FROM products 
        WHERE tenant_id = :tenant_id 
        ORDER BY product_id DESC LIMIT 1
    ");
  $stmt->execute(['tenant_id' => $tenant_id]);
  $lastProduct = $stmt->fetch();

  if ($lastProduct) {
    $lastNum = (int)substr($lastProduct['product_id'], 3);
    $newNum = $lastNum + 1;
  } else {
    $newNum = 1;
  }
  $new_product_id = 'PRD' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

  // 複製して登録
  $stmt = $pdo->prepare("
        INSERT INTO products (
            product_id, tenant_id, product_name, large_category,
            medium_category, small_category, point, price, cost,
            status, approval_required, description
        ) VALUES (
            :product_id, :tenant_id, :product_name, :large_category,
            :medium_category, :small_category, :point, :price, :cost,
            :status, :approval_required, :description
        )
    ");

  $stmt->execute([
    'product_id' => $new_product_id,
    'tenant_id' => $tenant_id,
    'product_name' => $source['product_name'] . ' (コピー)',
    'large_category' => $source['large_category'],
    'medium_category' => $source['medium_category'],
    'small_category' => $source['small_category'],
    'point' => $source['point'],
    'price' => $source['price'],
    'cost' => $source['cost'],
    'status' => $source['status'],
    'approval_required' => $source['approval_required'],
    'description' => $source['description']
  ]);

  // 監査ログ
  $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
        VALUES (:tenant_id, '複製', 'products', :operator, :name, :details)
    ");
  $stmt->execute([
    'tenant_id' => $tenant_id,
    'operator' => $_SESSION['member_id'],
    'name' => $_SESSION['name'],
    'details' => json_encode([
      'source_product_id' => $source_product_id,
      'new_product_id' => $new_product_id
    ], JSON_UNESCAPED_UNICODE)
  ]);

  echo json_encode([
    'success' => true,
    'message' => '商品を複製しました。',
    'product_id' => $new_product_id
  ]);
} catch (PDOException $e) {
  error_log('Product duplicate error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
