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
      // テンプレート一覧取得
      $stmt = $pdo->prepare("
        SELECT
          template_id,
          template_name,
          title,
          body,
          created_at,
          updated_at
        FROM bulletin_templates
        WHERE tenant_id = :tenant_id
        ORDER BY created_at DESC
      ");

      $stmt->execute(['tenant_id' => $tenant_id]);
      $templates = $stmt->fetchAll();

      echo json_encode([
        'success' => true,
        'data' => $templates
      ]);
      break;

    case 'POST':
      // 新規テンプレート作成
      $input = json_decode(file_get_contents('php://input'), true);

      // バリデーション
      if (empty($input['template_name']) || empty($input['title']) || empty($input['body'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // 新しいテンプレートID生成
      $stmt = $pdo->prepare("
        SELECT template_id FROM bulletin_templates 
        WHERE tenant_id = :tenant_id 
        ORDER BY template_id DESC LIMIT 1
      ");
      $stmt->execute(['tenant_id' => $tenant_id]);
      $lastTemplate = $stmt->fetch();

      if ($lastTemplate) {
        $lastNum = intval(substr($lastTemplate['template_id'], 4));
        $template_id = 'TPL' . str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
      } else {
        $template_id = 'TPL001';
      }

      // 登録
      $stmt = $pdo->prepare("
        INSERT INTO bulletin_templates (
          template_id, tenant_id, template_name, title, body
        ) VALUES (
          :template_id, :tenant_id, :template_name, :title, :body
        )
      ");

      $stmt->execute([
        'template_id' => $template_id,
        'tenant_id' => $tenant_id,
        'template_name' => $input['template_name'],
        'title' => $input['title'],
        'body' => $input['body']
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'テンプレートを登録しました。',
        'template_id' => $template_id
      ]);
      break;

    case 'PUT':
      // テンプレート更新
      $input = json_decode(file_get_contents('php://input'), true);

      if (empty($input['template_id']) || empty($input['template_name']) || empty($input['title']) || empty($input['body'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      $template_id = $input['template_id'];

      // 更新
      $stmt = $pdo->prepare("
        UPDATE bulletin_templates SET
          template_name = :template_name,
          title = :title,
          body = :body
        WHERE tenant_id = :tenant_id AND template_id = :template_id
      ");

      $stmt->execute([
        'template_name' => $input['template_name'],
        'title' => $input['title'],
        'body' => $input['body'],
        'tenant_id' => $tenant_id,
        'template_id' => $template_id
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'テンプレートを更新しました。'
      ]);
      break;

    case 'DELETE':
      // テンプレート削除
      $input = json_decode(file_get_contents('php://input'), true);

      if (empty($input['template_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'テンプレートIDが指定されていません。']);
        exit;
      }

      $template_id = $input['template_id'];

      // 削除
      $stmt = $pdo->prepare("
        DELETE FROM bulletin_templates
        WHERE tenant_id = :tenant_id AND template_id = :template_id
      ");

      $stmt->execute([
        'tenant_id' => $tenant_id,
        'template_id' => $template_id
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'テンプレートを削除しました。'
      ]);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => '許可されていないメソッドです。']);
      break;
  }
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => 'データベースエラーが発生しました。',
    'error' => $e->getMessage()
  ]);
}


