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
  // 元のアクションIDが指定されているか
  if (!isset($input['action_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'アクションIDが指定されていません。']);
    exit;
  }

  $source_action_id = $input['action_id'];

  // 元のアクション情報を取得
  $stmt = $pdo->prepare("
        SELECT * FROM actions 
        WHERE tenant_id = :tenant_id AND action_id = :action_id
    ");

  $stmt->execute([
    'tenant_id' => $tenant_id,
    'action_id' => $source_action_id
  ]);

  $source = $stmt->fetch();

  if (!$source) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'アクションが見つかりません。']);
    exit;
  }

  // 新しいアクションID生成（ACT001形式）
  $stmt = $pdo->prepare("
        SELECT action_id FROM actions 
        WHERE tenant_id = :tenant_id 
        ORDER BY action_id DESC LIMIT 1
    ");
  $stmt->execute(['tenant_id' => $tenant_id]);
  $lastAction = $stmt->fetch();

  if ($lastAction) {
    $lastNum = (int)substr($lastAction['action_id'], 3);
    $newNum = $lastNum + 1;
  } else {
    $newNum = 1;
  }
  $new_action_id = 'ACT' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

  // 複製して登録
  $stmt = $pdo->prepare("
        INSERT INTO actions (
            action_id, tenant_id, action_name, target, status,
            description, point, approval_required
        ) VALUES (
            :action_id, :tenant_id, :action_name, :target, :status,
            :description, :point, :approval_required
        )
    ");

  $stmt->execute([
    'action_id' => $new_action_id,
    'tenant_id' => $tenant_id,
    'action_name' => $source['action_name'] . ' (コピー)',
    'target' => $source['target'],
    'status' => $source['status'],
    'description' => $source['description'],
    'point' => $source['point'],
    'approval_required' => $source['approval_required']
  ]);

  // 監査ログ
  $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
        VALUES (:tenant_id, '複製', 'actions', :operator, :name, :details)
    ");
  $stmt->execute([
    'tenant_id' => $tenant_id,
    'operator' => $_SESSION['member_id'],
    'name' => $_SESSION['name'],
    'details' => json_encode([
      'source_action_id' => $source_action_id,
      'new_action_id' => $new_action_id
    ], JSON_UNESCAPED_UNICODE)
  ]);

  echo json_encode([
    'success' => true,
    'message' => 'アクションを複製しました。',
    'action_id' => $new_action_id
  ]);
} catch (PDOException $e) {
  error_log('Action duplicate error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
