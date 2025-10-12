<?php
// データベース接続設定（本番環境用）
define('DB_HOST', 'localhost');
define('DB_NAME', 'xs063745_incentive');
define('DB_USER', 'xs063745_incen');
define('DB_PASS', 'Ginowan29');
define('DB_CHARSET', 'utf8mb4');

// PDO接続を取得する関数
function getDB()
{
  static $pdo = null;

  if ($pdo === null) {
    try {
      $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false,
        ]
      );
      
      // 文字セットを明示的に設定
      $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    } catch (PDOException $e) {
      // 本番環境ではエラー詳細を隠す
      error_log('Database connection failed: ' . $e->getMessage());
      die('データベース接続エラーが発生しました。');
    }
  }

  return $pdo;
}
