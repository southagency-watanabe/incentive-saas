<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

startSession();

$pdo = getDB();

$debug_info = [
  'session_id' => session_id(),
  'session_data' => $_SESSION,
  'cookies' => $_COOKIE,
  'session_status' => session_status(),
  'db_session' => null
];

// DBのセッション情報を取得
if (isset($_SESSION['token'])) {
  $stmt = $pdo->prepare("
    SELECT token, tenant_id, member_id, expires_at, last_accessed_at, created_at,
           (expires_at > NOW()) as is_valid,
           TIMESTAMPDIFF(SECOND, NOW(), expires_at) as seconds_until_expiry
    FROM sessions
    WHERE token = :token
  ");
  $stmt->execute(['token' => $_SESSION['token']]);
  $debug_info['db_session'] = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 全セッションを取得（デバッグ用）
$stmt = $pdo->prepare("
  SELECT token, tenant_id, member_id, expires_at, last_accessed_at, created_at,
         (expires_at > NOW()) as is_valid
  FROM sessions
  ORDER BY created_at DESC
  LIMIT 5
");
$stmt->execute();
$debug_info['all_sessions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($debug_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
