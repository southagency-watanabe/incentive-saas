<?php
// データベース接続設定（ローカル開発用）
define('DB_HOST', 'localhost');
define('DB_NAME', 'incentive_local');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// PDO接続を取得する関数
function getDB()
{
  static $pdo = null;

  if ($pdo === null) {
    try {
      $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false,
        ]
      );
    } catch (PDOException $e) {
      // 本番環境ではエラー詳細を隠す
      error_log('Database connection failed: ' . $e->getMessage());
      die('データベース接続エラーが発生しました。');
    }
  }

  return $pdo;
}
