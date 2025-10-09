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
  // 元のタスクIDが指定されているか
  if (!isset($input['task_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'タスクIDが指定されていません。']);
    exit;
  }

  $source_task_id = $input['task_id'];

  // 元のタスク情報を取得
  $stmt = $pdo->prepare("
        SELECT * FROM tasks 
        WHERE tenant_id = :tenant_id AND task_id = :task_id
    ");

  $stmt->execute([
    'tenant_id' => $tenant_id,
    'task_id' => $source_task_id
  ]);

  $source = $stmt->fetch();

  if (!$source) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'タスクが見つかりません。']);
    exit;
  }

  // 新しいタスクID生成（TSK001形式）
  $stmt = $pdo->prepare("
        SELECT task_id FROM tasks 
        WHERE tenant_id = :tenant_id 
        ORDER BY task_id DESC LIMIT 1
    ");
  $stmt->execute(['tenant_id' => $tenant_id]);
  $lastTask = $stmt->fetch();

  if ($lastTask) {
    $lastNum = (int)substr($lastTask['task_id'], 3);
    $newNum = $lastNum + 1;
  } else {
    $newNum = 1;
  }
  $new_task_id = 'TSK' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

  // 複製して登録
  $stmt = $pdo->prepare("
        INSERT INTO tasks (
            task_id, tenant_id, task_name, type, repeat_type,
            days_of_week, day_of_month, point, daily_limit,
            approval_required, status, description
        ) VALUES (
            :task_id, :tenant_id, :task_name, :type, :repeat_type,
            :days_of_week, :day_of_month, :point, :daily_limit,
            :approval_required, :status, :description
        )
    ");

  $stmt->execute([
    'task_id' => $new_task_id,
    'tenant_id' => $tenant_id,
    'task_name' => $source['task_name'] . ' (コピー)',
    'type' => $source['type'],
    'repeat_type' => $source['repeat_type'],
    'days_of_week' => $source['days_of_week'],
    'day_of_month' => $source['day_of_month'],
    'point' => $source['point'],
    'daily_limit' => $source['daily_limit'],
    'approval_required' => $source['approval_required'],
    'status' => $source['status'],
    'description' => $source['description']
  ]);

  // 監査ログ
  $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
        VALUES (:tenant_id, '複製', 'tasks', :operator, :name, :details)
    ");
  $stmt->execute([
    'tenant_id' => $tenant_id,
    'operator' => $_SESSION['member_id'],
    'name' => $_SESSION['name'],
    'details' => json_encode([
      'source_task_id' => $source_task_id,
      'new_task_id' => $new_task_id
    ], JSON_UNESCAPED_UNICODE)
  ]);

  echo json_encode([
    'success' => true,
    'message' => 'タスクを複製しました。',
    'task_id' => $new_task_id
  ]);
} catch (PDOException $e) {
  error_log('Task duplicate error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
