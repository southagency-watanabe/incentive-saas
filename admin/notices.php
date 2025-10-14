<?php
require_once __DIR__ . '/../includes/session.php';

// 管理者権限チェック
requireAdmin();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お知らせ - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex">
  <!-- サイドバー -->
  <aside class="w-64 bg-white shadow-lg h-screen sticky top-0 flex flex-col">
    <!-- ロゴ・ヘッダー部分 -->
    <div class="p-6 border-b">
      <h1 class="text-xl font-bold text-gray-800">インセンティブSaaS</h1>
    </div>

    <!-- ナビゲーション -->
    <nav class="flex-1 overflow-y-auto py-4">
      <a href="/admin/dashboard.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>ランキングサマリー</span>
      </a>
      <!-- マスタ管理ドロップダウン -->
      <div>
        <button onclick="toggleMasterMenu()" class="w-full flex items-center justify-between px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
          <span>マスタ管理</span>
          <svg id="masterArrow" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div id="masterSubmenu" class="hidden bg-gray-50">
          <a href="/admin/masters/members.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>メンバー</span>
          </a>
          <a href="/admin/masters/teams.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>チーム</span>
          </a>
          <a href="/admin/masters/products.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>商品</span>
          </a>
          <a href="/admin/masters/actions.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>アクション</span>
          </a>
          <a href="/admin/masters/tasks.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>タスク</span>
          </a>
          <a href="/admin/masters/events.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>イベント</span>
          </a>
        </div>
      </div>
      <a href="/admin/sales/input.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>売上管理</span>
      </a>

      <!-- 承認管理ドロップダウン -->
      <div>
        <button onclick="toggleApprovalMenu()" class="w-full flex items-center justify-between px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
          <span>承認管理</span>
          <svg id="approvalArrow" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div id="approvalSubmenu" class="hidden bg-gray-50">
          <a href="/admin/approvals.php?tab=sales" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>売上承認</span>
          </a>
          <a href="/admin/approvals.php?tab=actions" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>アクション承認</span>
          </a>
          <a href="/admin/approvals.php?tab=tasks" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>タスク承認</span>
          </a>
        </div>
      </div>

      <a href="/admin/performance.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>実績管理</span>
      </a>
      <a href="/admin/events.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>イベント</span>
      </a>
      <a href="/admin/notices.php" class="flex items-center px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
        <span>お知らせ</span>
      </a>
    </nav>

    <!-- ユーザー情報・ログアウト -->
    <div class="border-t p-4">
      <div class="flex items-center justify-between">
        <span class="text-sm text-gray-700"><?= htmlspecialchars($_SESSION['name']) ?> さん</span>
        <a href="/api/logout.php" class="text-sm text-red-600 hover:text-red-700 font-medium">ログアウト</a>
      </div>
    </div>
  </aside>

  <!-- メインコンテンツエリア -->
  <div class="flex-1 overflow-y-auto">
    <!-- ページヘッダー -->
    <header class="bg-white shadow-sm border-b">
      <div class="px-8 py-6">
        <h2 class="text-2xl font-bold text-gray-800">お知らせ</h2>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
      <!-- お知らせセクション -->
      <div>
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800">お知らせ一覧</h2>
          <div class="flex gap-3">
            <button id="refreshNoticesBtn" onclick="refreshNotices()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center gap-2">
              <span id="refreshNoticesIcon">🔄</span>
              <span>更新</span>
            </button>
            <button onclick="openModal('create')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              新規投稿
            </button>
          </div>
        </div>
        <div id="noticesList" class="space-y-4">
          <!-- お知らせはJavaScriptで挿入 -->
        </div>
      </div>
    </main>
  </div>

  <!-- モーダル -->
  <div id="modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 id="modalTitle" class="text-xl font-bold">掲示板投稿</h3>
        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
      </div>

      <form id="bulletinForm" class="space-y-4">
        <input type="hidden" id="bulletinId" name="bulletin_id">

        <!-- タイトル -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">タイトル <span class="text-red-500">*</span></label>
          <input type="text" id="title" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- 本文 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">本文 <span class="text-red-500">*</span></label>
          <textarea id="body" name="body" rows="6" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <!-- タイプ -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">タイプ</label>
            <select id="type" name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="お知らせ">お知らせ</option>
              <option value="重要">重要</option>
              <option value="イベント">イベント</option>
              <option value="その他">その他</option>
            </select>
          </div>

          <!-- ステータス -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ステータス</label>
            <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="公開">公開</option>
              <option value="下書き">下書き</option>
              <option value="非公開">非公開</option>
            </select>
          </div>

          <!-- ピン留め -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ピン留め</label>
            <select id="pinned" name="pinned" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="0">なし</option>
              <option value="1">あり</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- 公開開始日時 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">公開開始日時</label>
            <input type="datetime-local" id="startDatetime" name="start_datetime" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 公開終了日時 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">公開終了日時</label>
            <input type="datetime-local" id="endDatetime" name="end_datetime" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            キャンセル
          </button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            投稿
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // マスタ管理ドロップダウンの開閉
    function toggleMasterMenu() {
      const submenu = document.getElementById('masterSubmenu');
      const arrow = document.getElementById('masterArrow');

      if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
      } else {
        submenu.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
      }
    }

    // 承認管理ドロップダウンの開閉
    function toggleApprovalMenu() {
      const submenu = document.getElementById('approvalSubmenu');
      const arrow = document.getElementById('approvalArrow');

      if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
      } else {
        submenu.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
      }
    }

    let currentMode = 'create';

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadNotices();
    });

    // お知らせ一覧取得
    async function loadNotices(showLoading = false) {
      try {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshNoticesIcon');
          const refreshBtn = document.getElementById('refreshNoticesBtn');
          refreshIcon.textContent = '⏳';
          refreshBtn.disabled = true;
          refreshBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const response = await fetch('/api/bulletins.php?filter=all');
        const result = await response.json();

        if (result.success) {
          // related_event_idがないものだけ表示
          const notices = result.data.filter(b => !b.related_event_id);
          renderNoticesList(notices);
        } else {
          alert('お知らせデータの取得に失敗しました。');
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      } finally {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshNoticesIcon');
          const refreshBtn = document.getElementById('refreshNoticesBtn');
          refreshIcon.textContent = '🔄';
          refreshBtn.disabled = false;
          refreshBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
      }
    }

    // お知らせリスト描画
    function renderNoticesList(notices) {
      const container = document.getElementById('noticesList');
      container.innerHTML = '';

      if (notices.length === 0) {
        container.innerHTML = '<div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">お知らせはありません</div>';
        return;
      }

      notices.forEach(notice => {
        const card = createNoticeCard(notice);
        container.appendChild(card);
      });
    }

    // お知らせカード作成
    function createNoticeCard(bulletin) {
      const div = document.createElement('div');
      div.className = 'bg-white rounded-lg shadow p-6';

      const typeColor = {
        'お知らせ': 'bg-blue-100 text-blue-800',
        '重要': 'bg-red-100 text-red-800',
        'イベント': 'bg-green-100 text-green-800',
        'その他': 'bg-gray-100 text-gray-800'
      }[bulletin.type] || 'bg-gray-100 text-gray-800';

      const statusColor = {
        '公開': 'bg-green-100 text-green-800',
        '下書き': 'bg-yellow-100 text-yellow-800',
        '非公開': 'bg-gray-100 text-gray-800'
      }[bulletin.status] || 'bg-gray-100 text-gray-800';

      div.innerHTML = `
        <div class="flex justify-between items-start mb-3">
          <div class="flex items-center gap-2">
            ${bulletin.pinned == 1 ? '<span class="text-yellow-500 text-xl">📌</span>' : ''}
            <h3 class="text-lg font-bold text-gray-900">${escapeHtml(bulletin.title)}</h3>
          </div>
          <div class="flex gap-2">
            <span class="px-2 py-1 text-xs font-semibold rounded ${typeColor}">${escapeHtml(bulletin.type)}</span>
            <span class="px-2 py-1 text-xs font-semibold rounded ${statusColor}">${escapeHtml(bulletin.status)}</span>
          </div>
        </div>
        <p class="text-gray-700 whitespace-pre-wrap mb-4">${escapeHtml(bulletin.body)}</p>
        <div class="flex justify-between items-center text-sm text-gray-500 border-t pt-3">
          <div>
            <span>投稿: ${escapeHtml(bulletin.author)} | ${formatDatetime(bulletin.created_at)}</span>
            ${bulletin.start_datetime ? `<br><span>公開期間: ${formatDatetime(bulletin.start_datetime)} 〜 ${bulletin.end_datetime ? formatDatetime(bulletin.end_datetime) : '無期限'}</span>` : ''}
          </div>
          <div class="flex gap-2">
            <button onclick='openModal("edit", ${JSON.stringify(bulletin).replace(/'/g, "&apos;")})' class="text-blue-600 hover:text-blue-900">編集</button>
            <button onclick='deleteBulletin("${bulletin.bulletin_id}", "${escapeHtml(bulletin.title)}")' class="text-red-600 hover:text-red-900">削除</button>
          </div>
        </div>
      `;

      return div;
    }

    // モーダル開く
    function openModal(mode, data = null) {
      currentMode = mode;
      const modal = document.getElementById('modal');
      const form = document.getElementById('bulletinForm');
      const title = document.getElementById('modalTitle');

      form.reset();

      if (mode === 'create') {
        title.textContent = '掲示板投稿';
      } else {
        title.textContent = '掲示板編集';
        document.getElementById('bulletinId').value = data.bulletin_id;
        document.getElementById('title').value = data.title;
        document.getElementById('body').value = data.body;
        document.getElementById('type').value = data.type;
        document.getElementById('status').value = data.status;
        document.getElementById('pinned').value = data.pinned;
        if (data.start_datetime) {
          document.getElementById('startDatetime').value = data.start_datetime.replace(' ', 'T').substring(0, 16);
        }
        if (data.end_datetime) {
          document.getElementById('endDatetime').value = data.end_datetime.replace(' ', 'T').substring(0, 16);
        }
      }

      modal.classList.remove('hidden');
    }

    // モーダル閉じる
    function closeModal() {
      document.getElementById('modal').classList.add('hidden');
    }

    // フォーム送信
    document.getElementById('bulletinForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);
      const data = {
        title: formData.get('title'),
        body: formData.get('body'),
        type: formData.get('type'),
        status: formData.get('status'),
        pinned: formData.get('pinned'),
        start_datetime: formData.get('start_datetime') || null,
        end_datetime: formData.get('end_datetime') || null
      };

      try {
        let url = '/api/bulletins.php';
        let method = 'POST';

        if (currentMode === 'edit') {
          const bulletinId = document.getElementById('bulletinId').value;
          url = `/api/bulletins.php?id=${bulletinId}`;
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
          loadNotices();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    });

    // 削除
    async function deleteBulletin(bulletinId, title) {
      if (!confirm(`「${title}」を削除しますか？\nこの操作は取り消せません。`)) {
        return;
      }

      try {
        const response = await fetch(`/api/bulletins.php?id=${bulletinId}`, {
          method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadNotices();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // 更新
    function refreshNotices() {
      loadNotices(true);
    }

    // 日時フォーマット
    function formatDatetime(datetime) {
      if (!datetime) return '';
      const d = new Date(datetime);
      return d.toLocaleString('ja-JP', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    // HTMLエスケープ
    function escapeHtml(text) {
      if (text === null || text === undefined) return '';
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>
</body>

</html>
