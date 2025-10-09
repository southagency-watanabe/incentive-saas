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

try {
  $data = json_decode(file_get_contents('php://input'), true);

  // イベントIDが指定されているか
  if (!isset($data['event_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'イベントIDが指定されていません。']);
    exit;
  }

  $event_id = $data['event_id'];

  // イベント情報を取得
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
      multiplier,
      description
    FROM events
    WHERE tenant_id = :tenant_id AND event_id = :event_id
  ");
  $stmt->execute([
    'tenant_id' => $tenant_id,
    'event_id' => $event_id
  ]);
  $event = $stmt->fetch();

  if (!$event) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'イベントが見つかりません。']);
    exit;
  }

  // 既に掲示板に投稿済みかチェック
  $stmt = $pdo->prepare("
    SELECT bulletin_id
    FROM bulletins
    WHERE tenant_id = :tenant_id AND related_event_id = :event_id
  ");
  $stmt->execute([
    'tenant_id' => $tenant_id,
    'event_id' => $event_id
  ]);
  $existing = $stmt->fetch();

  if ($existing) {
    echo json_encode([
      'success' => false,
      'message' => 'このイベントは既に掲示板に投稿されています。'
    ]);
    exit;
  }

  // 掲示板の本文を生成
  $body = "【イベント情報】\n\n";
  $body .= "期間: {$event['start_date']} 〜 {$event['end_date']}\n";
  $body .= "倍率: {$event['multiplier']}倍\n";

  if ($event['repeat_type'] === '毎週' && $event['days_of_week']) {
    $body .= "対象曜日: {$event['days_of_week']}\n";
  } elseif ($event['repeat_type'] === '毎月' && $event['day_of_month']) {
    $day_text = $event['day_of_month'] == 99 ? '月末' : "{$event['day_of_month']}日";
    $body .= "対象日: 毎月{$day_text}\n";
  }

  $body .= "対象: {$event['target_type']}\n";

  if ($event['description']) {
    $body .= "\n{$event['description']}";
  }

  // 新規掲示板ID生成
  $stmt = $pdo->prepare("
    SELECT bulletin_id
    FROM bulletins
    WHERE tenant_id = :tenant_id
    ORDER BY bulletin_id DESC
    LIMIT 1
  ");
  $stmt->execute(['tenant_id' => $tenant_id]);
  $last = $stmt->fetch();

  if ($last) {
    $last_num = intval(substr($last['bulletin_id'], 2));
    $new_id = 'BL' . str_pad($last_num + 1, 6, '0', STR_PAD_LEFT);
  } else {
    $new_id = 'BL000001';
  }

  // 掲示板に投稿
  $stmt = $pdo->prepare("
    INSERT INTO bulletins (
      bulletin_id,
      tenant_id,
      type,
      related_event_id,
      pinned,
      status,
      author,
      title,
      body,
      start_datetime,
      end_datetime
    ) VALUES (
      :bulletin_id,
      :tenant_id,
      'イベント',
      :related_event_id,
      1,
      '公開',
      :author,
      :title,
      :body,
      :start_datetime,
      :end_datetime
    )
  ");

  $stmt->execute([
    'bulletin_id' => $new_id,
    'tenant_id' => $tenant_id,
    'related_event_id' => $event_id,
    'author' => $_SESSION['member_id'],
    'title' => $event['event_name'],
    'body' => $body,
    'start_datetime' => $event['start_date'] . ' 00:00:00',
    'end_datetime' => $event['end_date'] . ' 23:59:59'
  ]);

  // 監査ログ
  $stmt = $pdo->prepare("
    INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
    VALUES (:tenant_id, 'イベント掲示板投稿', 'bulletins', :row_id, :operator, :name, :details)
  ");
  $stmt->execute([
    'tenant_id' => $tenant_id,
    'row_id' => $new_id,
    'operator' => $_SESSION['member_id'],
    'name' => $_SESSION['name'],
    'details' => json_encode([
      'event_id' => $event_id,
      'event_name' => $event['event_name']
    ], JSON_UNESCAPED_UNICODE)
  ]);

  echo json_encode([
    'success' => true,
    'message' => 'イベントを掲示板に投稿しました。',
    'bulletin_id' => $new_id
  ]);
} catch (PDOException $e) {
  error_log('Post event to bulletin error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
