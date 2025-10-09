<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

startSession();

// ログイン中の場合のみログアウト処理
if (isset($_SESSION['member_id'])) {
  try {
    $pdo = getDB();

    // 監査ログ記録
    $stmt = $pdo->prepare("
            INSERT INTO audit_logs (tenant_id, action, member_id, operator, user_display_name, details)
            VALUES (:tenant_id, 'ログアウト', :member_id, :operator, :name, :details)
        ");

    $stmt->execute([
      'tenant_id' => $_SESSION['tenant_id'],
      'member_id' => $_SESSION['member_id'],
      'operator' => $_SESSION['member_id'],
      'name' => $_SESSION['name'] ?? '',
      'details' => json_encode([
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
      ])
    ]);
  } catch (PDOException $e) {
    error_log('Logout audit log error: ' . $e->getMessage());
  }
}

// セッション破棄
destroySession();

// ログイン画面にリダイレクト
header('Location: /login.php');
exit;
