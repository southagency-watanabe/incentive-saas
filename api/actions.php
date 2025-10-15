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
                    action_id,
                    action_name,
                    category,
                    repeat_type,
                    start_date,
                    end_date,
                    days_of_week,
                    day_of_month,
                    target,
                    status,
                    description,
                    point,
                    approval_required,
                    created_at,
                    updated_at
                FROM actions
                WHERE tenant_id = :tenant_id
                ORDER BY action_id ASC
            ");

      $stmt->execute(['tenant_id' => $tenant_id]);
      $actions = $stmt->fetchAll();

      // approval_requiredを「必要」「不要」に変換
      foreach ($actions as &$action) {
        $action['approval_required'] = $action['approval_required'] ? '必要' : '不要';
      }

      echo json_encode([
        'success' => true,
        'data' => $actions
      ]);
      break;

    case 'POST':
      // 新規登録
      $input = json_decode(file_get_contents('php://input'), true);

      // バリデーション
      if (empty($input['action_name']) || empty($input['repeat_type']) || empty($input['start_date']) || empty($input['end_date']) || empty($input['target']) || !isset($input['point']) || empty($input['status']) || empty($input['approval_required'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // 対象チェック
      if (!in_array($input['target'], ['個人', 'チーム'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '対象は「個人」または「チーム」を選択してください。']);
        exit;
      }

      // 数値チェック
      if (!is_numeric($input['point'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '付与ptは数値で入力してください。']);
        exit;
      }

      // 新しいアクションID生成（ACT001形式）
      $stmt = $pdo->prepare("
                SELECT action_id FROM actions 
                WHERE tenant_id = :tenant_id 
                ORDER BY action_id DESC LIMIT 1
            ");
      $stmt->execute(['tenant_id' => $tenant_id]);
      $lastAction = $stmt->fetch();

      if ($lastAction) {
        $lastNum = (int)substr($lastAction['action_id'], 3);
        $newNum = $lastNum + 1;
      } else {
        $newNum = 1;
      }
      $action_id = 'ACT' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

      // approval_requiredを0/1に変換
      $approval_required_bool = ($input['approval_required'] === '必要') ? 1 : 0;

      // 登録
      $stmt = $pdo->prepare("
                INSERT INTO actions (
                    action_id, tenant_id, action_name, category, repeat_type, start_date, end_date,
                    days_of_week, day_of_month, target, status,
                    description, point, approval_required
                ) VALUES (
                    :action_id, :tenant_id, :action_name, :category, :repeat_type, :start_date, :end_date,
                    :days_of_week, :day_of_month, :target, :status,
                    :description, :point, :approval_required
                )
            ");

      $stmt->execute([
        'action_id' => $action_id,
        'tenant_id' => $tenant_id,
        'action_name' => $input['action_name'],
        'category' => !empty($input['category']) ? $input['category'] : null,
        'repeat_type' => $input['repeat_type'],
        'start_date' => $input['start_date'],
        'end_date' => $input['end_date'],
        'days_of_week' => $input['days_of_week'] ?? null,
        'day_of_month' => $input['day_of_month'] ?? null,
        'target' => $input['target'],
        'status' => $input['status'],
        'description' => $input['description'] ?? null,
        'point' => $input['point'],
        'approval_required' => $approval_required_bool
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
                VALUES (:tenant_id, '新規登録', 'actions', :row_id, :operator, :name, :details)
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
        'message' => 'アクションを登録しました。',
        'action_id' => $action_id
      ]);
      break;

    case 'PUT':
      // 更新
      $input = json_decode(file_get_contents('php://input'), true);

      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'アクションIDが指定されていません。']);
        exit;
      }

      $action_id = $_GET['id'];

      // バリデーション
      if (empty($input['action_name']) || empty($input['repeat_type']) || empty($input['start_date']) || empty($input['end_date']) || empty($input['target']) || !isset($input['point']) || empty($input['status']) || empty($input['approval_required'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // 対象チェック
      if (!in_array($input['target'], ['個人', 'チーム'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '対象は「個人」または「チーム」を選択してください。']);
        exit;
      }

      // 数値チェック
      if (!is_numeric($input['point'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '付与ptは数値で入力してください。']);
        exit;
      }

      // approval_requiredを0/1に変換
      $approval_required_bool = ($input['approval_required'] === '必要') ? 1 : 0;

      // 更新
      $stmt = $pdo->prepare("
                UPDATE actions SET
                    action_name = :action_name,
                    category = :category,
                    repeat_type = :repeat_type,
                    start_date = :start_date,
                    end_date = :end_date,
                    days_of_week = :days_of_week,
                    day_of_month = :day_of_month,
                    target = :target,
                    status = :status,
                    description = :description,
                    point = :point,
                    approval_required = :approval_required
                WHERE tenant_id = :tenant_id AND action_id = :action_id
            ");

      $stmt->execute([
        'action_name' => $input['action_name'],
        'category' => !empty($input['category']) ? $input['category'] : null,
        'repeat_type' => $input['repeat_type'],
        'start_date' => $input['start_date'],
        'end_date' => $input['end_date'],
        'days_of_week' => $input['days_of_week'] ?? null,
        'day_of_month' => $input['day_of_month'] ?? null,
        'target' => $input['target'],
        'status' => $input['status'],
        'description' => $input['description'] ?? null,
        'point' => $input['point'],
        'approval_required' => $approval_required_bool,
        'tenant_id' => $tenant_id,
        'action_id' => $action_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
                VALUES (:tenant_id, '更新', 'actions', :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(array_merge(['action_id' => $action_id], $input), JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'アクション情報を更新しました。'
      ]);
      break;

    case 'DELETE':
      // 削除
      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'アクションIDが指定されていません。']);
        exit;
      }

      $action_id = $_GET['id'];

      // アクション実績で使用されていないかチェック
      $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM action_records 
                WHERE tenant_id = :tenant_id AND action_id = :action_id
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'action_id' => $action_id
      ]);

      if ($stmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'このアクションは実績で使用されているため削除できません。']);
        exit;
      }

      $stmt = $pdo->prepare("
                DELETE FROM actions 
                WHERE tenant_id = :tenant_id AND action_id = :action_id
            ");

      $stmt->execute([
        'tenant_id' => $tenant_id,
        'action_id' => $action_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
                VALUES (:tenant_id, '削除', 'actions', :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(['deleted_action_id' => $action_id], JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'アクションを削除しました。'
      ]);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  }
} catch (PDOException $e) {
  error_log('Actions API error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
