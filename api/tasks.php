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
                    task_id,
                    task_name,
                    type,
                    repeat_type,
                    days_of_week,
                    day_of_month,
                    start_datetime,
                    end_datetime,
                    point,
                    daily_limit,
                    approval_required,
                    status,
                    description,
                    created_at,
                    updated_at
                FROM tasks
                WHERE tenant_id = :tenant_id
                ORDER BY task_id ASC
            ");

      $stmt->execute(['tenant_id' => $tenant_id]);
      $tasks = $stmt->fetchAll();

      // approval_requiredを「必要」「不要」に変換
      foreach ($tasks as &$task) {
        $task['approval_required'] = $task['approval_required'] ? '必要' : '不要';
      }

      echo json_encode([
        'success' => true,
        'data' => $tasks
      ]);
      break;

    case 'POST':
      // 新規登録
      $input = json_decode(file_get_contents('php://input'), true);

      // バリデーション
      if (empty($input['task_name']) || empty($input['type']) || empty($input['repeat_type']) || !isset($input['point']) || !isset($input['daily_limit']) || empty($input['status']) || empty($input['approval_required'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // 種別チェック
      if (!in_array($input['type'], ['個人', 'チーム'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '種別は「個人」または「チーム」を選択してください。']);
        exit;
      }

      // 繰り返しチェック
      if (!in_array($input['repeat_type'], ['毎日', '毎週', '毎月'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '繰り返しは「毎日」「毎週」「毎月」のいずれかを選択してください。']);
        exit;
      }

      // 数値チェック
      if (!is_numeric($input['point']) || !is_numeric($input['daily_limit'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '付与ptと1日上限は数値で入力してください。']);
        exit;
      }

      // 繰り返し設定の検証
      if ($input['repeat_type'] === '毎週' && empty($input['days_of_week'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '繰り返しが「毎週」の場合は曜日を選択してください。']);
        exit;
      }

      if ($input['repeat_type'] === '毎月' && empty($input['day_of_month'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '繰り返しが「毎月」の場合は日付を入力してください。']);
        exit;
      }

      // 新しいタスクID生成（TSK001形式）
      $stmt = $pdo->prepare("
                SELECT task_id FROM tasks 
                WHERE tenant_id = :tenant_id 
                ORDER BY task_id DESC LIMIT 1
            ");
      $stmt->execute(['tenant_id' => $tenant_id]);
      $lastTask = $stmt->fetch();

      if ($lastTask) {
        $lastNum = (int)substr($lastTask['task_id'], 3);
        $newNum = $lastNum + 1;
      } else {
        $newNum = 1;
      }
      $task_id = 'TSK' . str_pad($newNum, 3, '0', STR_PAD_LEFT);

      // 繰り返し設定に応じて値を設定
      $days_of_week = null;
      $day_of_month = null;

      if ($input['repeat_type'] === '毎週') {
        $days_of_week = $input['days_of_week'];
      } elseif ($input['repeat_type'] === '毎月') {
        $day_of_month = $input['day_of_month'];
      }

      // approval_requiredを0/1に変換
      $approval_required_bool = ($input['approval_required'] === '必要') ? 1 : 0;

      // 登録
      $stmt = $pdo->prepare("
                INSERT INTO tasks (
                    task_id, tenant_id, task_name, type, repeat_type,
                    days_of_week, day_of_month, start_datetime, end_datetime,
                    point, daily_limit, approval_required, status, description
                ) VALUES (
                    :task_id, :tenant_id, :task_name, :type, :repeat_type,
                    :days_of_week, :day_of_month, :start_datetime, :end_datetime,
                    :point, :daily_limit, :approval_required, :status, :description
                )
            ");

      $stmt->execute([
        'task_id' => $task_id,
        'tenant_id' => $tenant_id,
        'task_name' => $input['task_name'],
        'type' => $input['type'],
        'repeat_type' => $input['repeat_type'],
        'days_of_week' => $days_of_week,
        'day_of_month' => $day_of_month,
        'start_datetime' => $input['start_datetime'] ?: null,
        'end_datetime' => $input['end_datetime'] ?: null,
        'point' => $input['point'],
        'daily_limit' => $input['daily_limit'],
        'approval_required' => $approval_required_bool,
        'status' => $input['status'],
        'description' => $input['description'] ?? null
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
                VALUES (:tenant_id, '新規登録', 'tasks', :row_id, :operator, :name, :details)
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
        'message' => 'タスクを登録しました。',
        'task_id' => $task_id
      ]);
      break;

    case 'PUT':
      // 更新
      $input = json_decode(file_get_contents('php://input'), true);

      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'タスクIDが指定されていません。']);
        exit;
      }

      $task_id = $_GET['id'];

      // バリデーション
      if (empty($input['task_name']) || empty($input['type']) || empty($input['repeat_type']) || !isset($input['point']) || !isset($input['daily_limit']) || empty($input['status']) || empty($input['approval_required'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
        exit;
      }

      // 種別チェック
      if (!in_array($input['type'], ['個人', 'チーム'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '種別は「個人」または「チーム」を選択してください。']);
        exit;
      }

      // 繰り返しチェック
      if (!in_array($input['repeat_type'], ['毎日', '毎週', '毎月'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '繰り返しは「毎日」「毎週」「毎月」のいずれかを選択してください。']);
        exit;
      }

      // 数値チェック
      if (!is_numeric($input['point']) || !is_numeric($input['daily_limit'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '付与ptと1日上限は数値で入力してください。']);
        exit;
      }

      // 繰り返し設定の検証
      if ($input['repeat_type'] === '毎週' && empty($input['days_of_week'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '繰り返しが「毎週」の場合は曜日を選択してください。']);
        exit;
      }

      if ($input['repeat_type'] === '毎月' && empty($input['day_of_month'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '繰り返しが「毎月」の場合は日付を入力してください。']);
        exit;
      }

      // 繰り返し設定に応じて値を設定
      $days_of_week = null;
      $day_of_month = null;

      if ($input['repeat_type'] === '毎週') {
        $days_of_week = $input['days_of_week'];
      } elseif ($input['repeat_type'] === '毎月') {
        $day_of_month = $input['day_of_month'];
      }

      // approval_requiredを0/1に変換
      $approval_required_bool = ($input['approval_required'] === '必要') ? 1 : 0;

      // 更新
      $stmt = $pdo->prepare("
                UPDATE tasks SET
                    task_name = :task_name,
                    type = :type,
                    repeat_type = :repeat_type,
                    days_of_week = :days_of_week,
                    day_of_month = :day_of_month,
                    start_datetime = :start_datetime,
                    end_datetime = :end_datetime,
                    point = :point,
                    daily_limit = :daily_limit,
                    approval_required = :approval_required,
                    status = :status,
                    description = :description
                WHERE tenant_id = :tenant_id AND task_id = :task_id
            ");

      $stmt->execute([
        'task_name' => $input['task_name'],
        'type' => $input['type'],
        'repeat_type' => $input['repeat_type'],
        'days_of_week' => $days_of_week,
        'day_of_month' => $day_of_month,
        'start_datetime' => $input['start_datetime'] ?: null,
        'end_datetime' => $input['end_datetime'] ?: null,
        'point' => $input['point'],
        'daily_limit' => $input['daily_limit'],
        'approval_required' => $approval_required_bool,
        'status' => $input['status'],
        'description' => $input['description'] ?? null,
        'tenant_id' => $tenant_id,
        'task_id' => $task_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
                VALUES (:tenant_id, '更新', 'tasks', :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(array_merge(['task_id' => $task_id], $input), JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'タスク情報を更新しました。'
      ]);
      break;

    case 'DELETE':
      // 削除
      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'タスクIDが指定されていません。']);
        exit;
      }

      $task_id = $_GET['id'];

      // タスク実績で使用されていないかチェック
      $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM task_records 
                WHERE tenant_id = :tenant_id AND task_id = :task_id
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'task_id' => $task_id
      ]);

      if ($stmt->fetchColumn() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'このタスクは実績で使用されているため削除できません。']);
        exit;
      }

      $stmt = $pdo->prepare("
                DELETE FROM tasks 
                WHERE tenant_id = :tenant_id AND task_id = :task_id
            ");

      $stmt->execute([
        'tenant_id' => $tenant_id,
        'task_id' => $task_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, operator, user_display_name, details)
                VALUES (:tenant_id, '削除', 'tasks', :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(['deleted_task_id' => $task_id], JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => 'タスクを削除しました。'
      ]);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  }
} catch (PDOException $e) {
  error_log('Tasks API error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
