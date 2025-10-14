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
                    t.team_id,
                    t.team_name,
                    t.leader_id,
                    m.name AS leader_name,
                    t.status,
                    t.description,
                    t.created_at,
                    t.updated_at
                FROM teams t
                LEFT JOIN members m ON t.leader_id = m.member_id AND t.tenant_id = m.tenant_id
                WHERE t.tenant_id = :tenant_id
                ORDER BY t.team_id ASC
            ");

      $stmt->execute(['tenant_id' => $tenant_id]);
      $teams = $stmt->fetchAll();

      echo json_encode([
        'success' => true,
        'data' => $teams
      ]);
      break;

    case 'POST':
      // 新規登録
      $input = json_decode(file_get_contents('php://input'), true);

      // バリデーション
      if (empty($input['team_name']) || empty($input['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
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
      $team_id = 'T' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

      // リーダーが指定されている場合、そのメンバーをこのチームに所属させる
      $leader_id = !empty($input['leader_id']) ? $input['leader_id'] : null;

      // 登録
      $stmt = $pdo->prepare("
                INSERT INTO teams (
                    team_id, tenant_id, team_name, leader_id, status, description
                ) VALUES (
                    :team_id, :tenant_id, :team_name, :leader_id, :status, :description
                )
            ");

      $stmt->execute([
        'team_id' => $team_id,
        'tenant_id' => $tenant_id,
        'team_name' => $input['team_name'],
        'leader_id' => $leader_id,
        'status' => $input['status'],
        'description' => $input['description'] ?? null
      ]);

      // リーダーをこのチームに所属させる
      if ($leader_id) {
        $stmt = $pdo->prepare("
                  UPDATE members SET team_id = :team_id
                  WHERE tenant_id = :tenant_id AND member_id = :member_id
              ");
        $stmt->execute([
          'team_id' => $team_id,
          'tenant_id' => $tenant_id,
          'member_id' => $leader_id
        ]);
      }

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
                VALUES (:tenant_id, '新規登録', 'teams', :row_id, :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'row_id' => $pdo->lastInsertId(),
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode($input, JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'チームを登録しました。',
        'team_id' => $team_id
      ]);
      break;

    case 'PUT':
      // 更新
      $input = json_decode(file_get_contents('php://input'), true);

      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'チームIDが指定されていません。']);
        exit;
      }

      $team_id = $_GET['id'];

      // バリデーション
      if (empty($input['team_name']) || empty($input['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // リーダーが指定されている場合、そのメンバーをこのチームに所属させる
      $leader_id = !empty($input['leader_id']) ? $input['leader_id'] : null;

      // 更新
      $stmt = $pdo->prepare("
                UPDATE teams SET
                    team_name = :team_name,
                    leader_id = :leader_id,
                    status = :status,
                    description = :description
                WHERE tenant_id = :tenant_id AND team_id = :team_id
            ");

      $stmt->execute([
        'team_name' => $input['team_name'],
        'leader_id' => $leader_id,
        'status' => $input['status'],
        'description' => $input['description'] ?? null,
        'tenant_id' => $tenant_id,
        'team_id' => $team_id
      ]);

      // リーダーをこのチームに所属させる
      if ($leader_id) {
        $stmt = $pdo->prepare("
                  UPDATE members SET team_id = :team_id
                  WHERE tenant_id = :tenant_id AND member_id = :member_id
              ");
        $stmt->execute([
          'team_id' => $team_id,
          'tenant_id' => $tenant_id,
          'member_id' => $leader_id
        ]);
      }

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
                VALUES (:tenant_id, '更新', 'teams', :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(array_merge(['team_id' => $team_id], $input), JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'チーム情報を更新しました。'
      ]);
      break;

    case 'DELETE':
      // 削除
      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'チームIDが指定されていません。']);
        exit;
      }

      $team_id = $_GET['id'];

      // チームに所属しているメンバーがいないかチェック
      $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM members 
                WHERE tenant_id = :tenant_id AND team_id = :team_id
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'team_id' => $team_id
      ]);

      if ($stmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'このチームに所属しているメンバーがいるため削除できません。']);
        exit;
      }

      $stmt = $pdo->prepare("
                DELETE FROM teams 
                WHERE tenant_id = :tenant_id AND team_id = :team_id
            ");

      $stmt->execute([
        'tenant_id' => $tenant_id,
        'team_id' => $team_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
                VALUES (:tenant_id, '削除', 'teams', :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(['deleted_team_id' => $team_id], JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'チームを削除しました。'
      ]);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  }
} catch (PDOException $e) {
  error_log('Teams API error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
