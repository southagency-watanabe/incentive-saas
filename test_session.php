<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== PHP セッション設定確認 ===\n\n";

echo "session.save_path: " . ini_get('session.save_path') . "\n";
echo "session.save_handler: " . ini_get('session.save_handler') . "\n";
echo "session.gc_probability: " . ini_get('session.gc_probability') . "\n";
echo "session.gc_divisor: " . ini_get('session.gc_divisor') . "\n";
echo "session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . "\n";
echo "session.cookie_path: " . ini_get('session.cookie_path') . "\n";
echo "session.cookie_domain: " . ini_get('session.cookie_domain') . "\n";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "\n";

echo "\n=== セッション保存先の権限確認 ===\n";
$save_path = ini_get('session.save_path');
if (empty($save_path)) {
    $save_path = sys_get_temp_dir();
}
echo "保存先: $save_path\n";
echo "存在: " . (is_dir($save_path) ? 'はい' : 'いいえ') . "\n";
echo "書き込み可能: " . (is_writable($save_path) ? 'はい' : 'いいえ') . "\n";

echo "\n=== テストセッション作成 ===\n";
session_start();
$_SESSION['test'] = 'Hello from session!';
$_SESSION['time'] = time();

echo "セッションID: " . session_id() . "\n";
echo "セッションデータ:\n";
print_r($_SESSION);

session_write_close();

echo "\n=== セッションファイル確認 ===\n";
$session_file = $save_path . '/sess_' . session_id();
echo "ファイルパス: $session_file\n";
echo "ファイル存在: " . (file_exists($session_file) ? 'はい' : 'いいえ') . "\n";
if (file_exists($session_file)) {
    echo "ファイルサイズ: " . filesize($session_file) . " bytes\n";
    echo "ファイル内容:\n" . file_get_contents($session_file) . "\n";
}
