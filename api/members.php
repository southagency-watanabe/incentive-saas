<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// 管理者権限チェック
requireAdmin();

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$tenant_id = $_SESSION['tenant_id'];

try {
  switch ($method) {
    case 'GET':
      // 一覧取得
      $stmt = $pdo->prepare("
                SELECT 
                    m.member_id,
                    m.name,
                    m.team_id,
                    t.team_name,
                    m.status,
                    m.login_id,
                    m.role,
                    m.description,
                    m.created_at,
                    m.updated_at
                FROM members m
                LEFT JOIN teams t ON m.tenant_id = t.tenant_id AND m.team_id = t.team_id
                WHERE m.tenant_id = :tenant_id
                ORDER BY m.member_id ASC
            ");

      $stmt->execute(['tenant_id' => $tenant_id]);
      $members = $stmt->fetchAll();

      echo json_encode([
        'success' => true,
        'data' => $members
      ]);
      break;

    case 'POST':
      // 新規登録
      $input = json_decode(file_get_contents('php://input'), true);

      // バリデーション
      if (empty($input['name']) || empty($input['login_id']) || empty($input['pin']) || empty($input['role']) || empty($input['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // PINは4桁チェック
      if (!preg_match('/^\d{4}$/', $input['pin'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'PINは4桁の数字で入力してください。']);
        exit;
      }

      // ログインID重複チェック
      $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM members 
                WHERE tenant_id = :tenant_id AND login_id = :login_id
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'login_id' => $input['login_id']
      ]);

      if ($stmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'このログインIDは既に使用されています。']);
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
      $member_id = 'M' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

      // 登録
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
        'member_id' => $member_id,
        'tenant_id' => $tenant_id,
        'name' => $input['name'],
        'team_id' => $input['team_id'] ?: null,
        'status' => $input['status'],
        'login_id' => $input['login_id'],
        'pin' => $input['pin'],
        'role' => $input['role'],
        'description' => $input['description'] ?? null
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, member_id, operator, user_display_name, details)
                VALUES (:tenant_id, '新規登録', 'members', :member_id, :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'member_id' => $member_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode($input, JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'メンバーを登録しました。',
        'member_id' => $member_id
      ]);
      break;

    case 'PUT':
      // 更新
      parse_str(file_get_contents('php://input'), $_PUT);
      $input = json_decode(file_get_contents('php://input'), true);

      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'メンバーIDが指定されていません。']);
        exit;
      }

      $member_id = $_GET['id'];

      // バリデーション
      if (empty($input['name']) || empty($input['login_id']) || empty($input['role']) || empty($input['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // PINが入力されている場合は4桁チェック
      if (!empty($input['pin']) && !preg_match('/^\d{4}$/', $input['pin'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'PINは4桁の数字で入力してください。']);
        exit;
      }

      // ログインID重複チェック（自分以外）
      $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM members 
                WHERE tenant_id = :tenant_id 
                AND login_id = :login_id 
                AND member_id != :member_id
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'login_id' => $input['login_id'],
        'member_id' => $member_id
      ]);

      if ($stmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'このログインIDは既に使用されています。']);
        exit;
      }

      // 更新（PINが入力されている場合のみ更新）
      if (!empty($input['pin'])) {
        $stmt = $pdo->prepare("
                    UPDATE members SET
                        name = :name,
                        team_id = :team_id,
                        status = :status,
                        login_id = :login_id,
                        pin = :pin,
                        role = :role,
                        description = :description
                    WHERE tenant_id = :tenant_id AND member_id = :member_id
                ");
        $params = [
          'name' => $input['name'],
          'team_id' => $input['team_id'] ?: null,
          'status' => $input['status'],
          'login_id' => $input['login_id'],
          'pin' => $input['pin'],
          'role' => $input['role'],
          'description' => $input['description'] ?? null,
          'tenant_id' => $tenant_id,
          'member_id' => $member_id
        ];
      } else {
        $stmt = $pdo->prepare("
                    UPDATE members SET
                        name = :name,
                        team_id = :team_id,
                        status = :status,
                        login_id = :login_id,
                        role = :role,
                        description = :description
                    WHERE tenant_id = :tenant_id AND member_id = :member_id
                ");
        $params = [
          'name' => $input['name'],
          'team_id' => $input['team_id'] ?: null,
          'status' => $input['status'],
          'login_id' => $input['login_id'],
          'role' => $input['role'],
          'description' => $input['description'] ?? null,
          'tenant_id' => $tenant_id,
          'member_id' => $member_id
        ];
      }

      $stmt->execute($params);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, member_id, operator, user_display_name, details)
                VALUES (:tenant_id, '更新', 'members', :member_id, :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'member_id' => $member_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode($input, JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'メンバー情報を更新しました。'
      ]);
      break;

    case 'DELETE':
      // 削除
      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'メンバーIDが指定されていません。']);
        exit;
      }

      $member_id = $_GET['id'];

      // 自分自身は削除不可
      if ($member_id === $_SESSION['member_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '自分自身は削除できません。']);
        exit;
      }

      $stmt = $pdo->prepare("
                DELETE FROM members 
                WHERE tenant_id = :tenant_id AND member_id = :member_id
            ");

      $stmt->execute([
        'tenant_id' => $tenant_id,
        'member_id' => $member_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, member_id, operator, user_display_name, details)
                VALUES (:tenant_id, '削除', 'members', :member_id, :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'member_id' => $member_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(['deleted_member_id' => $member_id], JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'メンバーを削除しました。'
      ]);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  }
} catch (PDOException $e) {
  error_log('Members API error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
