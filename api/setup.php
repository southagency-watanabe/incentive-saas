<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDB();

    // POSTリクエストのみ受け付け
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // JSONデータを取得
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // 必須項目のチェック
    if (empty($data['tenant_id']) || empty($data['tenant_name']) || empty($data['industry'])) {
        throw new Exception('必須項目が入力されていません。');
    }

    // テナントIDの形式チェック
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $data['tenant_id'])) {
        throw new Exception('テナントIDは英数字、ハイフン、アンダースコアのみ使用可能です。');
    }

    // 既存のテナントIDチェック
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tenants WHERE tenant_id = :tenant_id");
    $stmt->bindValue(':tenant_id', $data['tenant_id']);
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result['count'] > 0) {
        throw new Exception('このテナントIDは既に使用されています。');
    }

    // トランザクション開始
    $pdo->beginTransaction();

    try {
        // テナント作成
        $stmt = $pdo->prepare("
            INSERT INTO tenants (tenant_id, tenant_name, status)
            VALUES (:tenant_id, :tenant_name, '有効')
        ");
        $stmt->bindValue(':tenant_id', $data['tenant_id']);
        $stmt->bindValue(':tenant_name', $data['tenant_name']);
        $stmt->execute();

        // テナント設定を保存
        $settings = [
            'industry' => $data['industry'] ?? '',
            'address' => $data['address'] ?? '',
            'phone' => $data['phone'] ?? '',
            'email' => $data['email'] ?? '',
            'sales_approval' => $data['sales_approval'] ?? 'none',
            'sales_threshold' => $data['sales_threshold'] ?? '0',
            'action_approval' => $data['action_approval'] ?? 'all',
            'task_approval' => $data['task_approval'] ?? 'none',
            'setup_completed' => date('Y-m-d H:i:s')
        ];

        $stmt = $pdo->prepare("
            INSERT INTO tenant_settings (tenant_id, setting_key, setting_value)
            VALUES (:tenant_id, :key, :value)
        ");

        foreach ($settings as $key => $value) {
            $stmt->bindValue(':tenant_id', $data['tenant_id']);
            $stmt->bindValue(':key', $key);
            $stmt->bindValue(':value', $value);
            $stmt->execute();
        }

        // デフォルトチームを作成
        $defaultTeams = [
            ['team_id' => 'TEAM001', 'team_name' => 'ホールスタッフ', 'description' => '接客・配膳担当'],
            ['team_id' => 'TEAM002', 'team_name' => 'キッチンスタッフ', 'description' => '調理担当'],
            ['team_id' => 'TEAM003', 'team_name' => '店長・マネージャー', 'description' => '管理職']
        ];

        $stmt = $pdo->prepare("
            INSERT INTO teams (team_id, tenant_id, team_name, description, status)
            VALUES (:team_id, :tenant_id, :team_name, :description, '有効')
        ");

        foreach ($defaultTeams as $team) {
            $stmt->bindValue(':team_id', $team['team_id']);
            $stmt->bindValue(':tenant_id', $data['tenant_id']);
            $stmt->bindValue(':team_name', $team['team_name']);
            $stmt->bindValue(':description', $team['description']);
            $stmt->execute();
        }

        // コミット
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => '初期設定が完了しました。',
            'tenant_id' => $data['tenant_id']
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log('Setup error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
