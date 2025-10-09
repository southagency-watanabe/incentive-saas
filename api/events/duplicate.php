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
  // 元のイベントIDが指定されているか
  if (!isset($input['event_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'イベントIDが指定されていません。']);
    exit;
  }

  $source_event_id = $input['event_id'];

  // 元のイベント情報を取得
  $stmt = $pdo->prepare("
        SELECT * FROM events 
        WHERE tenant_id = :tenant_id AND event_id = :event_id
    ");

  $stmt->execute([
    'tenant_id' => $tenant_id,
    'event_id' => $source_event_id
  ]);

  $source = $stmt->fetch();

  if (!$source) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'イベントが見つかりません。']);
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
  $new_event_id = 'EVT' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

  // 複製して登録
  $stmt = $pdo->prepare("
        INSERT INTO events (
            event_id, tenant_id, event_name, repeat_type, start_date, end_date,
            days_of_week, day_of_month, target_type, target_ids, multiplier,
            status, description, publish_notice, notice_title, notice_body
        ) VALUES (
            :event_id, :tenant_id, :event_name, :repeat_type, :start_date, :end_date,
            :days_of_week, :day_of_month, :target_type, :target_ids, :multiplier,
            :status, :description, :publish_notice, :notice_title, :notice_body
        )
    ");

  $stmt->execute([
    'event_id' => $new_event_id,
    'tenant_id' => $tenant_id,
    'event_name' => $source['event_name'] . ' (コピー)',
    'repeat_type' => $source['repeat_type'],
    'start_date' => $source['start_date'],
    'end_date' => $source['end_date'],
    'days_of_week' => $source['days_of_week'],
    'day_of_month' => $source['day_of_month'],
    'target_type' => $source['target_type'],
    'target_ids' => $source['target_ids'],
    'multiplier' => $source['multiplier'],
    'status' => $source['status'],
    'description' => $source['description'],
    'publish_notice' => $source['publish_notice'],
    'notice_title' => $source['notice_title'],
    'notice_body' => $source['notice_body']
  ]);

  // 監査ログ
  $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
        VALUES (:tenant_id, '複製', 'events', :operator, :name, :details)
    ");
  $stmt->execute([
    'tenant_id' => $tenant_id,
    'operator' => $_SESSION['member_id'],
    'name' => $_SESSION['name'],
    'details' => json_encode([
      'source_event_id' => $source_event_id,
      'new_event_id' => $new_event_id
    ], JSON_UNESCAPED_UNICODE)
  ]);

  echo json_encode([
    'success' => true,
    'message' => 'イベントを複製しました。',
    'event_id' => $new_event_id
  ]);
} catch (PDOException $e) {
  error_log('Event duplicate error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
