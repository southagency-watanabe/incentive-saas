<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/database.php';

// 管理者権限チェック
requireAdmin();

// チーム一覧取得（プルダウン用）
$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT team_id, team_name 
    FROM teams 
    WHERE tenant_id = :tenant_id AND status = '有効'
    ORDER BY team_id ASC
");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$teams = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>メンバーマスタ - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
  <!-- ヘッダー -->
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">インセンティブSaaS</h1>
        <p class="text-sm text-gray-600">メンバーマスタ</p>
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
        <a href="/admin/dashboard.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">ダッシュボード</a>
        <a href="/admin/masters/members.php" class="py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">マスタ管理</a>
        <a href="/admin/sales/input.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">売上管理</a>
        <a href="/admin/approvals.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">承認管理</a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">実績管理</a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">掲示板管理</a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">ランキング</a>
      </div>
    </div>
  </nav>

  <!-- サブナビゲーション（マスタ切り替え） -->
  <div class="bg-gray-50 border-b">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex space-x-6">
        <a href="/admin/masters/members.php" class="py-3 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">メンバー</a>
        <a href="/admin/masters/teams.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">チーム</a>
        <a href="/admin/masters/products.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">商品</a>
        <a href="/admin/masters/actions.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">アクション</a>
        <a href="/admin/masters/tasks.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">タスク</a>
        <a href="/admin/masters/events.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">イベント</a>
      </div>
    </div>
  </div>

  <!-- メインコンテンツ -->
  <main class="max-w-7xl mx-auto px-4 py-8">
    <!-- ヘッダーアクション -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">メンバー一覧</h2>
      <div class="flex gap-3">
        <button id="refreshBtn" onclick="refreshList()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center gap-2">
          <span id="refreshIcon">🔄</span>
          <span>更新</span>
        </button>
        <button onclick="openModal('create')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          新規登録
        </button>
      </div>
    </div>

    <!-- テーブル -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">メンバーID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">氏名</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">チーム</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ログインID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">権限</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
          </tr>
        </thead>
        <tbody id="memberTableBody" class="bg-white divide-y divide-gray-200">
          <!-- データはJavaScriptで挿入 -->
        </tbody>
      </table>
    </div>
  </main>

  <!-- モーダル -->
  <div id="modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 id="modalTitle" class="text-xl font-bold">メンバー登録</h3>
        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
      </div>

      <form id="memberForm" class="space-y-4">
        <input type="hidden" id="memberId" name="member_id">

        <!-- 氏名 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">氏名 <span class="text-red-500">*</span></label>
          <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- チーム -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">チーム</label>
          <select id="teamId" name="team_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <option value="">未所属</option>
            <?php foreach ($teams as $team): ?>
              <option value="<?= htmlspecialchars($team['team_id']) ?>">
                <?= htmlspecialchars($team['team_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- ログインID -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ログインID <span class="text-red-500">*</span></label>
          <input type="text" id="loginId" name="login_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          <p id="loginIdMessage" class="text-sm mt-1"></p>
        </div>

        <!-- PIN -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">PIN（4桁） <span class="text-red-500" id="pinRequired">*</span></label>
          <input type="password" id="pin" name="pin" maxlength="4" pattern="[0-9]{4}" placeholder="0000" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          <p class="text-sm text-gray-500 mt-1" id="pinHint">数字4桁を入力してください</p>
        </div>

        <!-- 権限 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">権限 <span class="text-red-500">*</span></label>
          <select id="role" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <option value="user">user（スタッフ）</option>
            <option value="admin">admin（管理者）</option>
          </select>
        </div>

        <!-- ステータス -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ステータス <span class="text-red-500">*</span></label>
          <select id="status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <option value="有効">有効</option>
            <option value="無効">無効</option>
          </select>
        </div>

        <!-- 説明 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">説明</label>
          <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            キャンセル
          </button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            保存
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    let currentMode = 'create';
    let loginIdCheckTimeout = null;

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadMembers();
      setupLoginIdCheck();
    });

    // メンバー一覧取得
    async function loadMembers(showLoading = false) {
      try {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshIcon');
          const refreshBtn = document.getElementById('refreshBtn');
          refreshIcon.textContent = '⏳';
          refreshBtn.disabled = true;
          refreshBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const response = await fetch('/api/members.php');
        const result = await response.json();

        if (result.success) {
          renderTable(result.data);
        } else {
          alert('データの取得に失敗しました。');
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      } finally {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshIcon');
          const refreshBtn = document.getElementById('refreshBtn');
          refreshIcon.textContent = '🔄';
          refreshBtn.disabled = false;
          refreshBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
      }
    }

    // テーブル描画
    function renderTable(members) {
      const tbody = document.getElementById('memberTableBody');
      tbody.innerHTML = '';

      members.forEach(member => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(member.member_id)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(member.name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(member.team_name || '-')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(member.login_id)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${member.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}">
                            ${escapeHtml(member.role)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${member.status === '有効' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${escapeHtml(member.status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <button onclick='openModal("edit", ${JSON.stringify(member)})' class="text-blue-600 hover:text-blue-900">編集</button>
                        <button onclick='duplicateMember("${member.member_id}")' class="text-green-600 hover:text-green-900">複製</button>
                        <button onclick='deleteMember("${member.member_id}", "${escapeHtml(member.name)}")' class="text-red-600 hover:text-red-900">削除</button>
                    </td>
                `;
        tbody.appendChild(tr);
      });
    }

    // モーダル開く
    function openModal(mode, data = null) {
      currentMode = mode;
      const modal = document.getElementById('modal');
      const form = document.getElementById('memberForm');
      const title = document.getElementById('modalTitle');
      const pinInput = document.getElementById('pin');
      const pinRequired = document.getElementById('pinRequired');
      const pinHint = document.getElementById('pinHint');

      form.reset();

      if (mode === 'create') {
        title.textContent = 'メンバー登録';
        pinInput.required = true;
        pinRequired.style.display = 'inline';
        pinHint.textContent = '数字4桁を入力してください';
      } else {
        title.textContent = 'メンバー編集';
        document.getElementById('memberId').value = data.member_id;
        document.getElementById('name').value = data.name;
        document.getElementById('teamId').value = data.team_id || '';
        document.getElementById('loginId').value = data.login_id;
        document.getElementById('role').value = data.role;
        document.getElementById('status').value = data.status;
        document.getElementById('description').value = data.description || '';

        pinInput.required = false;
        pinRequired.style.display = 'none';
        pinHint.textContent = '変更する場合のみ入力してください';
      }

      modal.classList.remove('hidden');
    }

    // モーダル閉じる
    function closeModal() {
      document.getElementById('modal').classList.add('hidden');
      document.getElementById('loginIdMessage').textContent = '';
    }

    // ログインID重複チェック設定
    function setupLoginIdCheck() {
      const loginIdInput = document.getElementById('loginId');

      loginIdInput.addEventListener('input', (e) => {
        clearTimeout(loginIdCheckTimeout);
        const loginId = e.target.value.trim();

        if (!loginId) {
          document.getElementById('loginIdMessage').textContent = '';
          return;
        }

        loginIdCheckTimeout = setTimeout(async () => {
          const excludeId = currentMode === 'edit' ? document.getElementById('memberId').value : '';
          const url = `/api/members/check-login-id.php?login_id=${encodeURIComponent(loginId)}${excludeId ? '&exclude=' + excludeId : ''}`;

          try {
            const response = await fetch(url);
            const result = await response.json();

            const message = document.getElementById('loginIdMessage');
            if (result.available) {
              message.textContent = '✓ ' + result.message;
              message.className = 'text-sm mt-1 text-green-600';
            } else {
              message.textContent = '✗ ' + result.message;
              message.className = 'text-sm mt-1 text-red-600';
            }
          } catch (error) {
            console.error(error);
          }
        }, 500);
      });
    }

    // フォーム送信
    document.getElementById('memberForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);
      const data = {
        name: formData.get('name'),
        team_id: formData.get('team_id'),
        login_id: formData.get('login_id'),
        pin: formData.get('pin'),
        role: formData.get('role'),
        status: formData.get('status'),
        description: formData.get('description')
      };

      try {
        let url = '/api/members.php';
        let method = 'POST';

        if (currentMode === 'edit') {
          const memberId = document.getElementById('memberId').value;
          url = `/api/members.php?id=${memberId}`;
          method = 'PUT';
        }

        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          closeModal();
          loadMembers();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    });

    // 削除
    async function deleteMember(memberId, name) {
      if (!confirm(`「${name}」を削除しますか？\nこの操作は取り消せません。`)) {
        return;
      }

      try {
        const response = await fetch(`/api/members.php?id=${memberId}`, {
          method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadMembers();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // 複製
    async function duplicateMember(memberId) {
      if (!confirm('このメンバーを複製しますか？')) {
        return;
      }

      try {
        const response = await fetch('/api/members/duplicate.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            member_id: memberId
          })
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message + '\n新しいログインID: ' + result.login_id);
          loadMembers();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // 更新
    function refreshList() {
      loadMembers(true);
    }

    // HTMLエスケープ
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>
</body>

</html>