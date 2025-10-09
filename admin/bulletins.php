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
  <title>掲示板管理 - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
  <!-- ヘッダー -->
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">インセンティブSaaS</h1>
        <p class="text-sm text-gray-600">掲示板管理</p>
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
        <a href="/admin/masters/members.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">マスタ管理</a>
        <a href="/admin/sales/input.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">売上管理</a>
        <a href="/admin/approvals.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">承認管理</a>
        <a href="/admin/performance.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">実績管理</a>
        <a href="/admin/bulletins.php" class="py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">掲示板管理</a>
        <a href="/admin/ranking.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">ランキング</a>
      </div>
    </div>
  </nav>

  <!-- メインコンテンツ -->
  <main class="max-w-7xl mx-auto px-4 py-8">
    <!-- イベント掲示セクション -->
    <div class="mb-8">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">📌 イベント掲示</h2>
        <button id="refreshBtn" onclick="refreshList()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center gap-2">
          <span id="refreshIcon">🔄</span>
          <span>更新</span>
        </button>
      </div>
      <div id="pinnedList" class="space-y-4">
        <!-- ピン留めされたイベント掲示はJavaScriptで挿入 -->
      </div>
    </div>

    <!-- 掲示板セクション -->
    <div>
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">💬 掲示板</h2>
        <button onclick="openModal('create')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          新規投稿
        </button>
      </div>
      <div id="bulletinList" class="space-y-4">
        <!-- 一般掲示板はJavaScriptで挿入 -->
      </div>
    </div>
  </main>

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
    let currentMode = 'create';

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadBulletins();
    });

    // 掲示板一覧取得
    async function loadBulletins(showLoading = false) {
      try {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshIcon');
          const refreshBtn = document.getElementById('refreshBtn');
          refreshIcon.textContent = '⏳';
          refreshBtn.disabled = true;
          refreshBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const response = await fetch('/api/bulletins.php?filter=all');
        const result = await response.json();

        if (result.success) {
          renderBulletinList(result.data);
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

    // 掲示板リスト描画
    function renderBulletinList(bulletins) {
      // イベント投稿（related_event_idがあるもの）とそれ以外で分ける
      const eventBulletins = bulletins.filter(b => b.related_event_id);
      const normalBulletins = bulletins.filter(b => !b.related_event_id);

      // イベント掲示を描画
      renderPinnedList(eventBulletins);

      // 一般掲示板を描画
      renderNormalList(normalBulletins);
    }

    // イベント掲示を描画
    function renderPinnedList(bulletins) {
      const container = document.getElementById('pinnedList');
      container.innerHTML = '';

      if (bulletins.length === 0) {
        container.innerHTML = '<div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">イベント掲示はありません</div>';
        return;
      }

      bulletins.forEach(bulletin => {
        const div = createBulletinCard(bulletin, true);
        container.appendChild(div);
      });
    }

    // 一般掲示板を描画
    function renderNormalList(bulletins) {
      const container = document.getElementById('bulletinList');
      container.innerHTML = '';

      if (bulletins.length === 0) {
        container.innerHTML = '<div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">掲示板投稿はありません</div>';
        return;
      }

      bulletins.forEach(bulletin => {
        const div = createBulletinCard(bulletin, false);
        container.appendChild(div);
      });
    }

    // 掲示板カード作成
    function createBulletinCard(bulletin, isEvent = false) {
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
            ${isEvent ? '<span class="text-green-500 text-xl">🎉</span>' : ''}
            ${bulletin.pinned == 1 && !isEvent ? '<span class="text-yellow-500 text-xl">📌</span>' : ''}
            <h3 class="text-lg font-bold text-gray-900">${escapeHtml(bulletin.title)}</h3>
            ${isEvent ? '<span class="ml-2 px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">イベント</span>' : ''}
          </div>
          <div class="flex gap-2">
            ${!isEvent ? `<span class="px-2 py-1 text-xs font-semibold rounded ${typeColor}">${escapeHtml(bulletin.type)}</span>` : ''}
            <span class="px-2 py-1 text-xs font-semibold rounded ${statusColor}">${escapeHtml(bulletin.status)}</span>
          </div>
        </div>
        <p class="text-gray-700 whitespace-pre-wrap mb-4">${escapeHtml(bulletin.body)}</p>
        <div class="flex justify-between items-center text-sm text-gray-500 border-t pt-3">
          <div>
            <span>投稿: ${escapeHtml(bulletin.author)} | ${formatDatetime(bulletin.created_at)}</span>
            ${bulletin.start_datetime ? `<br><span>公開期間: ${formatDatetime(bulletin.start_datetime)} 〜 ${bulletin.end_datetime ? formatDatetime(bulletin.end_datetime) : '無期限'}</span>` : ''}
            ${isEvent && bulletin.related_event_id ? `<br><span class="text-green-600">イベントID: ${escapeHtml(bulletin.related_event_id)}</span>` : ''}
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
          loadBulletins();
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
          loadBulletins();
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
      loadBulletins(true);
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
