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
      // 承認待ちのタスク実績を取得
      $stmt = $pdo->prepare("
                SELECT
                    tr.id,
                    tr.date,
                    tr.member_id,
                    m.name as member_name,
                    tr.task_id,
                    t.task_name,
                    tr.point,
                    tr.approval_status,
                    tr.reject_reason,
                    tr.created_at
                FROM task_records tr
                JOIN members m ON tr.member_id = m.member_id AND tr.tenant_id = m.tenant_id
                JOIN tasks t ON tr.task_id = t.task_id AND tr.tenant_id = t.tenant_id
                WHERE tr.tenant_id = :tenant_id
                AND tr.approval_status = '承認待ち'
                ORDER BY tr.created_at DESC
            ");

      $stmt->execute(['tenant_id' => $tenant_id]);
      $records = $stmt->fetchAll();

      echo json_encode([
        'success' => true,
        'data' => $records
      ]);
      break;

    case 'PUT':
      // 承認/却下処理
      $input = json_decode(file_get_contents('php://input'), true);

      if (!isset($_GET['id']) || !isset($input['action']) || !in_array($input['action'], ['approve', 'reject'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'パラメータが不正です。']);
        exit;
      }

      $record_id = $_GET['id'];
      $action = $input['action'];
      $reject_reason = $input['reject_reason'] ?? null;

      // レコード存在確認
      $stmt = $pdo->prepare("
                SELECT id FROM task_records
                WHERE tenant_id = :tenant_id AND id = :id AND approval_status = '承認待ち'
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'id' => $record_id
      ]);

      if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '該当のレコードが見つかりません。']);
        exit;
      }

      if ($action === 'approve') {
        // 承認処理
        $stmt = $pdo->prepare("
                    UPDATE task_records
                    SET approval_status = '承認済み',
                        approver = :approver,
                        approved_at = NOW()
                    WHERE tenant_id = :tenant_id AND id = :id
                ");
        $stmt->execute([
          'tenant_id' => $tenant_id,
          'id' => $record_id,
          'approver' => $_SESSION['member_id']
        ]);

        $message = 'タスク実績を承認しました。';
      } else {
        // 却下処理
        if (empty($reject_reason)) {
          http_response_code(400);
          echo json_encode(['success' => false, 'message' => '却下理由を入力してください。']);
          exit;
        }

        $stmt = $pdo->prepare("
                    UPDATE task_records
                    SET approval_status = '却下',
                        approver = :approver,
                        approved_at = NOW(),
                        reject_reason = :reject_reason
                    WHERE tenant_id = :tenant_id AND id = :id
                ");
        $stmt->execute([
          'tenant_id' => $tenant_id,
          'id' => $record_id,
          'approver' => $_SESSION['member_id'],
          'reject_reason' => $reject_reason
        ]);

        $message = 'タスク実績を却下しました。';
      }

      // 監査ログ
      $stmt = $pdo->prepare("
                INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
                VALUES (:tenant_id, :action, 'task_records', :row_id, :operator, :name, :details)
            ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'action' => $action === 'approve' ? '承認' : '却下',
        'row_id' => $record_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode($input, JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => $message
      ]);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
  }
} catch (PDOException $e) {
  error_log('Task Approvals API error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
