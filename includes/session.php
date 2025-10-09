<?php
// セッション管理関数

require_once __DIR__ . '/../config/database.php';

// セッション開始
function startSession()
{
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
}

// ログインチェック（リダイレクトあり）
function requireLogin()
{
  startSession();

  if (!isset($_SESSION['token']) || !isset($_SESSION['tenant_id']) || !isset($_SESSION['member_id'])) {
    header('Location: /login.php');
    exit;
  }

  // セッションの有効性をDBで確認
  if (!validateSession($_SESSION['token'])) {
    destroySession();
    header('Location: /login.php?error=session_expired');
    exit;
  }

  // 最終アクセス時刻を更新（スライディング延長）
  updateSessionAccess($_SESSION['token']);
}

// 管理者権限チェック
function requireAdmin()
{
  requireLogin();

  if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die('アクセス権限がありません。');
  }
}

// セッション作成
function createSession($tenant_id, $member_id, $role, $name)
{
  $pdo = getDB();

  // トークン生成
  $token = bin2hex(random_bytes(32));

  // 有効期限（24時間後）
  $expires_at = date('Y-m-d H:i:s', time() + 86400);

  // セッションをDBに保存
  $stmt = $pdo->prepare("
        INSERT INTO sessions (token, tenant_id, member_id, expires_at)
        VALUES (:token, :tenant_id, :member_id, :expires_at)
    ");

  $stmt->execute([
    'token' => $token,
    'tenant_id' => $tenant_id,
    'member_id' => $member_id,
    'expires_at' => $expires_at
  ]);

  // PHPセッションに保存
  startSession();
  $_SESSION['token'] = $token;
  $_SESSION['tenant_id'] = $tenant_id;
  $_SESSION['member_id'] = $member_id;
  $_SESSION['role'] = $role;
  $_SESSION['name'] = $name;

  return $token;
}

// セッション検証
function validateSession($token)
{
  $pdo = getDB();

  $stmt = $pdo->prepare("
        SELECT * FROM sessions 
        WHERE token = :token 
        AND expires_at > NOW()
    ");

  $stmt->execute(['token' => $token]);
  $session = $stmt->fetch();

  return $session !== false;
}

// 最終アクセス時刻更新
function updateSessionAccess($token)
{
  $pdo = getDB();

  $stmt = $pdo->prepare("
        UPDATE sessions 
        SET last_accessed_at = NOW() 
        WHERE token = :token
    ");

  $stmt->execute(['token' => $token]);
}

// セッション破棄
function destroySession()
{
  startSession();

  if (isset($_SESSION['token'])) {
    $pdo = getDB();

    // DBからセッション削除
    $stmt = $pdo->prepare("DELETE FROM sessions WHERE token = :token");
    $stmt->execute(['token' => $_SESSION['token']]);
  }

  // PHPセッション破棄
  $_SESSION = [];
  session_destroy();
}

// 期限切れセッションのクリーンアップ（定期実行推奨）
function cleanupExpiredSessions()
{
  $pdo = getDB();
  $stmt = $pdo->prepare("DELETE FROM sessions WHERE expires_at < NOW()");
  $stmt->execute();
}
