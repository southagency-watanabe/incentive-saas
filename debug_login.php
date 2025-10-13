<?php
require_once __DIR__ . '/includes/session.php';

startSession();

header('Content-Type: text/plain; charset=utf-8');

echo "=== セッションデバッグ情報 ===\n\n";

echo "セッションID: " . session_id() . "\n";
echo "セッションステータス: " . session_status() . " (1=無効, 2=有効)\n\n";

echo "=== $_SESSION の内容 ===\n";
print_r($_SESSION);

echo "\n=== セッションクッキー情報 ===\n";
echo "Cookie名: " . session_name() . "\n";
echo "Cookieパラメータ:\n";
print_r(session_get_cookie_params());

echo "\n=== $_COOKIE の内容 ===\n";
print_r($_COOKIE);

echo "\n=== データベースのセッション情報 ===\n";
if (isset($_SESSION['token'])) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE token = :token");
        $stmt->execute(['token' => $_SESSION['token']]);
        $session = $stmt->fetch();

        if ($session) {
            echo "DBセッション見つかりました:\n";
            print_r($session);
        } else {
            echo "DBにセッションが見つかりません\n";
        }
    } catch (Exception $e) {
        echo "エラー: " . $e->getMessage() . "\n";
    }
} else {
    echo "セッションにtokenがありません\n";
}
