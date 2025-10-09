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
  // 元のメンバーIDが指定されているか
  if (!isset($input['member_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'メンバーIDが指定されていません。']);
    exit;
  }

  $source_member_id = $input['member_id'];

  // 元のメンバー情報を取得
  $stmt = $pdo->prepare("
        SELECT * FROM members 
        WHERE tenant_id = :tenant_id AND member_id = :member_id
    ");

  $stmt->execute([
    'tenant_id' => $tenant_id,
    'member_id' => $source_member_id
  ]);

  $source = $stmt->fetch();

  if (!$source) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'メンバーが見つかりません。']);
    exit;
  }

  // 新しいメンバーID生成（M001形式）
  $stmt = $pdo->prepare("
        SELECT member_id FROM members 
        WHERE tenant_id = :tenant_id 
        ORDER BY member_id DESC LIMIT 1
    ");
  $stmt->execute(['tenant_id' => $tenant_id]);
  $lastMember = $stmt->fetch();

  if ($lastMember) {
    $lastNum = (int)substr($lastMember['member_id'], 1);
    $newNum = $lastNum + 1;
  } else {
    $newNum = 1;
  }
  $new_member_id = 'M' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

  // 新しいログインID生成（元のID + _copy）
  $new_login_id = $source['login_id'] . '_copy';
  $counter = 1;

  // ログインIDが重複しない場合まで繰り返す
  while (true) {
    $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM members 
            WHERE tenant_id = :tenant_id AND login_id = :login_id
        ");
    $stmt->execute([
      'tenant_id' => $tenant_id,
      'login_id' => $new_login_id
    ]);

    if ($stmt->fetchColumn() == 0) {
      break;
    }

    $counter++;
    $new_login_id = $source['login_id'] . '_copy' . $counter;
  }

  // 複製して登録
  $stmt = $pdo->prepare("
        INSERT INTO members (
            member_id, tenant_id, name, team_id, status, 
            login_id, pin, role, description
        ) VALUES (
            :member_id, :tenant_id, :name, :team_id, :status,
            :login_id, :pin, :role, :description
        )
    ");

  $stmt->execute([
    'member_id' => $new_member_id,
    'tenant_id' => $tenant_id,
    'name' => $source['name'] . ' (コピー)',
    'team_id' => $source['team_id'],
    'status' => $source['status'],
    'login_id' => $new_login_id,
    'pin' => $source['pin'],
    'role' => $source['role'],
    'description' => $source['description']
  ]);

  // 監査ログ
  $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, table_name, member_id, operator, user_display_name, details)
        VALUES (:tenant_id, '複製', 'members', :member_id, :operator, :name, :details)
    ");
  $stmt->execute([
    'tenant_id' => $tenant_id,
    'member_id' => $new_member_id,
    'operator' => $_SESSION['member_id'],
    'name' => $_SESSION['name'],
    'details' => json_encode([
      'source_member_id' => $source_member_id,
      'new_member_id' => $new_member_id,
      'new_login_id' => $new_login_id
    ], JSON_UNESCAPED_UNICODE)
  ]);

  echo json_encode([
    'success' => true,
    'message' => 'メンバーを複製しました。',
    'member_id' => $new_member_id,
    'login_id' => $new_login_id
  ]);
} catch (PDOException $e) {
  error_log('Member duplicate error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
