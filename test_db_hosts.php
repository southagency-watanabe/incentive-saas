<?php
// 異なるホストでのデータベース接続テスト

$hosts = [
  'localhost',
  '127.0.0.1',
  'mysql.xserver.jp',
  'mysql1.xserver.jp',
  'mysql2.xserver.jp',
  'mysql3.xserver.jp',
];

$db_name = 'xs063745_incentive';
$db_user = 'xs063745_incen';
$db_pass = 'Ginowan29';

echo "Testing different MySQL hosts...\n";
echo "================================\n\n";

foreach ($hosts as $host) {
  echo "Testing: $host ... ";
  try {
    $pdo = new PDO(
      "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
      $db_user,
      $db_pass,
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ SUCCESS!\n";
    echo "  → Use this host: $host\n\n";
    break; // 成功したら終了
  } catch (PDOException $e) {
    echo "✗ FAILED\n";
    echo "  Error: " . $e->getMessage() . "\n\n";
  }
}
