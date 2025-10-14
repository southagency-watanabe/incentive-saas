<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/session.php';

// ログインチェック
requireLogin();

// ダミーデータ
$pendingActions = [
  [
    'id' => 1,
    'date' => '2025-10-10',
    'action_name' => 'SNS投稿',
    'quantity' => 3,
    'point' => 150,
    
    'note' => 'Instagram, Twitter, Facebook',
    'approval_status' => 'ユーザー確認待ち'
  ],
  [
    'id' => 2,
    'date' => '2025-10-11',
    'action_name' => '顧客紹介',
    'quantity' => 1,
    'point' => 500,
    'note' => '田中様を紹介',
    'approval_status' => 'ユーザー確認待ち'
  ],
  [
    'id' => 3,
    'date' => '2025-10-12',
    'action_name' => 'セミナー参加',
    'quantity' => 1,
    'point' => 300,
    'note' => '営業スキルアップセミナー',
    'approval_status' => 'ユーザー確認待ち'
  ]
];

$approvedActions = [
  [
    'id' => 4,
    'date' => '2025-10-08',
    'action_name' => 'SNS投稿',
    'quantity' => 2,
    'point' => 100,
    'approval_status' => '承認済',
    'approved_at' => '2025-10-09 10:30:00'
  ],
  [
    'id' => 5,
    'date' => '2025-10-07',
    'action_name' => 'ブログ記事投稿',
    'quantity' => 1,
    'point' => 200,
    'approval_status' => '承認済',
    'approved_at' => '2025-10-08 14:20:00'
  ]
];
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>アクション承認 - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
  <!-- ヘッダー -->
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">インセンティブSaaS</h1>
        <p class="text-sm text-gray-600">アクション承認</p>
      </div>
      <div class="flex items-center gap-4">
        <span class="text-gray-700"><?= htmlspecialchars($_SESSION['name']) ?> さん</span>
        <a href="/api/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">ログアウト</a>
      </div>
    </div>
  </header>

  <!-- ナビゲーション -->
  <nav class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex space-x-8">
        <a href="/user/home.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">ホーム</a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">報告</a>
        <a href="/user/sales-approval.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">売上承認</a>
        <a href="/user/action-approval.php" class="py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">アクション承認</a>
        <a href="/user/task-approval.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">タスク承認</a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">ランキング</a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">掲示板</a>
      </div>
    </div>
  </nav>

  <!-- メインコンテンツ -->
  <main class="max-w-7xl mx-auto px-4 py-8">
    <!-- 未承認一覧 -->
    <div class="mb-8">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">未承認のアクション</h2>
        <button onclick="location.reload()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center gap-2">
          <span>🔄</span>
          <span>更新</span>
        </button>
      </div>

      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">日付</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">アクション</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">回数</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ポイント</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">備考</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($pendingActions)): ?>
              <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">未承認のアクションはありません</td>
              </tr>
            <?php else: ?>
              <?php foreach ($pendingActions as $action): ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($action['date']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($action['action_name']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $action['quantity'] ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $action['point'] ?>pt</td>
                  <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($action['note'] ?? '-') ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                    <button onclick='approveAction(<?= $action['id'] ?>)' class="text-green-600 hover:text-green-900">承認</button>
                    <button onclick='openRejectModal(<?= $action['id'] ?>)' class="text-red-600 hover:text-red-900">却下</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 承認済み一覧 -->
    <div>
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">承認済みのアクション</h2>
        <button id="toggleApproved" onclick="toggleApprovedList()" class="text-blue-600 hover:text-blue-900">
          <span id="toggleIcon">▼</span> 表示/非表示
        </button>
      </div>

      <div id="approvedSection" class="hidden bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">日付</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">アクション</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">回数</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ポイント</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">承認日時</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($approvedActions)): ?>
              <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">承認済みのアクションはありません</td>
              </tr>
            <?php else: ?>
              <?php foreach ($approvedActions as $action): ?>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($action['date']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($action['action_name']) ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $action['quantity'] ?></td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $action['point'] ?>pt</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($action['approved_at'] ?? '-') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <!-- 却下モーダル -->
  <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold">却下理由入力</h3>
        <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
      </div>

      <form id="rejectForm" onsubmit="handleReject(event)">
        <input type="hidden" id="rejectActionId">

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">却下理由 <span class="text-red-500">*</span></label>
          <textarea id="rejectReason" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex justify-end gap-3">
          <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            キャンセル
          </button>
          <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
            却下する
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // 承認済み一覧の表示切り替え
    function toggleApprovedList() {
      const section = document.getElementById('approvedSection');
      const icon = document.getElementById('toggleIcon');

      if (section.classList.contains('hidden')) {
        section.classList.remove('hidden');
        icon.textContent = '▲';
      } else {
        section.classList.add('hidden');
        icon.textContent = '▼';
      }
    }

    // 承認
    function approveAction(actionId) {
      if (!confirm('このアクションを承認しますか？')) {
        return;
      }

      alert('アクションID ' + actionId + ' を承認しました（ダミー処理）');
      location.reload();
    }

    // 却下モーダルを開く
    function openRejectModal(actionId) {
      document.getElementById('rejectActionId').value = actionId;
      document.getElementById('rejectReason').value = '';
      document.getElementById('rejectModal').classList.remove('hidden');
    }

    // 却下モーダルを閉じる
    function closeRejectModal() {
      document.getElementById('rejectModal').classList.add('hidden');
    }

    // 却下処理
    function handleReject(event) {
      event.preventDefault();

      const actionId = document.getElementById('rejectActionId').value;
      const rejectReason = document.getElementById('rejectReason').value;

      alert('アクションID ' + actionId + ' を却下しました（ダミー処理）\n理由: ' + rejectReason);
      closeRejectModal();
      location.reload();
    }
  </script>
</body>

</html>
