<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';

// 管理者権限チェック
requireAdmin();

// GETリクエストのみ
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  exit;
}

$pdo = getDB();
$tenant_id = $_SESSION['tenant_id'];

try {
  // ログインIDが指定されているか
  if (!isset($_GET['login_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ログインIDが指定されていません。']);
    exit;
  }

  $login_id = trim($_GET['login_id']);

  // 編集時は自分自身を除外（exclude パラメータ）
  $exclude_member_id = $_GET['exclude'] ?? null;

  if ($exclude_member_id) {
    // 編集時：自分以外で重複チェック
    $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM members 
            WHERE tenant_id = :tenant_id 
            AND login_id = :login_id 
            AND member_id != :exclude
        ");

    $stmt->execute([
      'tenant_id' => $tenant_id,
      'login_id' => $login_id,
      'exclude' => $exclude_member_id
    ]);
  } else {
    // 新規登録時：重複チェック
    $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM members 
            WHERE tenant_id = :tenant_id 
            AND login_id = :login_id
        ");

    $stmt->execute([
      'tenant_id' => $tenant_id,
      'login_id' => $login_id
    ]);
  }

  $count = $stmt->fetchColumn();

  echo json_encode([
    'success' => true,
    'available' => $count == 0,
    'message' => $count > 0 ? 'このログインIDは既に使用されています。' : '使用可能なログインIDです。'
  ]);
} catch (PDOException $e) {
  error_log('Check login ID error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
