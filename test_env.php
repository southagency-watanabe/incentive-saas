<?php
require_once __DIR__ . '/config/database.php';

echo "Environment Variables Test\n";
echo "==========================\n\n";

echo "ENVIRONMENT: " . ENVIRONMENT . "\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (DB_PASS ? '***SET***' : '(empty)') . "\n";
echo "DEBUG_MODE: " . DEBUG_MODE . "\n";
echo "\n";

echo "Testing database connection...\n";
try {
  $pdo = getDB();
  echo "SUCCESS: Database connection established!\n";

  // テーブル一覧を取得
  $stmt = $pdo->query("SHOW TABLES");
  $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
  echo "Found " . count($tables) . " tables\n";
} catch (PDOException $e) {
  echo "ERROR: " . $e->getMessage() . "\n";
}
