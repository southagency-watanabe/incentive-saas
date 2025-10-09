<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/session.php';

// 管理者権限チェック
requireAdmin();

// POSTリクエストのみ
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  exit;
}

$pdo = getDB();
$tenant_id = $_SESSION['tenant_id'];
$input = json_decode(file_get_contents('php://input'), true);

try {
  // 元のチームIDが指定されているか
  if (!isset($input['team_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'チームIDが指定されていません。']);
    exit;
  }

  $source_team_id = $input['team_id'];

  // 元のチーム情報を取得
  $stmt = $pdo->prepare("
        SELECT * FROM teams 
        WHERE tenant_id = :tenant_id AND team_id = :team_id
    ");

  $stmt->execute([
    'tenant_id' => $tenant_id,
    'team_id' => $source_team_id
  ]);

  $source = $stmt->fetch();

  if (!$source) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'チームが見つかりません。']);
    exit;
  }

  // 新しいチームID生成（T001形式）
  $stmt = $pdo->prepare("
        SELECT team_id FROM teams 
        WHERE tenant_id = :tenant_id 
        ORDER BY team_id DESC LIMIT 1
    ");
  $stmt->execute(['tenant_id' => $tenant_id]);
  $lastTeam = $stmt->fetch();

  if ($lastTeam) {
    $lastNum = (int)substr($lastTeam['team_id'], 1);
    $newNum = $lastNum + 1;
  } else {
    $newNum = 1;
  }
  $new_team_id = 'T' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

  // 複製して登録
  $stmt = $pdo->prepare("
        INSERT INTO teams (
            team_id, tenant_id, team_name, status, description
        ) VALUES (
            :team_id, :tenant_id, :team_name, :status, :description
        )
    ");

  $stmt->execute([
    'team_id' => $new_team_id,
    'tenant_id' => $tenant_id,
    'team_name' => $source['team_name'] . ' (コピー)',
    'status' => $source['status'],
    'description' => $source['description']
  ]);

  // 監査ログ
  $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
        VALUES (:tenant_id, '複製', 'teams', :operator, :name, :details)
    ");
  $stmt->execute([
    'tenant_id' => $tenant_id,
    'operator' => $_SESSION['member_id'],
    'name' => $_SESSION['name'],
    'details' => json_encode([
      'source_team_id' => $source_team_id,
      'new_team_id' => $new_team_id
    ], JSON_UNESCAPED_UNICODE)
  ]);

  echo json_encode([
    'success' => true,
    'message' => 'チームを複製しました。',
    'team_id' => $new_team_id
  ]);
} catch (PDOException $e) {
  error_log('Team duplicate error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
