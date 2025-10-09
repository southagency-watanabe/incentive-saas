<?php
require_once __DIR__ . '/../../includes/session.php';

// 管理者権限チェック
requireAdmin();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>タスクマスタ - インセンティブSaaS</title>
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
        <span>ダッシュボード</span>
      </a>
      <a href="/admin/masters/members.php" class="flex items-center px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
        <span class="font-medium">マスタ管理</span>
      </a>
      <a href="/admin/sales/input.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>売上管理</span>
      </a>
      <a href="/admin/approvals.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>承認管理</span>
      </a>
      <a href="/admin/performance.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>実績管理</span>
      </a>
      <a href="/admin/bulletins.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>掲示板管理</span>
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
        <h2 class="text-2xl font-bold text-gray-800">タスクマスタ</h2>
      </div>
      <!-- サブナビゲーション（マスタ切り替え） -->
      <div class="bg-gray-50 border-t">
        <div class="px-8">
          <div class="flex space-x-6">
            <a href="/admin/masters/members.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">メンバー</a>
            <a href="/admin/masters/teams.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">チーム</a>
            <a href="/admin/masters/products.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">商品</a>
            <a href="/admin/masters/actions.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">アクション</a>
            <a href="/admin/masters/tasks.php" class="py-3 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">タスク</a>
            <a href="/admin/masters/events.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">イベント</a>
          </div>
        </div>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
      <!-- ヘッダーアクション -->
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">タスク一覧</h3>
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
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">タスクID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">タスク名</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">種別</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">期間</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">繰り返し</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">設定</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">付与pt</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">1日上限</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
          </tr>
        </thead>
        <tbody id="taskTableBody" class="bg-white divide-y divide-gray-200">
          <!-- データはJavaScriptで挿入 -->
        </tbody>
      </table>
    </div>
  </main>

  <!-- モーダル -->
  <div id="modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 id="modalTitle" class="text-xl font-bold">タスク登録</h3>
        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
      </div>

      <form id="taskForm" class="space-y-4">
        <input type="hidden" id="taskId" name="task_id">

        <!-- タスク名 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">タスク名 <span class="text-red-500">*</span></label>
          <input type="text" id="taskName" name="task_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- 種別 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">種別 <span class="text-red-500">*</span></label>
            <select id="type" name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="">選択してください</option>
              <option value="個人">個人</option>
              <option value="チーム">チーム</option>
            </select>
          </div>

          <!-- 繰り返し -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">繰り返し <span class="text-red-500">*</span></label>
            <select id="repeatType" name="repeat_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="">選択してください</option>
              <option value="毎日">毎日</option>
              <option value="毎週">毎週</option>
              <option value="毎月">毎月</option>
            </select>
          </div>
        </div>

        <!-- 日時設定 -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">開始日時</label>
            <input type="datetime-local" id="startDatetime" name="start_datetime" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <p class="text-sm text-gray-500 mt-1">未指定の場合は無期限</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">終了日時</label>
            <input type="datetime-local" id="endDatetime" name="end_datetime" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <p class="text-sm text-gray-500 mt-1">未指定の場合は無期限</p>
          </div>
        </div>

        <!-- 曜日選択（毎週の場合のみ表示） -->
        <div id="daysOfWeekContainer" class="hidden">
          <label class="block text-sm font-medium text-gray-700 mb-2">曜日 <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-4 gap-2">
            <label class="flex items-center space-x-2">
              <input type="checkbox" name="days_of_week[]" value="月" class="rounded">
              <span>月</span>
            </label>
            <label class="flex items-center space-x-2">
              <input type="checkbox" name="days_of_week[]" value="火" class="rounded">
              <span>火</span>
            </label>
            <label class="flex items-center space-x-2">
              <input type="checkbox" name="days_of_week[]" value="水" class="rounded">
              <span>水</span>
            </label>
            <label class="flex items-center space-x-2">
              <input type="checkbox" name="days_of_week[]" value="木" class="rounded">
              <span>木</span>
            </label>
            <label class="flex items-center space-x-2">
              <input type="checkbox" name="days_of_week[]" value="金" class="rounded">
              <span>金</span>
            </label>
            <label class="flex items-center space-x-2">
              <input type="checkbox" name="days_of_week[]" value="土" class="rounded">
              <span>土</span>
            </label>
            <label class="flex items-center space-x-2">
              <input type="checkbox" name="days_of_week[]" value="日" class="rounded">
              <span>日</span>
            </label>
          </div>
        </div>

        <!-- 毎月日（毎月の場合のみ表示） -->
        <div id="dayOfMonthContainer" class="hidden">
          <label class="block text-sm font-medium text-gray-700 mb-1">毎月日 <span class="text-red-500">*</span></label>
          <input type="text" id="dayOfMonth" name="day_of_month" placeholder="例：15 または 末" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          <p class="text-sm text-gray-500 mt-1">数字（1〜31）または「末」を入力</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- 付与pt -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">付与pt <span class="text-red-500">*</span></label>
            <input type="number" id="point" name="point" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 1日上限 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">1日上限 <span class="text-red-500">*</span></label>
            <input type="number" id="dailyLimit" name="daily_limit" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <p class="text-sm text-gray-500 mt-1">0は無制限</p>
          </div>

          <!-- 承認要否 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">承認要否 <span class="text-red-500">*</span></label>
            <select id="approvalRequired" name="approval_required" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="必要">必要</option>
              <option value="不要">不要</option>
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

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadTasks();
      setupRepeatTypeToggle();
    });

    // 繰り返し設定の切り替え
    function setupRepeatTypeToggle() {
      const repeatType = document.getElementById('repeatType');
      const daysOfWeekContainer = document.getElementById('daysOfWeekContainer');
      const dayOfMonthContainer = document.getElementById('dayOfMonthContainer');

      repeatType.addEventListener('change', (e) => {
        const value = e.target.value;

        // すべて非表示
        daysOfWeekContainer.classList.add('hidden');
        dayOfMonthContainer.classList.add('hidden');

        // 選択に応じて表示
        if (value === '毎週') {
          daysOfWeekContainer.classList.remove('hidden');
        } else if (value === '毎月') {
          dayOfMonthContainer.classList.remove('hidden');
        }
      });
    }

    // タスク一覧取得
    async function loadTasks(showLoading = false) {
      try {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshIcon');
          const refreshBtn = document.getElementById('refreshBtn');
          refreshIcon.textContent = '⏳';
          refreshBtn.disabled = true;
          refreshBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const response = await fetch('/api/tasks.php');
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
    function renderTable(tasks) {
      const tbody = document.getElementById('taskTableBody');
      tbody.innerHTML = '';

      if (tasks.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      tasks.forEach(task => {
        let setting = '-';
        if (task.repeat_type === '毎週' && task.days_of_week) {
          setting = task.days_of_week;
        } else if (task.repeat_type === '毎月' && task.day_of_month) {
          setting = task.day_of_month + '日';
        }

        // 期間の表示
        let period = '-';
        if (task.start_datetime || task.end_datetime) {
          const start = task.start_datetime ? task.start_datetime.replace('T', ' ') : '-';
          const end = task.end_datetime ? task.end_datetime.replace('T', ' ') : '-';
          period = `${start}<br>〜<br>${end}`;
        }

        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(task.task_id)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(task.task_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${task.type === '個人' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}">
                            ${escapeHtml(task.type)}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 text-center">${period}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(task.repeat_type)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(setting)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(task.point)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${task.daily_limit == 0 ? '無制限' : task.daily_limit}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${task.status === '有効' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${escapeHtml(task.status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <button onclick='openModal("edit", ${JSON.stringify(task).replace(/'/g, "&apos;")})' class="text-blue-600 hover:text-blue-900">編集</button>
                        <button onclick='duplicateTask("${task.task_id}")' class="text-green-600 hover:text-green-900">複製</button>
                        <button onclick='deleteTask("${task.task_id}", "${escapeHtml(task.task_name)}")' class="text-red-600 hover:text-red-900">削除</button>
                    </td>
                `;
        tbody.appendChild(tr);
      });
    }

    // モーダル開く
    function openModal(mode, data = null) {
      currentMode = mode;
      const modal = document.getElementById('modal');
      const form = document.getElementById('taskForm');
      const title = document.getElementById('modalTitle');

      form.reset();

      // チェックボックスをすべてクリア
      document.querySelectorAll('input[name="days_of_week[]"]').forEach(cb => cb.checked = false);

      if (mode === 'create') {
        title.textContent = 'タスク登録';
        document.getElementById('approvalRequired').value = '必要';
        document.getElementById('daysOfWeekContainer').classList.add('hidden');
        document.getElementById('dayOfMonthContainer').classList.add('hidden');
      } else {
        title.textContent = 'タスク編集';
        document.getElementById('taskId').value = data.task_id;
        document.getElementById('taskName').value = data.task_name;
        document.getElementById('type').value = data.type;
        document.getElementById('repeatType').value = data.repeat_type;
        document.getElementById('startDatetime').value = data.start_datetime || '';
        document.getElementById('endDatetime').value = data.end_datetime || '';
        document.getElementById('point').value = data.point;
        document.getElementById('dailyLimit').value = data.daily_limit;
        document.getElementById('approvalRequired').value = data.approval_required;
        document.getElementById('status').value = data.status;
        document.getElementById('description').value = data.description || '';

        // 繰り返し設定に応じて表示
        if (data.repeat_type === '毎週') {
          document.getElementById('daysOfWeekContainer').classList.remove('hidden');
          if (data.days_of_week) {
            const days = data.days_of_week.split(',');
            days.forEach(day => {
              const checkbox = document.querySelector(`input[name="days_of_week[]"][value="${day}"]`);
              if (checkbox) checkbox.checked = true;
            });
          }
        } else if (data.repeat_type === '毎月') {
          document.getElementById('dayOfMonthContainer').classList.remove('hidden');
          document.getElementById('dayOfMonth').value = data.day_of_month || '';
        }
      }

      modal.classList.remove('hidden');
    }

    // モーダル閉じる
    function closeModal() {
      document.getElementById('modal').classList.add('hidden');
    }

    // フォーム送信
    document.getElementById('taskForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);
      const repeatType = formData.get('repeat_type');

      let daysOfWeek = null;
      let dayOfMonth = null;

      if (repeatType === '毎週') {
        const selectedDays = formData.getAll('days_of_week[]');
        if (selectedDays.length === 0) {
          alert('曜日を選択してください。');
          return;
        }
        daysOfWeek = selectedDays.join(',');
      } else if (repeatType === '毎月') {
        dayOfMonth = formData.get('day_of_month');
        if (!dayOfMonth) {
          alert('毎月日を入力してください。');
          return;
        }
      }

      const data = {
        task_name: formData.get('task_name'),
        type: formData.get('type'),
        repeat_type: repeatType,
        days_of_week: daysOfWeek,
        day_of_month: dayOfMonth,
        start_datetime: formData.get('start_datetime'),
        end_datetime: formData.get('end_datetime'),
        point: formData.get('point'),
        daily_limit: formData.get('daily_limit'),
        approval_required: formData.get('approval_required'),
        status: formData.get('status'),
        description: formData.get('description')
      };

      try {
        let url = '/api/tasks.php';
        let method = 'POST';

        if (currentMode === 'edit') {
          const taskId = document.getElementById('taskId').value;
          url = `/api/tasks.php?id=${taskId}`;
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
          loadTasks();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    });

    // 削除
    async function deleteTask(taskId, name) {
      if (!confirm(`「${name}」を削除しますか？\nこの操作は取り消せません。`)) {
        return;
      }

      try {
        const response = await fetch(`/api/tasks.php?id=${taskId}`, {
          method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadTasks();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // 複製
    async function duplicateTask(taskId) {
      if (!confirm('このタスクを複製しますか？')) {
        return;
      }

      try {
        const response = await fetch('/api/tasks/duplicate.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            task_id: taskId
          })
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadTasks();
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
      loadTasks(true);
    }

    // HTMLエスケープ
    function escapeHtml(text) {
      if (text === null || text === undefined) return '';
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>
    </main>
  </div>
</body>

</html>