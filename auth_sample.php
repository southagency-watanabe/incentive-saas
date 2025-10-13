<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// POSTリクエストのみ受け付け
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// JSON入力を取得
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 必須項目チェック
if (!isset($data['tenant_id']) || !isset($data['login_id']) || !isset($data['pin'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '必須項目が入力されていません。']);
    exit;
}

$tenant_id = trim($data['tenant_id']);
$login_id = trim($data['login_id']);
$pin = trim($data['pin']);

try {
    $pdo = getDB();
    
    // テナントとメンバーを取得
    $stmt = $pdo->prepare("
        SELECT m.member_id, m.tenant_id, m.name, m.role, m.pin, m.status
        FROM members m
        INNER JOIN tenants t ON m.tenant_id = t.tenant_id
        WHERE m.tenant_id = :tenant_id 
        AND m.login_id = :login_id
        AND t.status = '有効'
    ");
    
    $stmt->execute([
        'tenant_id' => $tenant_id,
        'login_id' => $login_id
    ]);
    
    $member = $stmt->fetch();
    
    // ユーザーが存在しない、またはステータスが無効
    if (!$member || $member['status'] !== '有効') {
        echo json_encode(['success' => false, 'message' => '認証に失敗しました。']);
        exit;
    }
    
    // PIN検証（現在は平文比較、本番環境ではpassword_verify使用）
    if ($member['pin'] !== $pin) {
        echo json_encode(['success' => false, 'message' => '認証に失敗しました。']);
        exit;
    }
    
    // セッション作成
    $token = createSession(
        $member['tenant_id'],
        $member['member_id'],
        $member['role'],
        $member['name']
    );
    
    // リダイレクト先を決定
    $redirect = ($member['role'] === 'admin') ? '/admin/dashboard.php' : '/user/home.php';
    
    // 監査ログ記録
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (tenant_id, action, member_id, operator, user_display_name, details)
        VALUES (:tenant_id, 'ログイン', :member_id, :operator, :name, :details)
    ");
    
    $stmt->execute([
        'tenant_id' => $member['tenant_id'],
        'member_id' => $member['member_id'],
        'operator' => $member['member_id'],
        'name' => $member['name'],
        'details' => json_encode([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ])
    ]);
    
    // 成功レスポンス
    echo json_encode([
        'success' => true,
        'message' => 'ログインに成功しました。',
        'redirect' => $redirect,
        'user' => [
            'name' => $member['name'],
            'role' => $member['role']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}