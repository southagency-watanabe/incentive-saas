<?php
// 環境変数を読み込む関数
function loadEnv($filePath = __DIR__ . '/../.env')
{
  if (!file_exists($filePath)) {
    return;
  }

  $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    // コメント行をスキップ
    if (strpos(trim($line), '#') === 0) {
      continue;
    }

    // KEY=VALUE 形式をパース
    if (strpos($line, '=') !== false) {
      list($key, $value) = explode('=', $line, 2);
      $key = trim($key);
      $value = trim($value);

      // 既存の環境変数を上書きしない
      if (!array_key_exists($key, $_ENV)) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
      }
    }
  }
}

// .envファイルを読み込み
loadEnv();

// 環境変数からデータベース設定を取得（デフォルト値付き）
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'incentive_local');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'local');
define('DEBUG_MODE', getenv('DEBUG_MODE') ?: '0');


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

      if (DEBUG_MODE === '1') {
        die('データベース接続エラー: ' . $e->getMessage());
      } else {
        die('データベース接続エラーが発生しました。');
      }
    }
  }

  return $pdo;
}
