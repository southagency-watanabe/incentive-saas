<?php
// 環境判定（本番環境かローカル環境か）
// サーバーホスト名で判定
$isProduction = (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'southagency.email') !== false);

// 本番環境の設定
if ($isProduction) {
  define('DB_HOST', 'localhost');
  define('DB_NAME', 'xs063745_incentive');
  define('DB_USER', 'xs063745_incen');
  define('DB_PASS', 'Ginowan29');
  define('ENVIRONMENT', 'production');
  define('DEBUG_MODE', '1'); // トラブルシューティング中は1
} else {
  // ローカル環境の設定
  define('DB_HOST', 'localhost');
  define('DB_NAME', 'incentive_local');
  define('DB_USER', 'root');
  define('DB_PASS', '');
  define('ENVIRONMENT', 'local');
  define('DEBUG_MODE', '1');
}

define('DB_CHARSET', 'utf8mb4');


// PDO接続を取得する関数（リトライ機能付き）
function getDB()
{
  static $pdo = null;

  if ($pdo === null) {
    $maxRetries = 3;
    $retryDelay = 1; // 秒
    $lastException = null;

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
      try {
        error_log("Database connection attempt {$attempt}/{$maxRetries}");
        error_log("Connecting to: host=" . DB_HOST . ", db=" . DB_NAME . ", user=" . DB_USER);

        $pdo = new PDO(
          'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
          DB_USER,
          DB_PASS,
          [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 10, // 10秒タイムアウト
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
          ]
        );

        // 接続テスト
        $pdo->query("SELECT 1");

        error_log("Database connection successful on attempt {$attempt}");
        break; // 成功したらループを抜ける

      } catch (PDOException $e) {
        $lastException = $e;
        error_log("Database connection failed on attempt {$attempt}: " . $e->getMessage());
        error_log("Error code: " . $e->getCode());

        if ($attempt < $maxRetries) {
          error_log("Retrying in {$retryDelay} seconds...");
          sleep($retryDelay);
        }
      }
    }

    // 全てのリトライが失敗した場合
    if ($pdo === null && $lastException !== null) {
      error_log('Database connection failed after ' . $maxRetries . ' attempts');
      error_log('Final error: ' . $lastException->getMessage());

      // 詳細なエラーメッセージを返す
      if (DEBUG_MODE === '1') {
        throw new PDOException(
          'データベース接続エラー: ' . $lastException->getMessage() .
          ' (Host: ' . DB_HOST . ', DB: ' . DB_NAME . ')',
          (int)$lastException->getCode(),
          $lastException
        );
      } else {
        throw new PDOException('データベース接続エラーが発生しました。管理者に連絡してください。', (int)$lastException->getCode(), $lastException);
      }
    }
  }

  return $pdo;
}
