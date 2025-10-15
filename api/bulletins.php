<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// ログイン必須
requireLogin();

$pdo = getDB();
$tenant_id = $_SESSION['tenant_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
  switch ($method) {
    case 'GET':
      // 掲示板一覧取得
      $filter = $_GET['filter'] ?? 'all'; // all, pinned, public
      $is_admin = $_SESSION['role'] === 'admin';

      // 管理者の場合は全てのステータス、一般ユーザーは公開のみ
      if ($is_admin) {
        $where_clause = "tenant_id = :tenant_id";
      } else {
        $where_clause = "tenant_id = :tenant_id AND status = '公開'";
      }
      $params = ['tenant_id' => $tenant_id];

      // 公開期間チェック（管理者以外）
      if (!$is_admin) {
        $where_clause .= " AND (start_datetime IS NULL OR start_datetime <= NOW())";
        $where_clause .= " AND (end_datetime IS NULL OR end_datetime >= NOW())";
      }

      if ($filter === 'pinned') {
        $where_clause .= " AND pinned = 1";
      }

      $stmt = $pdo->prepare("
        SELECT
          bulletin_id,
          type,
          related_event_id,
          pinned,
          status,
          author,
          title,
          body,
          start_datetime,
          end_datetime,
          created_at
        FROM bulletins
        WHERE {$where_clause}
        ORDER BY pinned DESC, created_at DESC
      ");
      $stmt->execute($params);
      $bulletins = $stmt->fetchAll();

      echo json_encode([
        'success' => true,
        'data' => $bulletins
      ]);
      break;

    case 'POST':
      // 掲示板投稿（全ユーザー可能）
      $data = json_decode(file_get_contents('php://input'), true);

      // バリデーション
      if (empty($data['title']) || empty($data['body'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'タイトルと本文は必須です。']);
        exit;
      }

      // 新規ID生成
      $stmt = $pdo->prepare("
        SELECT bulletin_id
        FROM bulletins
        WHERE tenant_id = :tenant_id
        ORDER BY bulletin_id DESC
        LIMIT 1
      ");
      $stmt->execute(['tenant_id' => $tenant_id]);
      $last = $stmt->fetch();

      if ($last) {
        $last_num = intval(substr($last['bulletin_id'], 2));
        $new_id = 'BL' . str_pad($last_num + 1, 6, '0', STR_PAD_LEFT);
      } else {
        $new_id = 'BL000001';
      }

      $stmt = $pdo->prepare("
        INSERT INTO bulletins (
          bulletin_id,
          tenant_id,
          type,
          pinned,
          status,
          author,
          title,
          body,
          start_datetime,
          end_datetime
        ) VALUES (
          :bulletin_id,
          :tenant_id,
          :type,
          :pinned,
          :status,
          :author,
          :title,
          :body,
          :start_datetime,
          :end_datetime
        )
      ");

      $stmt->execute([
        'bulletin_id' => $new_id,
        'tenant_id' => $tenant_id,
        'type' => $data['type'] ?? 'お知らせ',
        'pinned' => $data['pinned'] ?? 0,
        'status' => $data['status'] ?? '公開',
        'author' => $_SESSION['member_id'],
        'title' => $data['title'],
        'body' => $data['body'],
        'start_datetime' => $data['start_datetime'] ?? null,
        'end_datetime' => $data['end_datetime'] ?? null
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
        VALUES (:tenant_id, '掲示板投稿', 'bulletins', :row_id, :operator, :name, :details)
      ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'row_id' => $new_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(['title' => $data['title']], JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => '掲示板に投稿しました。',
        'bulletin_id' => $new_id
      ]);
      break;

    case 'PUT':
      // 掲示板更新（管理者または投稿者本人のみ）
      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '掲示板IDが指定されていません。']);
        exit;
      }

      $bulletin_id = $_GET['id'];
      $data = json_decode(file_get_contents('php://input'), true);

      // 既存データ取得
      $stmt = $pdo->prepare("
        SELECT author
        FROM bulletins
        WHERE tenant_id = :tenant_id AND bulletin_id = :bulletin_id
      ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'bulletin_id' => $bulletin_id
      ]);
      $bulletin = $stmt->fetch();

      if (!$bulletin) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '掲示板が見つかりません。']);
        exit;
      }

      // 権限チェック（管理者または投稿者本人のみ）
      if ($_SESSION['role'] !== 'admin' && $bulletin['author'] !== $_SESSION['member_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => '編集権限がありません。']);
        exit;
      }

      $stmt = $pdo->prepare("
        UPDATE bulletins
        SET
          type = :type,
          pinned = :pinned,
          status = :status,
          title = :title,
          body = :body,
          start_datetime = :start_datetime,
          end_datetime = :end_datetime
        WHERE tenant_id = :tenant_id AND bulletin_id = :bulletin_id
      ");

      $stmt->execute([
        'type' => $data['type'] ?? 'お知らせ',
        'pinned' => $data['pinned'] ?? 0,
        'status' => $data['status'] ?? '公開',
        'title' => $data['title'],
        'body' => $data['body'],
        'start_datetime' => $data['start_datetime'] ?? null,
        'end_datetime' => $data['end_datetime'] ?? null,
        'tenant_id' => $tenant_id,
        'bulletin_id' => $bulletin_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
        VALUES (:tenant_id, '掲示板更新', 'bulletins', :row_id, :operator, :name, :details)
      ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'row_id' => $bulletin_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(['title' => $data['title']], JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => '掲示板を更新しました。'
      ]);
      break;

    case 'DELETE':
      // 掲示板削除（管理者または投稿者本人のみ）
      if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '掲示板IDが指定されていません。']);
        exit;
      }

      $bulletin_id = $_GET['id'];

      // 既存データ取得
      $stmt = $pdo->prepare("
        SELECT author, title
        FROM bulletins
        WHERE tenant_id = :tenant_id AND bulletin_id = :bulletin_id
      ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'bulletin_id' => $bulletin_id
      ]);
      $bulletin = $stmt->fetch();

      if (!$bulletin) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '掲示板が見つかりません。']);
        exit;
      }

      // 権限チェック（管理者または投稿者本人のみ）
      if ($_SESSION['role'] !== 'admin' && $bulletin['author'] !== $_SESSION['member_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => '削除権限がありません。']);
        exit;
      }

      $stmt = $pdo->prepare("
        DELETE FROM bulletins
        WHERE tenant_id = :tenant_id AND bulletin_id = :bulletin_id
      ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'bulletin_id' => $bulletin_id
      ]);

      // 監査ログ
      $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, table_name, row_id, operator, user_display_name, details)
        VALUES (:tenant_id, '掲示板削除', 'bulletins', :row_id, :operator, :name, :details)
      ");
      $stmt->execute([
        'tenant_id' => $tenant_id,
        'row_id' => $bulletin_id,
        'operator' => $_SESSION['member_id'],
        'name' => $_SESSION['name'],
        'details' => json_encode(['title' => $bulletin['title']], JSON_UNESCAPED_UNICODE)
      ]);

      echo json_encode([
        'success' => true,
        'message' => '掲示板を削除しました。'
      ]);
      break;

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
      break;
  }
} catch (PDOException $e) {
  error_log('Bulletins API error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
