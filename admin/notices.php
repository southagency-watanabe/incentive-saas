<?php
require_once __DIR__ . '/../includes/session.php';

// 管理者権限チェック
requireAdmin();

// ページタイトルとアクティブページ設定
$page_title = 'お知らせ';
$active_page = 'notices';
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?> - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
  <div class="flex h-screen">
    <?php include __DIR__ . '/../includes/performance/sidebar.php'; ?>

    <!-- メインコンテンツエリア -->
    <main class="flex-1 overflow-y-auto">
      <!-- ページヘッダー -->
      <header class="bg-white shadow-sm border-b">
        <div class="px-8 py-6">
          <h2 class="text-2xl font-bold text-gray-800">お知らせ</h2>
        </div>
      </header>

      <!-- メインコンテンツ -->
      <div class="px-8 py-8">
      <!-- タブナビゲーション -->
      <div class="mb-6 border-b border-gray-200">
        <nav class="flex space-x-8">
          <button id="noticesTab" onclick="switchTab('notices')" class="py-4 px-1 border-b-2 border-blue-600 font-medium text-sm text-blue-600">
            お知らせ一覧
          </button>
          <button id="templatesTab" onclick="switchTab('templates')" class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
            テンプレート管理
          </button>
        </nav>
      </div>

      <!-- お知らせセクション -->
      <div id="noticesSection">
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

      <!-- テンプレート管理セクション -->
      <div id="templatesSection" class="hidden">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800">テンプレート管理</h2>
          <button onclick="openTemplateModal('create')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <span>+ 新規テンプレート</span>
          </button>
        </div>
        <div class="bg-white rounded-lg shadow">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">テンプレート名</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">タイトル</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">作成日</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
              </tr>
            </thead>
            <tbody id="templatesTableBody" class="bg-white divide-y divide-gray-200">
              <!-- テンプレートはJavaScriptで挿入 -->
            </tbody>
          </table>
        </div>
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

        <!-- テンプレート選択ボタン -->
        <div class="flex gap-2">
          <button type="button" onclick="openTemplateSelectModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">
            📋 テンプレートから作成
          </button>
          <button type="button" onclick="saveAsTemplate()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 text-sm">
            💾 テンプレートとして保存
          </button>
        </div>

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
              <option value="非公開">非公開（下書き）</option>
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

  <!-- テンプレート選択モーダル -->
  <div id="templateSelectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold">テンプレートから作成</h3>
        <button onclick="closeTemplateSelectModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
      </div>
      <div id="templateSelectList" class="space-y-2 max-h-96 overflow-y-auto">
        <!-- テンプレート一覧がJavaScriptで挿入されます -->
      </div>
    </div>
  </div>

  <!-- テンプレート編集モーダル -->
  <div id="templateModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 id="templateModalTitle" class="text-xl font-bold">テンプレート編集</h3>
        <button onclick="closeTemplateModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
      </div>

      <form id="templateForm" class="space-y-4">
        <input type="hidden" id="templateId" name="template_id">

        <!-- テンプレート名 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">テンプレート名 <span class="text-red-500">*</span></label>
          <input type="text" id="templateName" name="template_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- タイトル -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">タイトル <span class="text-red-500">*</span></label>
          <input type="text" id="templateTitle" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- 本文 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">本文 <span class="text-red-500">*</span></label>
          <textarea id="templateBody" name="body" rows="8" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" onclick="closeTemplateModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
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
    // タブ切り替え
    function switchTab(tab) {
      const noticesTab = document.getElementById('noticesTab');
      const templatesTab = document.getElementById('templatesTab');
      const noticesSection = document.getElementById('noticesSection');
      const templatesSection = document.getElementById('templatesSection');

      if (tab === 'notices') {
        noticesTab.classList.add('border-blue-600', 'text-blue-600');
        noticesTab.classList.remove('border-transparent', 'text-gray-500');
        templatesTab.classList.add('border-transparent', 'text-gray-500');
        templatesTab.classList.remove('border-blue-600', 'text-blue-600');
        noticesSection.classList.remove('hidden');
        templatesSection.classList.add('hidden');
      } else {
        templatesTab.classList.add('border-blue-600', 'text-blue-600');
        templatesTab.classList.remove('border-transparent', 'text-gray-500');
        noticesTab.classList.add('border-transparent', 'text-gray-500');
        noticesTab.classList.remove('border-blue-600', 'text-blue-600');
        templatesSection.classList.remove('hidden');
        noticesSection.classList.add('hidden');
        loadTemplates();
      }
    }

    // テンプレート一覧を読み込み
    async function loadTemplates() {
      try {
        const response = await fetch('/api/bulletin_templates.php');
        const result = await response.json();

        if (result.success) {
          renderTemplates(result.data);
        }
      } catch (error) {
        console.error('テンプレート読み込みエラー:', error);
      }
    }

    // テンプレート一覧を表示
    function renderTemplates(templates) {
      const tbody = document.getElementById('templatesTableBody');
      if (!templates || templates.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">テンプレートがありません</td></tr>';
        return;
      }

      tbody.innerHTML = templates.map(template => `
        <tr>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(template.template_name)}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(template.title)}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(template.created_at).toLocaleDateString('ja-JP')}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <button onclick="editTemplate('${template.template_id}')" class="text-blue-600 hover:text-blue-900 mr-3">編集</button>
            <button onclick="deleteTemplate('${template.template_id}')" class="text-red-600 hover:text-red-900">削除</button>
          </td>
        </tr>
      `).join('');
    }

    // テンプレートモーダルを開く
    function openTemplateModal(mode, templateId = null) {
      const modal = document.getElementById('templateModal');
      const title = document.getElementById('templateModalTitle');
      const form = document.getElementById('templateForm');
      
      form.reset();
      
      if (mode === 'create') {
        title.textContent = '新規テンプレート作成';
        document.getElementById('templateId').value = '';
      } else {
        title.textContent = 'テンプレート編集';
        // テンプレートデータを読み込んで設定
        loadTemplateData(templateId);
      }
      
      modal.classList.remove('hidden');
    }

    async function loadTemplateData(templateId) {
      try {
        const response = await fetch('/api/bulletin_templates.php');
        const result = await response.json();
        
        if (result.success) {
          const template = result.data.find(t => t.template_id === templateId);
          if (template) {
            document.getElementById('templateId').value = template.template_id;
            document.getElementById('templateName').value = template.template_name;
            document.getElementById('templateTitle').value = template.title;
            document.getElementById('templateBody').value = template.body;
          }
        }
      } catch (error) {
        console.error('テンプレート読み込みエラー:', error);
      }
    }

    function closeTemplateModal() {
      document.getElementById('templateModal').classList.add('hidden');
    }

    function editTemplate(templateId) {
      openTemplateModal('edit', templateId);
    }

    async function deleteTemplate(templateId) {
      if (!confirm('このテンプレートを削除してもよろしいですか？')) return;
      
      try {
        const response = await fetch('/api/bulletin_templates.php', {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ template_id: templateId })
        });
        
        const result = await response.json();
        if (result.success) {
          alert(result.message);
          loadTemplates();
        }
      } catch (error) {
        console.error('削除エラー:', error);
        alert('削除に失敗しました。');
      }
    }

    // テンプレートフォーム送信
    document.getElementById('templateForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const formData = new FormData(e.target);
      const templateId = formData.get('template_id');
      const data = {
        template_name: formData.get('template_name'),
        title: formData.get('title'),
        body: formData.get('body')
      };
      
      if (templateId) {
        data.template_id = templateId;
      }
      
      try {
        const response = await fetch('/api/bulletin_templates.php', {
          method: templateId ? 'PUT' : 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
          alert(result.message);
          closeTemplateModal();
          loadTemplates();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error('保存エラー:', error);
        alert('保存に失敗しました。');
      }
    });

    // テンプレート選択モーダルを開く
    async function openTemplateSelectModal() {
      try {
        const response = await fetch('/api/bulletin_templates.php');
        const result = await response.json();
        
        if (result.success) {
          renderTemplateSelectList(result.data);
          document.getElementById('templateSelectModal').classList.remove('hidden');
        }
      } catch (error) {
        console.error('テンプレート読み込みエラー:', error);
      }
    }

    function closeTemplateSelectModal() {
      document.getElementById('templateSelectModal').classList.add('hidden');
    }

    function renderTemplateSelectList(templates) {
      const container = document.getElementById('templateSelectList');
      
      if (!templates || templates.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 py-4">テンプレートがありません</p>';
        return;
      }
      
      container.innerHTML = templates.map(template => `
        <div class="border border-gray-300 rounded-md p-4 hover:bg-gray-50 cursor-pointer" onclick="selectTemplate('${template.template_id}')">
          <h4 class="font-bold text-gray-900">${escapeHtml(template.template_name)}</h4>
          <p class="text-sm text-gray-600 mt-1">${escapeHtml(template.title)}</p>
        </div>
      `).join('');
    }

    async function selectTemplate(templateId) {
      try {
        const response = await fetch('/api/bulletin_templates.php');
        const result = await response.json();
        
        if (result.success) {
          const template = result.data.find(t => t.template_id === templateId);
          if (template) {
            document.getElementById('title').value = template.title;
            document.getElementById('body').value = template.body;
            closeTemplateSelectModal();
          }
        }
      } catch (error) {
        console.error('テンプレート読み込みエラー:', error);
      }
    }

    // テンプレートとして保存
    async function saveAsTemplate() {
      const title = document.getElementById('title').value;
      const body = document.getElementById('body').value;
      
      if (!title || !body) {
        alert('タイトルと本文を入力してください。');
        return;
      }
      
      const templateName = prompt('テンプレート名を入力してください:');
      if (!templateName) return;
      
      try {
        const response = await fetch('/api/bulletin_templates.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            template_name: templateName,
            title: title,
            body: body
          })
        });
        
        const result = await response.json();
        if (result.success) {
          alert('テンプレートとして保存しました。');
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error('保存エラー:', error);
        alert('保存に失敗しました。');
      }
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>
</body>

</html>
