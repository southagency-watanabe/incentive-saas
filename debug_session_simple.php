<?php
require_once __DIR__ . '/includes/session.php';

startSession();

header('Content-Type: text/plain; charset=utf-8');

echo "セッションデバッグ情報\n";
echo str_repeat("=", 50) . "\n\n";

echo "【セッションID】\n";
echo "PHPSESSID: " . session_id() . "\n\n";

echo "【$_SESSION の内容】\n";
if (!empty($_SESSION)) {
  foreach ($_SESSION as $key => $value) {
    if ($key === 'token') {
      echo "$key: " . substr($value, 0, 20) . "... (先頭20文字のみ表示)\n";
    } else {
      echo "$key: " . (is_string($value) ? $value : json_encode($value)) . "\n";
    }
  }
} else {
  echo "❌ セッション変数が空です！\n";
}

echo "\n【Cookieの確認】\n";
if (isset($_COOKIE['PHPSESSID'])) {
  echo "✓ PHPSESSIDクッキー存在: " . $_COOKIE['PHPSESSID'] . "\n";
} else {
  echo "❌ PHPSESSIDクッキーが見つかりません\n";
}

echo "\n【データベースセッション確認】\n";
if (isset($_SESSION['token'])) {
  try {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE token = :token");
    $stmt->execute(['token' => $_SESSION['token']]);
    $dbSession = $stmt->fetch();

    if ($dbSession) {
      echo "✓ DBにセッション存在\n";
      echo "  tenant_id: " . $dbSession['tenant_id'] . "\n";
      echo "  member_id: " . $dbSession['member_id'] . "\n";
      echo "  expires_at: " . $dbSession['expires_at'] . "\n";
      echo "  現在時刻: " . date('Y-m-d H:i:s') . "\n";

      $expired = strtotime($dbSession['expires_at']) < time();
      echo "  期限切れ: " . ($expired ? '❌ YES' : '✓ NO') . "\n";
    } else {
      echo "❌ DBにセッションが見つかりません\n";
    }
  } catch (Exception $e) {
    echo "❌ エラー: " . $e->getMessage() . "\n";
  }
} else {
  echo "❌ \$_SESSION['token'] が設定されていません\n";
}

echo "\n【診断】\n";
if (empty($_SESSION)) {
  echo "問題: セッション変数が保存されていません\n";
  echo "原因候補:\n";
  echo "  1. セッションディレクトリの書き込み権限\n";
  echo "  2. Cookie設定の問題\n";
  echo "  3. session_start()が正しく動いていない\n";
} elseif (!isset($_SESSION['token'])) {
  echo "問題: ログイン情報が保存されていません\n";
  echo "原因: ログインが正常に完了していない可能性\n";
} else {
  echo "✓ セッションは正常に動作しているようです\n";
}
