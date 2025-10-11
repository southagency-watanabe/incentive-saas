<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/database.php';

// 管理者権限チェック
requireAdmin();

// 商品・アクション一覧取得（対象選択用）
$pdo = getDB();

$stmt = $pdo->prepare("SELECT product_id, product_name FROM products WHERE tenant_id = :tenant_id AND status = '有効' ORDER BY product_id ASC");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$products = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT action_id, action_name FROM actions WHERE tenant_id = :tenant_id AND status = '有効' ORDER BY action_id ASC");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$actions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>イベントマスタ - インセンティブSaaS</title>
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
          <a href="/admin/masters/actions.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>アクション</span>
          </a>
          <a href="/admin/masters/tasks.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>タスク</span>
          </a>
          <a href="/admin/masters/events.php" class="flex items-center px-6 py-2 pl-12 text-sm text-blue-600 font-medium bg-blue-50 hover:bg-blue-100">
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
        <h2 class="text-2xl font-bold text-gray-800">イベントマスタ</h2>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
      <!-- ヘッダーアクション -->
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">イベント一覧</h3>
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">イベントID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">イベント名</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">期間</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">繰り返し</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">対象</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">倍率</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
          </tr>
        </thead>
        <tbody id="eventTableBody" class="bg-white divide-y divide-gray-200">
          <!-- データはJavaScriptで挿入 -->
        </tbody>
      </table>
    </div>
  </main>

  <!-- モーダル -->
  <div id="modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white mb-10">
      <div class="flex justify-between items-center mb-4">
        <h3 id="modalTitle" class="text-xl font-bold">イベント登録</h3>
        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
      </div>

      <form id="eventForm" class="space-y-4">
        <input type="hidden" id="eventId" name="event_id">

        <!-- イベント名 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">イベント名 <span class="text-red-500">*</span></label>
          <input type="text" id="eventName" name="event_name" required placeholder="例：肉の日キャンペーン" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-3 gap-4">
          <!-- 繰り返し -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">繰り返し <span class="text-red-500">*</span></label>
            <select id="repeatType" name="repeat_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="">選択してください</option>
              <option value="単発">単発</option>
              <option value="毎週">毎週</option>
              <option value="毎月">毎月</option>
            </select>
          </div>

          <!-- 開始日時 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">開始日時 <span class="text-red-500">*</span></label>
            <input type="datetime-local" id="startDate" name="start_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 終了日時 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">終了日時 <span class="text-red-500">*</span></label>
            <input type="datetime-local" id="endDate" name="end_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
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
          <input type="text" id="dayOfMonth" name="day_of_month" placeholder="例：29" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          <p class="text-sm text-gray-500 mt-1">数字（1〜31）を入力</p>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <!-- 対象タイプ -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">対象タイプ <span class="text-red-500">*</span></label>
            <select id="targetType" name="target_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="">選択してください</option>
              <option value="全商品">全商品</option>
              <option value="特定商品">特定商品</option>
              <option value="全アクション">全アクション</option>
              <option value="特定アクション">特定アクション</option>
            </select>
          </div>

          <!-- 倍率 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">倍率 <span class="text-red-500">*</span></label>
            <input type="number" id="multiplier" name="multiplier" min="0" step="0.1" required placeholder="例：2.0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
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

        <!-- 対象選択（特定商品の場合） -->
        <div id="targetProductsContainer" class="hidden">
          <label class="block text-sm font-medium text-gray-700 mb-2">対象商品 <span class="text-red-500">*</span></label>
          <div class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto">
            <div class="grid grid-cols-2 gap-2">
              <?php foreach ($products as $product): ?>
                <label class="flex items-center space-x-2">
                  <input type="checkbox" name="target_products[]" value="<?= htmlspecialchars($product['product_id']) ?>" class="rounded">
                  <span class="text-sm"><?= htmlspecialchars($product['product_name']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- 対象選択（特定アクションの場合） -->
        <div id="targetActionsContainer" class="hidden">
          <label class="block text-sm font-medium text-gray-700 mb-2">対象アクション <span class="text-red-500">*</span></label>
          <div class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto">
            <div class="grid grid-cols-2 gap-2">
              <?php foreach ($actions as $action): ?>
                <label class="flex items-center space-x-2">
                  <input type="checkbox" name="target_actions[]" value="<?= htmlspecialchars($action['action_id']) ?>" class="rounded">
                  <span class="text-sm"><?= htmlspecialchars($action['action_name']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- 説明 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">説明</label>
          <textarea id="description" name="description" rows="2" placeholder="例：毎月29日は肉類商品ポイント2倍" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <!-- 告知公開 -->
        <div class="border-t pt-4">
          <label class="flex items-center space-x-2 mb-3">
            <input type="checkbox" id="publishNotice" name="publish_notice" class="rounded">
            <span class="font-medium">告知を掲示板に投稿</span>
          </label>

          <div id="noticeContainer" class="hidden space-y-3 pl-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">告知公開日時</label>
              <input type="datetime-local" id="noticePublishAt" name="notice_publish_at" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <p class="text-sm text-gray-500 mt-1">未指定の場合は即時公開されます</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">告知タイトル</label>
              <input type="text" id="noticeTitle" name="notice_title" placeholder="例：肉の日キャンペーン開催！" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">告知本文</label>
              <textarea id="noticeBody" name="notice_body" rows="3" placeholder="詳細を入力..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
          </div>
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

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadEvents();
      setupRepeatTypeToggle();
      setupTargetTypeToggle();
      setupPublishNoticeToggle();
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

    // 対象タイプの切り替え
    function setupTargetTypeToggle() {
      const targetType = document.getElementById('targetType');
      const targetProductsContainer = document.getElementById('targetProductsContainer');
      const targetActionsContainer = document.getElementById('targetActionsContainer');

      targetType.addEventListener('change', (e) => {
        const value = e.target.value;
        targetProductsContainer.classList.add('hidden');
        targetActionsContainer.classList.add('hidden');

        if (value === '特定商品') {
          targetProductsContainer.classList.remove('hidden');
        } else if (value === '特定アクション') {
          targetActionsContainer.classList.remove('hidden');
        }
      });
    }

    // 告知公開の切り替え
    function setupPublishNoticeToggle() {
      const publishNotice = document.getElementById('publishNotice');
      const noticeContainer = document.getElementById('noticeContainer');

      publishNotice.addEventListener('change', (e) => {
        if (e.target.checked) {
          noticeContainer.classList.remove('hidden');
        } else {
          noticeContainer.classList.add('hidden');
        }
      });
    }

    // イベント一覧取得
    async function loadEvents(showLoading = false) {
      try {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshIcon');
          const refreshBtn = document.getElementById('refreshBtn');
          refreshIcon.textContent = '⏳';
          refreshBtn.disabled = true;
          refreshBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const response = await fetch('/api/events.php');
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
    function renderTable(events) {
      const tbody = document.getElementById('eventTableBody');
      tbody.innerHTML = '';

      if (events.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      events.forEach(event => {
        // 日付のみフォーマット（YYYY-MM-DD）
        const formatDate = (datetime) => {
          if (!datetime) return '';
          return datetime.substring(0, 10);
        };
        const period = `${formatDate(event.start_date)} 〜 ${formatDate(event.end_date)}`;

        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(event.event_id)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(event.event_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${period}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(event.repeat_type)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(event.target_type)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(event.multiplier).toFixed(1)}倍</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${event.status === '有効' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${escapeHtml(event.status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <button onclick='openModal("edit", ${JSON.stringify(event).replace(/'/g, "&apos;")})' class="text-blue-600 hover:text-blue-900">編集</button>
                        <button onclick='duplicateEvent("${event.event_id}")' class="text-green-600 hover:text-green-900">複製</button>
                        <button onclick='deleteEvent("${event.event_id}", "${escapeHtml(event.event_name)}")' class="text-red-600 hover:text-red-900">削除</button>
                    </td>
                `;
        tbody.appendChild(tr);
      });
    }

    // モーダル開く
    function openModal(mode, data = null) {
      currentMode = mode;
      const modal = document.getElementById('modal');
      const form = document.getElementById('eventForm');
      const title = document.getElementById('modalTitle');

      form.reset();

      // すべてのチェックボックスをクリア
      document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);

      // コンテナを非表示
      document.getElementById('daysOfWeekContainer').classList.add('hidden');
      document.getElementById('dayOfMonthContainer').classList.add('hidden');
      document.getElementById('targetProductsContainer').classList.add('hidden');
      document.getElementById('targetActionsContainer').classList.add('hidden');
      document.getElementById('noticeContainer').classList.add('hidden');

      if (mode === 'create') {
        title.textContent = 'イベント登録';
      } else {
        title.textContent = 'イベント編集';
        document.getElementById('eventId').value = data.event_id;
        document.getElementById('eventName').value = data.event_name;
        document.getElementById('repeatType').value = data.repeat_type;
        // datetime形式をdatetime-local形式に変換（YYYY-MM-DDTHH:MM）
        document.getElementById('startDate').value = data.start_date ? data.start_date.substring(0, 16) : '';
        document.getElementById('endDate').value = data.end_date ? data.end_date.substring(0, 16) : '';
        document.getElementById('targetType').value = data.target_type;
        document.getElementById('multiplier').value = data.multiplier;
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

        // 対象設定
        if (data.target_type === '特定商品') {
          document.getElementById('targetProductsContainer').classList.remove('hidden');
          if (data.target_ids) {
            const ids = data.target_ids.split(',');
            ids.forEach(id => {
              const checkbox = document.querySelector(`input[name="target_products[]"][value="${id}"]`);
              if (checkbox) checkbox.checked = true;
            });
          }
        } else if (data.target_type === '特定アクション') {
          document.getElementById('targetActionsContainer').classList.remove('hidden');
          if (data.target_ids) {
            const ids = data.target_ids.split(',');
            ids.forEach(id => {
              const checkbox = document.querySelector(`input[name="target_actions[]"][value="${id}"]`);
              if (checkbox) checkbox.checked = true;
            });
          }
        }

        // 告知設定
        if (data.publish_notice) {
          document.getElementById('publishNotice').checked = true;
          document.getElementById('noticeContainer').classList.remove('hidden');
          document.getElementById('noticePublishAt').value = data.notice_publish_at || '';
          document.getElementById('noticeTitle').value = data.notice_title || '';
          document.getElementById('noticeBody').value = data.notice_body || '';
        }
      }

      modal.classList.remove('hidden');
    }

    // モーダル閉じる
    function closeModal() {
      document.getElementById('modal').classList.add('hidden');
    }

    // 期間重複チェック
    async function checkEventOverlap(startDate, endDate, currentEventId) {
      try {
        const response = await fetch('/api/events.php');
        const result = await response.json();

        if (!result.success) {
          return null;
        }

        const newStart = new Date(startDate);
        const newEnd = new Date(endDate);
        const overlappingEvents = [];

        for (const event of result.data) {
          // 編集中のイベント自身は除外
          if (currentEventId && event.event_id === currentEventId) {
            continue;
          }

          // 無効なイベントはスキップ
          if (event.status !== '有効') {
            continue;
          }

          const eventStart = new Date(event.start_date);
          const eventEnd = new Date(event.end_date);

          // 期間が重複しているかチェック
          if ((newStart <= eventEnd) && (newEnd >= eventStart)) {
            overlappingEvents.push(event);
          }
        }

        if (overlappingEvents.length > 0) {
          const formatDate = (datetime) => datetime ? datetime.substring(0, 10) : '';
          const names = overlappingEvents.map(e => `「${e.event_name}」(${formatDate(e.start_date)}〜${formatDate(e.end_date)})`).join('\n');
          return `⚠️ 以下のイベントと期間が重複しています：\n\n${names}`;
        }

        return null;
      } catch (error) {
        console.error('Overlap check error:', error);
        return null;
      }
    }

    // フォーム送信
    document.getElementById('eventForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);
      const repeatType = formData.get('repeat_type');
      const targetType = formData.get('target_type');

      let daysOfWeek = null;
      let dayOfMonth = null;
      let targetIds = null;

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

      // 対象設定
      if (targetType === '特定商品') {
        const selectedProducts = formData.getAll('target_products[]');
        if (selectedProducts.length === 0) {
          alert('対象商品を選択してください。');
          return;
        }
        targetIds = selectedProducts.join(',');
      } else if (targetType === '特定アクション') {
        const selectedActions = formData.getAll('target_actions[]');
        if (selectedActions.length === 0) {
          alert('対象アクションを選択してください。');
          return;
        }
        targetIds = selectedActions.join(',');
      }

      // datetime-local形式の値をdatetime形式に変換（YYYY-MM-DD HH:MM:SS）
      const startDateTime = formData.get('start_date');
      const endDateTime = formData.get('end_date');
      const startDate = startDateTime ? startDateTime.replace('T', ' ') + ':00' : '';
      const endDate = endDateTime ? endDateTime.replace('T', ' ') + ':00' : '';

      // 期間重複チェック
      const currentEventId = currentMode === 'edit' ? document.getElementById('eventId').value : null;
      const overlapWarning = await checkEventOverlap(startDate, endDate, currentEventId);
      if (overlapWarning) {
        if (!confirm(overlapWarning + '\n\nこのまま登録しますか？')) {
          return;
        }
      }

      const data = {
        event_name: formData.get('event_name'),
        repeat_type: repeatType,
        start_date: startDate,
        end_date: endDate,
        days_of_week: daysOfWeek,
        day_of_month: dayOfMonth,
        target_type: targetType,
        target_ids: targetIds,
        multiplier: formData.get('multiplier'),
        status: formData.get('status'),
        description: formData.get('description'),
        publish_notice: formData.get('publish_notice') ? true : false,
        notice_publish_at: formData.get('notice_publish_at'),
        notice_title: formData.get('notice_title'),
        notice_body: formData.get('notice_body')
      };

      try {
        let url = '/api/events.php';
        let method = 'POST';

        if (currentMode === 'edit') {
          const eventId = document.getElementById('eventId').value;
          url = `/api/events.php?id=${eventId}`;
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
          loadEvents();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    });

    // 削除
    async function deleteEvent(eventId, name) {
      if (!confirm(`「${name}」を削除しますか？\n関連する掲示板投稿も削除されます。`)) {
        return;
      }

      try {
        const response = await fetch(`/api/events.php?id=${eventId}`, {
          method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadEvents();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // 複製
    async function duplicateEvent(eventId) {
      if (!confirm('このイベントを複製しますか？')) {
        return;
      }

      try {
        const response = await fetch('/api/events/duplicate.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            event_id: eventId
          })
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadEvents();
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
      loadEvents(true);
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