<?php
// デバッグ用：セッション状態確認ページ
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';

startSession();

echo "<h1>セッションデバッグ情報</h1>";
echo "<h2>PHPセッション変数</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Cookie情報</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "<h2>データベース接続テスト</h2>";
try {
    $pdo = getDB();
    echo "<p style='color: green;'>✓ データベース接続成功</p>";

    // セッションテーブル確認
    if (isset($_SESSION['token'])) {
        echo "<h3>セッションテーブル確認（token: " . htmlspecialchars($_SESSION['token']) . "）</h3>";
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE token = :token");
        $stmt->execute(['token' => $_SESSION['token']]);
        $session = $stmt->fetch();

        if ($session) {
            echo "<p style='color: green;'>✓ セッションレコードが見つかりました</p>";
            echo "<pre>";
            print_r($session);
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>✗ セッションレコードが見つかりません</p>";
        }
    } else {
        echo "<p style='color: orange;'>セッションにtokenが設定されていません</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ データベース接続エラー: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>サーバー情報</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "HTTPS: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'on' : 'off') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "\n";
echo "</pre>";
?>
