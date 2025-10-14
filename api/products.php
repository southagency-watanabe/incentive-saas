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
                    product_id,
                    product_name,
                    large_category,
                    medium_category,
                    small_category,
                    point,
                    price,
                    cost,
                    status,
                    approval_required,
                    description,
                    created_at,
                    updated_at
                FROM products
                WHERE tenant_id = :tenant_id
                ORDER BY product_id ASC
            ");

      $stmt->execute(['tenant_id' => $tenant_id]);
      $products = $stmt->fetchAll();

      // approval_requiredを「必要」「不要」に変換
      foreach ($products as &$product) {
        $product['approval_required'] = $product['approval_required'] ? '必要' : '不要';
      }

      echo json_encode([
        'success' => true,
        'data' => $products
      ]);
      break;

    case 'POST':
      // 新規登録
      $input = json_decode(file_get_contents('php://input'), true);

      // バリデーション
      if (empty($input['product_name']) || !isset($input['point']) || !isset($input['price']) || empty($input['status']) || empty($input['approval_required'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // 数値チェック
      if (!is_numeric($input['point']) || !is_numeric($input['price'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '付与ptと売価は数値で入力してください。']);
        exit;
      }

      if (isset($input['cost']) && $input['cost'] !== '' && !is_numeric($input['cost'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '原価は数値で入力してください。']);
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
      $product_id = 'PRD' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

      // approval_requiredを0/1に変換
      $approval_required_bool = ($input['approval_required'] === '必要') ? 1 : 0;

      // 登録
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
        'product_id' => $product_id,
        'tenant_id' => $tenant_id,
        'product_name' => $input['product_name'],
        'large_category' => $input['large_category'] ?? null,
        'medium_category' => $input['medium_category'] ?? null,
        'small_category' => $input['small_category'] ?? null,
        'point' => $input['point'],
        'price' => $input['price'],
        'cost' => ($input['cost'] !== '' && $input['cost'] !== null) ? $input['cost'] : null,
        'status' => $input['status'],
        'approval_required' => $approval_required_bool,
        'description' => $input['description'] ?? null
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
                VALUES (:tenant_id, '新規登録', 'products', :row_id, :operator, :name, :details)
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
        'message' => '商品を登録しました。',
        'product_id' => $product_id
      ]);
      break;

    case 'PUT':
      // 更新
      $input = json_decode(file_get_contents('php://input'), true);

      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '商品IDが指定されていません。']);
        exit;
      }

      $product_id = $_GET['id'];

      // バリデーション
      if (empty($input['product_name']) || !isset($input['point']) || !isset($input['price']) || empty($input['status']) || empty($input['approval_required'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // 数値チェック
      if (!is_numeric($input['point']) || !is_numeric($input['price'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '付与ptと売価は数値で入力してください。']);
        exit;
      }

      if (isset($input['cost']) && $input['cost'] !== '' && !is_numeric($input['cost'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '原価は数値で入力してください。']);
        exit;
      }

      // approval_requiredを0/1に変換
      $approval_required_bool = ($input['approval_required'] === '必要') ? 1 : 0;

      // 更新
      $stmt = $pdo->prepare("
                UPDATE products SET
                    product_name = :product_name,
                    large_category = :large_category,
                    medium_category = :medium_category,
                    small_category = :small_category,
                    point = :point,
                    price = :price,
                    cost = :cost,
                    status = :status,
                    approval_required = :approval_required,
                    description = :description
                WHERE tenant_id = :tenant_id AND product_id = :product_id
            ");

      $stmt->execute([
        'product_name' => $input['product_name'],
        'large_category' => $input['large_category'] ?? null,
        'medium_category' => $input['medium_category'] ?? null,
        'small_category' => $input['small_category'] ?? null,
        'point' => $input['point'],
        'price' => $input['price'],
        'cost' => ($input['cost'] !== '' && $input['cost'] !== null) ? $input['cost'] : null,
        'status' => $input['status'],
        'approval_required' => $approval_required_bool,
        'description' => $input['description'] ?? null,
        'tenant_id' => $tenant_id,
        'product_id' => $product_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
                VALUES (:tenant_id, '更新', 'products', :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(array_merge(['product_id' => $product_id], $input), JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => '商品情報を更新しました。'
      ]);
      break;

    case 'DELETE':
      // 削除
      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '商品IDが指定されていません。']);
        exit;
      }

      $product_id = $_GET['id'];

      // 売上実績で使用されていないかチェック
      $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM sales_records 
                WHERE tenant_id = :tenant_id AND product_id = :product_id
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'product_id' => $product_id
      ]);

      if ($stmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'この商品は売上実績で使用されているため削除できません。']);
        exit;
      }

      $stmt = $pdo->prepare("
                DELETE FROM products 
                WHERE tenant_id = :tenant_id AND product_id = :product_id
            ");

      $stmt->execute([
        'tenant_id' => $tenant_id,
        'product_id' => $product_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
                VALUES (:tenant_id, '削除', 'products', :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(['deleted_product_id' => $product_id], JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => '商品を削除しました。'
      ]);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  }
} catch (PDOException $e) {
  error_log('Products API error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
