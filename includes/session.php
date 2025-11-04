<?php
// セッション管理関数

require_once __DIR__ . '/../config/database.php';

// セッション開始
function startSession()
{
  if (session_status() === PHP_SESSION_NONE) {
    // セッション保存パスを設定（書き込み可能なディレクトリ）
    $sessionPath = __DIR__ . '/../tmp/sessions';
    if (!is_dir($sessionPath)) {
      mkdir($sessionPath, 0777, true);
    }
    // 既存のディレクトリのパーミッションも確認
    if (is_dir($sessionPath) && !is_writable($sessionPath)) {
      chmod($sessionPath, 0777);
    }
    session_save_path($sessionPath);

    // セッションのセキュリティ設定（本番用：緩和設定）
    ini_set('session.cookie_httponly', '0');      // JavaScriptアクセス許可
    ini_set('session.cookie_secure', '0');        // HTTP許可
    ini_set('session.cookie_samesite', '');       // SameSite制限なし
    ini_set('session.use_strict_mode', '0');      // 厳格モードOFF
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_lifetime', 0);        // ブラウザ閉じるまで
    ini_set('session.gc_maxlifetime', 86400);     // 24時間

    session_start();

    // デバッグログ
    error_log('startSession - Session started, ID: ' . session_id());
  } else {
    error_log('startSession - Session already active, ID: ' . session_id());
  }
}

// APIリクエストかどうかを判定
function isApiRequest()
{
  // URLが /api/ で始まる場合
  $requestUri = $_SERVER['REQUEST_URI'] ?? '';
  if (strpos($requestUri, '/api/') === 0) {
    return true;
  }

  // Content-Typeがapplication/jsonの場合
  $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
  if (strpos($contentType, 'application/json') !== false) {
    return true;
  }

  // Acceptヘッダーにapplication/jsonが含まれる場合
  $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
  if (strpos($accept, 'application/json') !== false) {
    return true;
  }

  return false;
}

// ログインチェック（リダイレクトあり）
function requireLogin()
{
  startSession();

  // 【テスト用】認証チェックを一時的に無効化
  // テスト用のダミーセッションデータを設定
  if (!isset($_SESSION['tenant_id'])) {
    $_SESSION['token'] = 'test_token_' . bin2hex(random_bytes(16));
    $_SESSION['tenant_id'] = 'DEMO01';  // テスト用テナントID
    $_SESSION['member_id'] = 'MEM001';  // テスト用メンバーID
    $_SESSION['role'] = 'admin';        // テスト用ロール
    $_SESSION['name'] = '高濱 太郎';
  }

  return; // 認証チェックをスキップ

  /* 以下の認証チェックを一時的にコメントアウト
  // デバッグログ
  error_log('requireLogin - Session ID: ' . session_id());
  error_log('requireLogin - $_SESSION contents: ' . json_encode($_SESSION));
  error_log('requireLogin - token isset: ' . (isset($_SESSION['token']) ? 'yes' : 'no'));

  if (!isset($_SESSION['token']) || !isset($_SESSION['tenant_id']) || !isset($_SESSION['member_id'])) {
    error_log('requireLogin - Session validation failed: missing session variables');
    if (isApiRequest()) {
      header('Content-Type: application/json; charset=utf-8');
      http_response_code(401);
      echo json_encode(['success' => false, 'message' => '認証が必要です。', 'error' => 'unauthorized']);
      exit;
    }
    header('Location: /login.php');
    exit;
  }

  // セッションの有効性をDBで確認
  if (!validateSession($_SESSION['token'])) {
    destroySession();
    if (isApiRequest()) {
      header('Content-Type: application/json; charset=utf-8');
      http_response_code(401);
      echo json_encode(['success' => false, 'message' => 'セッションの有効期限が切れました。', 'error' => 'session_expired']);
      exit;
    }
    header('Location: /login.php?error=session_expired');
    exit;
  }

  // 最終アクセス時刻を更新（スライディング延長）
  updateSessionAccess($_SESSION['token']);
  */
}

// 管理者権限チェック
function requireAdmin()
{
  requireLogin();

  // 【テスト用】管理者権限チェックを一時的に無効化
  return; // 権限チェックをスキップ

  /* 以下の権限チェックを一時的にコメントアウト
  if ($_SESSION['role'] !== 'admin') {
    if (isApiRequest()) {
      header('Content-Type: application/json; charset=utf-8');
      http_response_code(403);
      echo json_encode(['success' => false, 'message' => 'アクセス権限がありません。', 'error' => 'forbidden']);
      exit;
    }
    http_response_code(403);
    die('アクセス権限がありません。');
  }
  */
}

// セッション作成
function createSession($tenant_id, $member_id, $role, $name)
{
  $pdo = getDB();

  // タイムゾーンを設定
  date_default_timezone_set('Asia/Tokyo');
  $pdo->exec("SET time_zone = '+09:00'");

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

  // デバッグログ
  error_log('createSession - Session created for member: ' . $member_id);
  error_log('createSession - Session ID: ' . session_id());
  error_log('createSession - Session data: ' . json_encode($_SESSION));

  // セッションはそのまま開いたままにする（本番用：セッション維持）
  // session_write_close() はコメントアウト

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

  // デバッグログ
  if (DEBUG_MODE === '1') {
    error_log('validateSession - token: ' . substr($token, 0, 10) . '...');
    error_log('validateSession - result: ' . ($session ? 'found' : 'not found'));
    if ($session) {
      error_log('validateSession - expires_at: ' . $session['expires_at']);
    }
  }

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
