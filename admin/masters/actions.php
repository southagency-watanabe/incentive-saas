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
  <title>アクションマスタ - インセンティブSaaS</title>
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
        <button onclick="toggleMasterMenu()" class="w-full flex items-center justify-between px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
          <span class="font-medium">マスタ管理</span>
          <svg id="masterArrow" class="w-4 h-4 transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div id="masterSubmenu" class="bg-gray-50">
          <a href="/admin/masters/members.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>メンバー</span>
          </a>
          <a href="/admin/masters/teams.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>チーム</span>
          </a>
          <a href="/admin/masters/products.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>商品</span>
          </a>
          <a href="/admin/masters/actions.php" class="flex items-center px-6 py-2 pl-12 text-sm text-blue-600 font-medium bg-blue-50 hover:bg-blue-100">
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

      <!-- 実績管理ドロップダウン -->
      <div>
        <button onclick="togglePerformanceMenu()" class="w-full flex items-center justify-between px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
          <span>実績管理</span>
          <svg id="performanceArrow" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div id="performanceSubmenu" class="hidden bg-gray-50">
          <div>
            <button onclick="toggleTimeSeriesMenu()" class="w-full flex items-center justify-between px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
              <span>時系列</span>
              <svg id="timeSeriesArrow" class="w-3 h-3 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>
            <div id="timeSeriesSubmenu" class="hidden bg-gray-100">
              <a href="/admin/performance/monthly.php" class="flex items-center px-6 py-2 pl-20 text-sm text-gray-700 hover:bg-gray-200"><span>月別</span></a>
              <a href="/admin/performance/weekly.php" class="flex items-center px-6 py-2 pl-20 text-sm text-gray-700 hover:bg-gray-200"><span>週別</span></a>
              <a href="/admin/performance/daily.php" class="flex items-center px-6 py-2 pl-20 text-sm text-gray-700 hover:bg-gray-200"><span>日別</span></a>
              <a href="/admin/performance/dayofweek.php" class="flex items-center px-6 py-2 pl-20 text-sm text-gray-700 hover:bg-gray-200"><span>曜日別</span></a>
            </div>
          </div>
          <a href="/admin/performance/product.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200"><span>商品別</span></a>
          <a href="/admin/performance/member_team.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200"><span>メンバー別/チーム別</span></a>
        </div>
      </div>
      <a href="/admin/events.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>イベント</span>
      </a>
      <a href="/admin/notices.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
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
        <h2 class="text-2xl font-bold text-gray-800">アクションマスタ</h2>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
      <!-- ヘッダーアクション -->
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">アクション一覧</h3>
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="action_id" data-type="string">
              アクションID <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="action_name" data-type="string">
              アクション名 <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="category" data-type="string">
              カテゴリ <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="target" data-type="string">
              対象 <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="point" data-type="number">
              付与pt <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="status" data-type="string">
              ステータス <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="approval_required" data-type="string">
              承認要否 <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">操作</th>
          </tr>
        </thead>
        <tbody id="actionTableBody" class="bg-white divide-y divide-gray-200">
          <!-- データはJavaScriptで挿入 -->
        </tbody>
      </table>
    </div>
  </main>

  <!-- モーダル -->
  <div id="modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 id="modalTitle" class="text-xl font-bold">アクション登録</h3>
        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
      </div>

      <form id="actionForm" class="space-y-4">
        <input type="hidden" id="actionId" name="action_id">

        <!-- アクション名 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">アクション名 <span class="text-red-500">*</span></label>
          <input type="text" id="actionName" name="action_name" required placeholder="例：Google口コミ獲得" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- カテゴリ -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">カテゴリ</label>
          <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <option value="">カテゴリなし</option>
            <option value="指名・接客">指名・接客</option>
            <option value="営業活動">営業活動</option>
            <option value="販促・SNS">販促・SNS</option>
          </select>
          <p class="text-xs text-gray-500 mt-1">※ カテゴリを設定すると、イベント登録時にカテゴリ単位で倍率を設定できます</p>
        </div>

        <!-- 繰り返し設定 -->
        <div class="grid grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">繰り返し <span class="text-red-500">*</span></label>
            <select id="repeatType" name="repeat_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="単発">単発</option>
              <option value="毎週">毎週</option>
              <option value="毎月">毎月</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">開始日時 <span class="text-red-500">*</span></label>
            <input type="datetime-local" id="startDate" name="start_date" required value="<?= date('Y-m-d\T00:00') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">終了日時 <span class="text-red-500">*</span></label>
            <input type="datetime-local" id="endDate" name="end_date" required value="<?= date('Y-m-d\T00:00') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>
        </div>

        <!-- 曜日選択（毎週の場合） -->
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

        <!-- 毎月日（毎月の場合） -->
        <div id="dayOfMonthContainer" class="hidden">
          <label class="block text-sm font-medium text-gray-700 mb-1">毎月日 <span class="text-red-500">*</span></label>
          <input type="text" id="dayOfMonth" name="day_of_month" placeholder="例：29 (月末は99)" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <!-- 対象 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">対象 <span class="text-red-500">*</span></label>
            <select id="target" name="target" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="">選択してください</option>
              <option value="個人">個人</option>
              <option value="チーム">チーム</option>
            </select>
          </div>

          <!-- 付与pt -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">付与pt <span class="text-red-500">*</span></label>
            <input type="number" id="point" name="point" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
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

    // マスター管理メニューの開閉
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

    function togglePerformanceMenu() {
      const submenu = document.getElementById('performanceSubmenu');
      const arrow = document.getElementById('performanceArrow');
      if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
      } else {
        submenu.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
      }
    }

    function toggleTimeSeriesMenu() {
      const submenu = document.getElementById('timeSeriesSubmenu');
      const arrow = document.getElementById('timeSeriesArrow');
      if (submenu.classList.contains('hidden')) {
        submenu.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
      } else {
        submenu.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
      }
    }

    // グローバル変数
    let allActions = [];
    let sortConfig = {
      column: null,
      direction: 'asc'
    };

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadActions();
      setupRepeatTypeToggle();
      setupSortableHeaders();
    });

    // 繰り返し設定の切り替え
    function setupRepeatTypeToggle() {
      const repeatType = document.getElementById('repeatType');
      const daysOfWeekContainer = document.getElementById('daysOfWeekContainer');
      const dayOfMonthContainer = document.getElementById('dayOfMonthContainer');

      repeatType.addEventListener('change', (e) => {
        const value = e.target.value;
        daysOfWeekContainer.classList.add('hidden');
        dayOfMonthContainer.classList.add('hidden');

        if (value === '毎週') {
          daysOfWeekContainer.classList.remove('hidden');
        } else if (value === '毎月') {
          dayOfMonthContainer.classList.remove('hidden');
        }
      });
    }

    // アクション一覧取得
    async function loadActions(showLoading = false) {
      try {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshIcon');
          const refreshBtn = document.getElementById('refreshBtn');
          refreshIcon.textContent = '⏳';
          refreshBtn.disabled = true;
          refreshBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const response = await fetch('/api/actions.php');
        const result = await response.json();

        if (result.success) {
          allActions = result.data;
          renderTable(allActions);
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
    function renderTable(actions) {
      const tbody = document.getElementById('actionTableBody');
      tbody.innerHTML = '';

      if (actions.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      actions.forEach(action => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(action.action_id)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(action.action_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${action.category ? escapeHtml(action.category) : '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${action.target === '個人' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}">
                            ${escapeHtml(action.target)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(action.point)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${action.status === '有効' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${escapeHtml(action.status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${action.approval_required === '必要' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'}">
                            ${escapeHtml(action.approval_required)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <button onclick='openModal("edit", ${JSON.stringify(action)})' class="text-blue-600 hover:text-blue-900">編集</button>
                        <button onclick='duplicateAction("${action.action_id}")' class="text-green-600 hover:text-green-900">複製</button>
                        <button onclick='deleteAction("${action.action_id}", "${escapeHtml(action.action_name)}")' class="text-red-600 hover:text-red-900">削除</button>
                    </td>
                `;
        tbody.appendChild(tr);
      });
    }

    // ソート可能なヘッダーの設定
    function setupSortableHeaders() {
      const sortableHeaders = document.querySelectorAll('th[data-sort]');
      
      sortableHeaders.forEach(header => {
        header.addEventListener('click', () => {
          const column = header.getAttribute('data-sort');
          sortTable(column);
        });
      });
    }

    // テーブルのソート処理
    function sortTable(column) {
      if (sortConfig.column === column) {
        sortConfig.direction = sortConfig.direction === 'asc' ? 'desc' : 'asc';
      } else {
        sortConfig.column = column;
        sortConfig.direction = 'asc';
      }

      const header = document.querySelector(`th[data-sort="${column}"]`);
      const dataType = header ? header.getAttribute('data-type') : 'string';

      const sortedActions = [...allActions].sort((a, b) => {
        let aValue = a[column];
        let bValue = b[column];

        if (dataType === 'number') {
          aValue = parseFloat(aValue) || 0;
          bValue = parseFloat(bValue) || 0;
          return sortConfig.direction === 'asc' ? aValue - bValue : bValue - aValue;
        }
        
        aValue = String(aValue || '').toLowerCase();
        bValue = String(bValue || '').toLowerCase();

        if (aValue < bValue) return sortConfig.direction === 'asc' ? -1 : 1;
        if (aValue > bValue) return sortConfig.direction === 'asc' ? 1 : -1;
        return 0;
      });

      updateSortIcons();
      renderTable(sortedActions);
    }

    // ソートアイコンの更新
    function updateSortIcons() {
      const sortableHeaders = document.querySelectorAll('th[data-sort]');
      
      sortableHeaders.forEach(header => {
        const column = header.getAttribute('data-sort');
        const icon = header.querySelector('.sort-icon');
        
        if (column === sortConfig.column) {
          icon.textContent = sortConfig.direction === 'asc' ? '↑' : '↓';
          icon.classList.add('text-blue-600');
        } else {
          icon.textContent = '⇅';
          icon.classList.remove('text-blue-600');
        }
      });
    }

    // モーダル開く
    function openModal(mode, data = null) {
      currentMode = mode;
      const modal = document.getElementById('modal');
      const form = document.getElementById('actionForm');
      const title = document.getElementById('modalTitle');

      form.reset();

      // チェックボックスクリア
      document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);

      // コンテナ非表示
      document.getElementById('daysOfWeekContainer').classList.add('hidden');
      document.getElementById('dayOfMonthContainer').classList.add('hidden');

      if (mode === 'create') {
        title.textContent = 'アクション登録';
        document.getElementById('approvalRequired').value = '必要';
        document.getElementById('repeatType').value = '単発';
      } else {
        title.textContent = 'アクション編集';
        document.getElementById('actionId').value = data.action_id;
        document.getElementById('actionName').value = data.action_name;
        document.getElementById('category').value = data.category || '';
        document.getElementById('repeatType').value = data.repeat_type || '単発';
        // datetime形式をdatetime-local形式に変換（YYYY-MM-DDTHH:MM）
        document.getElementById('startDate').value = data.start_date ? data.start_date.substring(0, 16) : '';
        document.getElementById('endDate').value = data.end_date ? data.end_date.substring(0, 16) : '';
        document.getElementById('target').value = data.target;
        document.getElementById('point').value = data.point;
        document.getElementById('approvalRequired').value = data.approval_required;
        document.getElementById('status').value = data.status;
        document.getElementById('description').value = data.description || '';

        // 繰り返し設定
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
    document.getElementById('actionForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);
      const repeatType = formData.get('repeat_type');

      let daysOfWeek = null;
      let dayOfMonth = null;

      // 繰り返し設定
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

      // datetime-local形式の値をdatetime形式に変換（YYYY-MM-DD HH:MM:SS）
      const startDateTime = formData.get('start_date');
      const endDateTime = formData.get('end_date');
      const startDate = startDateTime ? startDateTime.replace('T', ' ') + ':00' : '';
      const endDate = endDateTime ? endDateTime.replace('T', ' ') + ':00' : '';

      const data = {
        action_name: formData.get('action_name'),
        repeat_type: repeatType,
        start_date: startDate,
        end_date: endDate,
        days_of_week: daysOfWeek,
        day_of_month: dayOfMonth,
        target: formData.get('target'),
        point: formData.get('point'),
        approval_required: formData.get('approval_required'),
        status: formData.get('status'),
        description: formData.get('description')
      };

      try {
        let url = '/api/actions.php';
        let method = 'POST';

        if (currentMode === 'edit') {
          const actionId = document.getElementById('actionId').value;
          url = `/api/actions.php?id=${actionId}`;
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
          loadActions();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    });

    // 削除
    async function deleteAction(actionId, name) {
      if (!confirm(`「${name}」を削除しますか？\nこの操作は取り消せません。`)) {
        return;
      }

      try {
        const response = await fetch(`/api/actions.php?id=${actionId}`, {
          method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadActions();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // 複製
    async function duplicateAction(actionId) {
      if (!confirm('このアクションを複製しますか？')) {
        return;
      }

      try {
        const response = await fetch('/api/actions/duplicate.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action_id: actionId
          })
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadActions();
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
      loadActions(true);
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