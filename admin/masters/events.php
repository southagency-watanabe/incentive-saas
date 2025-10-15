<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/database.php';

// 管理者権限チェック
requireAdmin();

// 商品・アクション一覧取得（対象選択用）
$pdo = getDB();

$stmt = $pdo->prepare("SELECT product_id, product_name, large_category, medium_category, small_category FROM products WHERE tenant_id = :tenant_id AND status = '有効' ORDER BY large_category, medium_category, small_category, product_id ASC");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$products = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT action_id, action_name, category FROM actions WHERE tenant_id = :tenant_id AND status = '有効' ORDER BY action_id ASC");
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="event_id" data-type="string">
              イベントID <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="event_name" data-type="string">
              イベント名 <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="start_date" data-type="string">
              期間 <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="repeat_type" data-type="string">
              繰り返し <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="target_type" data-type="string">
              対象 <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="multiplier" data-type="number">
              倍率 <span class="sort-icon">⇅</span>
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

          <!-- 承認要否 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">承認要否 <span class="text-red-500">*</span></label>
            <select id="approval_required" name="approval_required" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="不要">不要</option>
              <option value="必要">必要</option>
            </select>
          </div>
        </div>

        <!-- 対象選択（特定商品の場合） -->
        <div id="targetProductsContainer" class="hidden">
          <!-- 商品カテゴリ選択 -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">商品カテゴリ別倍率</label>
            <div class="border border-gray-300 rounded-md p-3">
              <div class="space-y-2" id="productCategoryMultiplierContainer">
                <!-- カテゴリは動的に読み込まれます -->
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">※ カテゴリ倍率を設定すると、そのカテゴリに属する全商品に適用されます</p>
          </div>

          <!-- 個別商品選択 -->
          <label class="block text-sm font-medium text-gray-700 mb-2">対象商品（個別指定）</label>
          <div class="border border-gray-300 rounded-md p-3 max-h-60 overflow-y-auto">
            <div class="space-y-2">
              <?php foreach ($products as $product): ?>
                <div class="flex items-center space-x-2">
                  <input type="checkbox" name="target_products[]" value="<?= htmlspecialchars($product['product_id']) ?>" class="rounded product-checkbox" data-large-category="<?= htmlspecialchars($product['large_category'] ?? '') ?>" data-medium-category="<?= htmlspecialchars($product['medium_category'] ?? '') ?>" data-small-category="<?= htmlspecialchars($product['small_category'] ?? '') ?>" onchange="toggleMultiplierInput(this)">
                  <span class="text-sm flex-1"><?= htmlspecialchars($product['product_name']) ?>
                    <?php
                    $categoryParts = array_filter([
                      $product['large_category'] ?? null,
                      $product['medium_category'] ?? null,
                      $product['small_category'] ?? null
                    ]);
                    if (!empty($categoryParts)):
                    ?>
                      <span class="text-xs text-gray-500 ml-1">(<?= htmlspecialchars(implode(' > ', $categoryParts)) ?>)</span>
                    <?php endif; ?>
                  </span>
                  <input type="number" step="0.01" min="0.01" placeholder="倍率" class="w-20 px-2 py-1 text-sm border border-gray-300 rounded product-multiplier" data-product-id="<?= htmlspecialchars($product['product_id']) ?>" onkeydown="if(event.key==='Enter')event.preventDefault()" disabled>
                  <span class="text-xs text-gray-500">倍</span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-1">※ 個別商品の倍率は、カテゴリ倍率より優先されます</p>
        </div>

        <!-- 対象選択（特定アクションの場合） -->
        <div id="targetActionsContainer" class="hidden">
          <!-- アクションカテゴリ選択 -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">アクションカテゴリ別倍率</label>
            <div class="border border-gray-300 rounded-md p-3">
              <div class="space-y-2" id="categoryMultiplierContainer">
                <!-- カテゴリは動的に読み込まれます -->
              </div>
            </div>
            <p class="text-xs text-gray-500 mt-1">※ カテゴリ倍率を設定すると、そのカテゴリに属する全アクションに適用されます</p>
          </div>

          <!-- 個別アクション選択 -->
          <label class="block text-sm font-medium text-gray-700 mb-2">対象アクション（個別指定）</label>
          <div class="border border-gray-300 rounded-md p-3 max-h-60 overflow-y-auto">
            <div class="space-y-2">
              <?php foreach ($actions as $action): ?>
                <div class="flex items-center space-x-2">
                  <input type="checkbox" name="target_actions[]" value="<?= htmlspecialchars($action['action_id']) ?>" class="rounded action-checkbox" data-category="<?= htmlspecialchars($action['category'] ?? '') ?>" onchange="toggleActionMultiplierInput(this)">
                  <span class="text-sm flex-1"><?= htmlspecialchars($action['action_name']) ?><?php if (!empty($action['category'])): ?><span class="text-xs text-gray-500 ml-1">(<?= htmlspecialchars($action['category']) ?>)</span><?php endif; ?></span>
                  <input type="number" step="0.01" min="0.01" placeholder="倍率" class="w-20 px-2 py-1 text-sm border border-gray-300 rounded action-multiplier" data-action-id="<?= htmlspecialchars($action['action_id']) ?>" onkeydown="if(event.key==='Enter')event.preventDefault()" disabled>
                  <span class="text-xs text-gray-500">倍</span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-1">※ 個別アクションの倍率は、カテゴリ倍率より優先されます</p>
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

    // グローバル変数
    let allEvents = [];
    let allProductCategories = []; // 商品カテゴリ一覧
    let allActionCategories = []; // アクションカテゴリ一覧
    let sortConfig = {
      column: null,
      direction: 'asc'
    };

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadEvents();
      loadProductCategories();
      loadActionCategories();
      setupRepeatTypeToggle();
      setupTargetTypeToggle();
      setupSortableHeaders();
      setupPublishNoticeToggle();
      setupMultiplierSync();
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

    // 商品別倍率入力欄の有効/無効切り替え
    function toggleMultiplierInput(checkbox) {
      const productId = checkbox.value;
      const multiplierInput = document.querySelector(`.product-multiplier[data-product-id="${productId}"]`);
      
      if (checkbox.checked) {
        multiplierInput.disabled = false;
        // デフォルト倍率を設定（イベント倍率の値）
        if (!multiplierInput.value) {
          const eventMultiplier = document.getElementById('multiplier').value;
          multiplierInput.value = eventMultiplier || '1.00';
        }
      } else {
        multiplierInput.disabled = true;
        multiplierInput.value = '';
      }
    }

    // イベント倍率変更時に商品別倍率を更新
    function setupMultiplierSync() {
      const eventMultiplierInput = document.getElementById('multiplier');
      
      eventMultiplierInput.addEventListener('change', (e) => {
        const newMultiplier = e.target.value;
        // チェック済みで未入力の商品倍率を更新
        document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
          const productId = checkbox.value;
          const multiplierInput = document.querySelector(`.product-multiplier[data-product-id="${productId}"]`);
          if (!multiplierInput.disabled && !multiplierInput.value) {
            multiplierInput.value = newMultiplier;
          }
        });
        // チェック済みで未入力の商品カテゴリ倍率を更新（階層構造対応）
        document.querySelectorAll('.product-category-checkbox:checked').forEach(checkbox => {
          const level = checkbox.dataset.level;
          const large = checkbox.dataset.largeCategory;
          const medium = checkbox.dataset.mediumCategory;
          const small = checkbox.dataset.smallCategory;
          
          let selector = '';
          if (level === 'large') {
            selector = `.product-category-multiplier[data-level="large"][data-large-category="${large}"]`;
          } else if (level === 'medium') {
            selector = `.product-category-multiplier[data-level="medium"][data-large-category="${large}"][data-medium-category="${medium}"]`;
          } else if (level === 'small') {
            selector = `.product-category-multiplier[data-level="small"][data-large-category="${large}"][data-medium-category="${medium}"][data-small-category="${small}"]`;
          }
          
          const multiplierInput = document.querySelector(selector);
          if (multiplierInput && !multiplierInput.disabled && !multiplierInput.value) {
            multiplierInput.value = newMultiplier;
          }
        });
        // チェック済みで未入力のアクションカテゴリ倍率を更新
        document.querySelectorAll('.action-category-checkbox:checked').forEach(checkbox => {
          const category = checkbox.dataset.category;
          const multiplierInput = document.querySelector(`.action-category-multiplier[data-category="${category}"]`);
          if (!multiplierInput.disabled && !multiplierInput.value) {
            multiplierInput.value = newMultiplier;
          }
        });
        // チェック済みで未入力のアクション倍率を更新
        document.querySelectorAll('.action-checkbox:checked').forEach(checkbox => {
          const actionId = checkbox.value;
          const multiplierInput = document.querySelector(`.action-multiplier[data-action-id="${actionId}"]`);
          if (!multiplierInput.disabled && !multiplierInput.value) {
            multiplierInput.value = newMultiplier;
          }
        });
      });
    }

    // アクション別倍率入力欄の有効/無効切り替え
    function toggleActionMultiplierInput(checkbox) {
      const actionId = checkbox.value;
      const multiplierInput = document.querySelector(`.action-multiplier[data-action-id="${actionId}"]`);
      
      if (checkbox.checked) {
        multiplierInput.disabled = false;
        // デフォルト倍率を設定（イベント倍率の値）
        if (!multiplierInput.value) {
          const eventMultiplier = document.getElementById('multiplier').value;
          multiplierInput.value = eventMultiplier || '1.00';
        }
      } else {
        multiplierInput.disabled = true;
        multiplierInput.value = '';
      }
    }

    // イベント一覧取得
    // 商品カテゴリ一覧を読み込み
    async function loadProductCategories() {
      try {
        const response = await fetch('/api/products.php');
        const result = await response.json();
        
        if (result.success) {
          // カテゴリを階層構造で構築
          const categoryTree = {};
          result.data.forEach(product => {
            const large = product.large_category || '未分類';
            const medium = product.medium_category || '未分類';
            const small = product.small_category || '未分類';
            
            if (!categoryTree[large]) {
              categoryTree[large] = {};
            }
            if (!categoryTree[large][medium]) {
              categoryTree[large][medium] = new Set();
            }
            categoryTree[large][medium].add(small);
          });
          
          // Setを配列に変換してソート
          Object.keys(categoryTree).forEach(large => {
            Object.keys(categoryTree[large]).forEach(medium => {
              categoryTree[large][medium] = Array.from(categoryTree[large][medium]).sort();
            });
          });
          
          allProductCategories = categoryTree;
          renderProductCategoryMultipliers();
        }
      } catch (error) {
        console.error('商品カテゴリの読み込みエラー:', error);
      }
    }

    // アクションカテゴリ一覧を読み込み
    async function loadActionCategories() {
      try {
        const response = await fetch('/api/actions.php');
        const result = await response.json();
        
        if (result.success) {
          // カテゴリのユニークなリストを取得
          const categorySet = new Set();
          result.data.forEach(action => {
            if (action.category) {
              categorySet.add(action.category);
            }
          });
          allActionCategories = Array.from(categorySet).sort();
          renderActionCategoryMultipliers();
        }
      } catch (error) {
        console.error('アクションカテゴリの読み込みエラー:', error);
      }
    }

    // 商品カテゴリ別倍率の入力欄を表示（階層構造）
    function renderProductCategoryMultipliers() {
      const container = document.getElementById('productCategoryMultiplierContainer');
      if (!container) return;
      
      if (Object.keys(allProductCategories).length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-500">カテゴリが設定された商品がありません</p>';
        return;
      }
      
      let html = '';
      
      // 階層構造で表示
      Object.keys(allProductCategories).sort().forEach(large => {
        html += `
          <div class="border-l-2 border-blue-300 pl-2 mb-2">
            <!-- 大カテゴリ -->
            <div class="flex items-center space-x-2 mb-1">
              <input type="checkbox" class="rounded product-category-checkbox" data-level="large" data-large-category="${large}" onchange="toggleProductCategoryMultiplierInput(this)">
              <span class="text-sm font-semibold text-blue-700 flex-1">${large}</span>
              <input type="number" step="0.01" min="0.01" placeholder="倍率" class="w-20 px-2 py-1 text-xs border border-gray-300 rounded product-category-multiplier" data-level="large" data-large-category="${large}" oninput="updateProductCategoryMultipliers(this)" onkeydown="handleProductCategoryMultiplierKeydown(event)" disabled>
              <span class="text-xs text-gray-500">倍</span>
            </div>
        `;
        
        // 中カテゴリ
        Object.keys(allProductCategories[large]).sort().forEach(medium => {
          html += `
            <div class="border-l-2 border-green-300 pl-3 ml-2 mb-1">
              <div class="flex items-center space-x-2 mb-1">
                <input type="checkbox" class="rounded product-category-checkbox" data-level="medium" data-large-category="${large}" data-medium-category="${medium}" onchange="toggleProductCategoryMultiplierInput(this)">
                <span class="text-sm font-medium text-green-700 flex-1">${medium}</span>
                <input type="number" step="0.01" min="0.01" placeholder="倍率" class="w-20 px-2 py-1 text-xs border border-gray-300 rounded product-category-multiplier" data-level="medium" data-large-category="${large}" data-medium-category="${medium}" oninput="updateProductCategoryMultipliers(this)" onkeydown="handleProductCategoryMultiplierKeydown(event)" disabled>
                <span class="text-xs text-gray-500">倍</span>
              </div>
          `;
          
          // 小カテゴリ
          allProductCategories[large][medium].forEach(small => {
            html += `
              <div class="border-l-2 border-gray-300 pl-3 ml-2 mb-1">
                <div class="flex items-center space-x-2">
                  <input type="checkbox" class="rounded product-category-checkbox" data-level="small" data-large-category="${large}" data-medium-category="${medium}" data-small-category="${small}" onchange="toggleProductCategoryMultiplierInput(this)">
                  <span class="text-sm text-gray-600 flex-1">${small}</span>
                  <input type="number" step="0.01" min="0.01" placeholder="倍率" class="w-20 px-2 py-1 text-xs border border-gray-300 rounded product-category-multiplier" data-level="small" data-large-category="${large}" data-medium-category="${medium}" data-small-category="${small}" oninput="updateProductCategoryMultipliers(this)" onkeydown="handleProductCategoryMultiplierKeydown(event)" disabled>
                  <span class="text-xs text-gray-500">倍</span>
                </div>
              </div>
            `;
          });
          
          html += `</div>`;
        });
        
        html += `</div>`;
      });
      
      container.innerHTML = html;
    }

    // アクションカテゴリ別倍率の入力欄を表示
    function renderActionCategoryMultipliers() {
      const container = document.getElementById('categoryMultiplierContainer');
      if (!container) return;
      
      if (allActionCategories.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-500">カテゴリが設定されたアクションがありません</p>';
        return;
      }
      
      container.innerHTML = allActionCategories.map(category => `
        <div class="flex items-center space-x-2">
          <input type="checkbox" class="rounded action-category-checkbox" data-category="${category}" onchange="toggleActionCategoryMultiplierInput(this)">
          <span class="text-sm flex-1">${category}</span>
          <input type="number" step="0.01" min="0.01" placeholder="倍率" class="w-20 px-2 py-1 text-sm border border-gray-300 rounded action-category-multiplier" data-category="${category}" oninput="updateActionCategoryMultipliers(this)" onkeydown="handleActionCategoryMultiplierKeydown(event)" disabled>
          <span class="text-xs text-gray-500">倍</span>
        </div>
      `).join('');
    }

    // 商品カテゴリ倍率入力欄でEnterキーを押したときの処理
    function handleProductCategoryMultiplierKeydown(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        updateProductCategoryMultipliers(event.target);
        event.target.blur();
      }
    }

    // 商品カテゴリ倍率が変更されたら、子カテゴリと商品倍率にも反映（階層構造対応）
    function updateProductCategoryMultipliers(input) {
      const level = input.dataset.level;
      const large = input.dataset.largeCategory;
      const medium = input.dataset.mediumCategory;
      const small = input.dataset.smallCategory;
      const multiplierValue = input.value;
      
      if (!multiplierValue) return;
      
      // 階層的に子カテゴリの倍率も更新
      if (level === 'large') {
        // 大カテゴリの倍率変更 → 配下の全ての中・小カテゴリの倍率を更新
        document.querySelectorAll(`.product-category-multiplier[data-level="medium"][data-large-category="${large}"]`).forEach(mediumInput => {
          if (!mediumInput.disabled) {
            mediumInput.value = multiplierValue;
          }
        });
        document.querySelectorAll(`.product-category-multiplier[data-level="small"][data-large-category="${large}"]`).forEach(smallInput => {
          if (!smallInput.disabled) {
            smallInput.value = multiplierValue;
          }
        });
      } else if (level === 'medium') {
        // 中カテゴリの倍率変更 → 配下の全ての小カテゴリの倍率を更新
        document.querySelectorAll(`.product-category-multiplier[data-level="small"][data-large-category="${large}"][data-medium-category="${medium}"]`).forEach(smallInput => {
          if (!smallInput.disabled) {
            smallInput.value = multiplierValue;
          }
        });
      }
      
      // このカテゴリに属する商品の倍率も更新
      let productSelector = '';
      if (level === 'large') {
        productSelector = `.product-checkbox[data-large-category="${large}"]`;
      } else if (level === 'medium') {
        productSelector = `.product-checkbox[data-large-category="${large}"][data-medium-category="${medium}"]`;
      } else if (level === 'small') {
        productSelector = `.product-checkbox[data-large-category="${large}"][data-medium-category="${medium}"][data-small-category="${small}"]`;
      }
      
      const productCheckboxes = document.querySelectorAll(productSelector);
      productCheckboxes.forEach(productCheckbox => {
        if (productCheckbox.checked) {
          const productId = productCheckbox.value;
          const productMultiplierInput = document.querySelector(`.product-multiplier[data-product-id="${productId}"]`);
          if (productMultiplierInput && !productMultiplierInput.disabled) {
            productMultiplierInput.value = multiplierValue;
          }
        }
      });
    }

    // 商品カテゴリ倍率入力欄の有効/無効切り替え（階層構造対応）
    function toggleProductCategoryMultiplierInput(checkbox) {
      const level = checkbox.dataset.level;
      const large = checkbox.dataset.largeCategory;
      const medium = checkbox.dataset.mediumCategory;
      const small = checkbox.dataset.smallCategory;
      
      // 対応する倍率入力欄を取得
      let selector = '';
      if (level === 'large') {
        selector = `.product-category-multiplier[data-level="large"][data-large-category="${large}"]`;
      } else if (level === 'medium') {
        selector = `.product-category-multiplier[data-level="medium"][data-large-category="${large}"][data-medium-category="${medium}"]`;
      } else if (level === 'small') {
        selector = `.product-category-multiplier[data-level="small"][data-large-category="${large}"][data-medium-category="${medium}"][data-small-category="${small}"]`;
      }
      const multiplierInput = document.querySelector(selector);
      
      if (checkbox.checked) {
        // 倍率入力欄を有効化
        if (multiplierInput) {
          multiplierInput.disabled = false;
          multiplierInput.focus();
          
          const defaultMultiplier = document.getElementById('multiplier').value;
          if (defaultMultiplier && !multiplierInput.value) {
            multiplierInput.value = defaultMultiplier;
          }
        }
        
        // 階層的に子カテゴリもチェック
        if (level === 'large') {
          // 大カテゴリがチェックされた → 配下の全ての中・小カテゴリをチェック
          document.querySelectorAll(`.product-category-checkbox[data-level="medium"][data-large-category="${large}"]`).forEach(mediumCheckbox => {
            if (!mediumCheckbox.checked) {
              mediumCheckbox.checked = true;
              toggleProductCategoryMultiplierInput(mediumCheckbox);
            }
          });
        } else if (level === 'medium') {
          // 中カテゴリがチェックされた → 配下の全ての小カテゴリをチェック
          document.querySelectorAll(`.product-category-checkbox[data-level="small"][data-large-category="${large}"][data-medium-category="${medium}"]`).forEach(smallCheckbox => {
            if (!smallCheckbox.checked) {
              smallCheckbox.checked = true;
              toggleProductCategoryMultiplierInput(smallCheckbox);
            }
          });
        }
        
        // このカテゴリに属する商品を自動チェック
        let productSelector = '';
        if (level === 'large') {
          productSelector = `.product-checkbox[data-large-category="${large}"]`;
        } else if (level === 'medium') {
          productSelector = `.product-checkbox[data-large-category="${large}"][data-medium-category="${medium}"]`;
        } else if (level === 'small') {
          productSelector = `.product-checkbox[data-large-category="${large}"][data-medium-category="${medium}"][data-small-category="${small}"]`;
        }
        
        const productCheckboxes = document.querySelectorAll(productSelector);
        productCheckboxes.forEach(productCheckbox => {
          if (!productCheckbox.checked) {
            productCheckbox.checked = true;
            toggleMultiplierInput(productCheckbox);
          }
        });
        
        // カテゴリ倍率を個別商品にも反映
        if (multiplierInput && multiplierInput.value) {
          updateProductCategoryMultipliers(multiplierInput);
        }
      } else {
        // チェックを外す
        if (multiplierInput) {
          multiplierInput.disabled = true;
          multiplierInput.value = '';
        }
        
        // 階層的に子カテゴリのチェックも外す
        if (level === 'large') {
          // 大カテゴリのチェックを外す → 配下の全ての中・小カテゴリのチェックを外す
          document.querySelectorAll(`.product-category-checkbox[data-level="medium"][data-large-category="${large}"]`).forEach(mediumCheckbox => {
            if (mediumCheckbox.checked) {
              mediumCheckbox.checked = false;
              toggleProductCategoryMultiplierInput(mediumCheckbox);
            }
          });
        } else if (level === 'medium') {
          // 中カテゴリのチェックを外す → 配下の全ての小カテゴリのチェックを外す
          document.querySelectorAll(`.product-category-checkbox[data-level="small"][data-large-category="${large}"][data-medium-category="${medium}"]`).forEach(smallCheckbox => {
            if (smallCheckbox.checked) {
              smallCheckbox.checked = false;
              toggleProductCategoryMultiplierInput(smallCheckbox);
            }
          });
        }
        
        // このカテゴリに属する商品のチェックを外す
        let productSelector = '';
        if (level === 'large') {
          productSelector = `.product-checkbox[data-large-category="${large}"]`;
        } else if (level === 'medium') {
          productSelector = `.product-checkbox[data-large-category="${large}"][data-medium-category="${medium}"]`;
        } else if (level === 'small') {
          productSelector = `.product-checkbox[data-large-category="${large}"][data-medium-category="${medium}"][data-small-category="${small}"]`;
        }
        
        const productCheckboxes = document.querySelectorAll(productSelector);
        productCheckboxes.forEach(productCheckbox => {
          if (productCheckbox.checked) {
            productCheckbox.checked = false;
            toggleMultiplierInput(productCheckbox);
          }
        });
      }
    }

    // アクションカテゴリ倍率入力欄でEnterキーを押したときの処理
    function handleActionCategoryMultiplierKeydown(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        updateActionCategoryMultipliers(event.target);
        event.target.blur();
      }
    }

    // アクションカテゴリ倍率が変更されたら、そのカテゴリのアクション倍率にも反映
    function updateActionCategoryMultipliers(input) {
      const category = input.dataset.category;
      const multiplierValue = input.value;
      
      if (!multiplierValue) return;
      
      const actionCheckboxes = document.querySelectorAll(`.action-checkbox[data-category="${category}"]`);
      actionCheckboxes.forEach(actionCheckbox => {
        if (actionCheckbox.checked) {
          const actionId = actionCheckbox.value;
          const actionMultiplierInput = document.querySelector(`.action-multiplier[data-action-id="${actionId}"]`);
          if (actionMultiplierInput && !actionMultiplierInput.disabled) {
            actionMultiplierInput.value = multiplierValue;
          }
        }
      });
    }

    // アクションカテゴリ倍率入力欄の有効/無効切り替え
    function toggleActionCategoryMultiplierInput(checkbox) {
      const category = checkbox.dataset.category;
      const multiplierInput = document.querySelector(`.action-category-multiplier[data-category="${category}"]`);
      
      if (checkbox.checked) {
        multiplierInput.disabled = false;
        multiplierInput.focus();
        
        const defaultMultiplier = document.getElementById('multiplier').value;
        if (defaultMultiplier && !multiplierInput.value) {
          multiplierInput.value = defaultMultiplier;
        }
        
        // このカテゴリに属するアクションを自動チェック
        const actionCheckboxes = document.querySelectorAll(`.action-checkbox[data-category="${category}"]`);
        actionCheckboxes.forEach(actionCheckbox => {
          if (!actionCheckbox.checked) {
            actionCheckbox.checked = true;
            toggleActionMultiplierInput(actionCheckbox);
          }
        });
        
        // カテゴリ倍率を個別アクションにも反映
        if (multiplierInput.value) {
          updateActionCategoryMultipliers(multiplierInput);
        }
      } else {
        multiplierInput.disabled = true;
        multiplierInput.value = '';
        
        // このカテゴリに属するアクションのチェックを外す
        const actionCheckboxes = document.querySelectorAll(`.action-checkbox[data-category="${category}"]`);
        actionCheckboxes.forEach(actionCheckbox => {
          if (actionCheckbox.checked) {
            actionCheckbox.checked = false;
            toggleActionMultiplierInput(actionCheckbox);
          }
        });
      }
    }

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
          allEvents = result.data;
          renderTable(allEvents);
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
        tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      events.forEach(event => {
        // 日付のみフォーマット（YYYY-MM-DD）
        const formatDate = (datetime) => {
          if (!datetime) return '';
          return datetime.substring(0, 10);
        };
        const period = `${formatDate(event.start_date)} 〜 ${formatDate(event.end_date)}`;

        // 倍率表示（商品別/カテゴリ別/アクション別倍率がある場合は追記）
        let multiplierDisplay = `${parseFloat(event.multiplier).toFixed(1)}倍`;
        if (event.product_multipliers && Object.keys(event.product_multipliers).length > 0) {
          multiplierDisplay += ' <span class="text-xs text-blue-600">(商品別設定あり)</span>';
        }
        if (event.category_multipliers && Object.keys(event.category_multipliers).length > 0) {
          multiplierDisplay += ' <span class="text-xs text-purple-600">(カテゴリ別設定あり)</span>';
        }
        if (event.action_multipliers && Object.keys(event.action_multipliers).length > 0) {
          multiplierDisplay += ' <span class="text-xs text-green-600">(アクション別設定あり)</span>';
        }

        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(event.event_id)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(event.event_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${period}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(event.repeat_type)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(event.target_type)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${multiplierDisplay}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${event.status === '有効' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${escapeHtml(event.status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${event.approval_required === '必要' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'}">
                            ${escapeHtml(event.approval_required)}
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

      const sortedEvents = [...allEvents].sort((a, b) => {
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
      renderTable(sortedEvents);
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
        // 新規作成時は現在日付の0時をデフォルト値に設定
        const today = new Date();
        const dateStr = today.getFullYear() + '-' +
                        String(today.getMonth() + 1).padStart(2, '0') + '-' +
                        String(today.getDate()).padStart(2, '0') + 'T00:00';
        document.getElementById('startDate').value = dateStr;
        document.getElementById('endDate').value = dateStr;
        document.getElementById('noticePublishAt').value = dateStr;
      } else {
        title.textContent = 'イベント編集';
        document.getElementById('eventId').value = data.event_id;
        document.getElementById('eventName').value = data.event_name;
        document.getElementById('repeatType').value = data.repeat_type;
        // datetime形式をdatetime-local形式に変換（YYYY-MM-DD HH:MM:SS → YYYY-MM-DDTHH:MM）
        document.getElementById('startDate').value = data.start_date ? data.start_date.substring(0, 16).replace(' ', 'T') : '';
        document.getElementById('endDate').value = data.end_date ? data.end_date.substring(0, 16).replace(' ', 'T') : '';
        document.getElementById('targetType').value = data.target_type;
        document.getElementById('multiplier').value = data.multiplier;
        document.getElementById('status').value = data.status;
        document.getElementById('approval_required').value = data.approval_required || '不要';
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
              if (checkbox) {
                checkbox.checked = true;
                // 商品別倍率を設定
                const multiplierInput = document.querySelector(`.product-multiplier[data-product-id="${id}"]`);
                if (multiplierInput) {
                  multiplierInput.disabled = false;
                  // product_multipliersから倍率を取得、なければデフォルト倍率
                  const customMultiplier = data.product_multipliers && data.product_multipliers[id];
                  multiplierInput.value = customMultiplier || data.multiplier || '1.00';
                }
              }
            });
          }
        } else if (data.target_type === '特定アクション') {
          document.getElementById('targetActionsContainer').classList.remove('hidden');
          
          // カテゴリ別倍率を設定
          if (data.category_multipliers) {
            Object.keys(data.category_multipliers).forEach(category => {
              const checkbox = document.querySelector(`.category-checkbox[data-category="${category}"]`);
              if (checkbox) {
                checkbox.checked = true;
                const multiplierInput = document.querySelector(`.category-multiplier[data-category="${category}"]`);
                if (multiplierInput) {
                  multiplierInput.disabled = false;
                  multiplierInput.value = data.category_multipliers[category];
                }
              }
            });
          }
          
          // 個別アクション別倍率を設定
          if (data.target_ids) {
            const ids = data.target_ids.split(',');
            ids.forEach(id => {
              const checkbox = document.querySelector(`input[name="target_actions[]"][value="${id}"]`);
              if (checkbox) {
                checkbox.checked = true;
                // アクション別倍率を設定
                const multiplierInput = document.querySelector(`.action-multiplier[data-action-id="${id}"]`);
                if (multiplierInput) {
                  multiplierInput.disabled = false;
                  // action_multipliersから倍率を取得、なければデフォルト倍率
                  const customMultiplier = data.action_multipliers && data.action_multipliers[id];
                  multiplierInput.value = customMultiplier || data.multiplier || '1.00';
                }
              }
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
      let productMultipliers = {};
      let productCategoryMultipliers = {};
      let actionMultipliers = {};
      let actionCategoryMultipliers = {};
      if (targetType === '特定商品') {
        const selectedProducts = formData.getAll('target_products[]');
        const selectedProductCategoryCheckboxes = document.querySelectorAll('.product-category-checkbox:checked');
        
        if (selectedProducts.length === 0 && selectedProductCategoryCheckboxes.length === 0) {
          alert('カテゴリまたは対象商品を選択してください。');
          return;
        }
        targetIds = selectedProducts.join(',');

        // 商品別倍率を収集
        selectedProducts.forEach(productId => {
          const multiplierInput = document.querySelector(`.product-multiplier[data-product-id="${productId}"]`);
          if (multiplierInput && multiplierInput.value) {
            productMultipliers[productId] = parseFloat(multiplierInput.value);
          }
        });

        // 商品カテゴリ別倍率を収集（階層構造対応）
        selectedProductCategoryCheckboxes.forEach(checkbox => {
          const level = checkbox.dataset.level;
          const large = checkbox.dataset.largeCategory;
          const medium = checkbox.dataset.mediumCategory;
          const small = checkbox.dataset.smallCategory;
          
          // カテゴリキーを構築（レベルに応じて）
          let categoryKey = '';
          if (level === 'large') {
            categoryKey = large;
          } else if (level === 'medium') {
            categoryKey = `${large} > ${medium}`;
          } else if (level === 'small') {
            categoryKey = `${large} > ${medium} > ${small}`;
          }
          
          // 対応する倍率入力欄を取得
          let selector = '';
          if (level === 'large') {
            selector = `.product-category-multiplier[data-level="large"][data-large-category="${large}"]`;
          } else if (level === 'medium') {
            selector = `.product-category-multiplier[data-level="medium"][data-large-category="${large}"][data-medium-category="${medium}"]`;
          } else if (level === 'small') {
            selector = `.product-category-multiplier[data-level="small"][data-large-category="${large}"][data-medium-category="${medium}"][data-small-category="${small}"]`;
          }
          
          const multiplierInput = document.querySelector(selector);
          if (multiplierInput && multiplierInput.value) {
            productCategoryMultipliers[categoryKey] = parseFloat(multiplierInput.value);
          }
        });
      } else if (targetType === '特定アクション') {
        const selectedActions = formData.getAll('target_actions[]');
        // カテゴリまたは個別アクションのいずれかが選択されている必要がある
        const selectedActionCategories = Array.from(document.querySelectorAll('.action-category-checkbox:checked')).map(cb => cb.dataset.category);
        
        if (selectedActions.length === 0 && selectedActionCategories.length === 0) {
          alert('カテゴリまたは対象アクションを選択してください。');
          return;
        }
        targetIds = selectedActions.join(',');

        // アクション別倍率を収集
        selectedActions.forEach(actionId => {
          const multiplierInput = document.querySelector(`.action-multiplier[data-action-id="${actionId}"]`);
          if (multiplierInput && multiplierInput.value) {
            actionMultipliers[actionId] = parseFloat(multiplierInput.value);
          }
        });

        // アクションカテゴリ別倍率を収集
        selectedActionCategories.forEach(category => {
          const multiplierInput = document.querySelector(`.action-category-multiplier[data-category="${category}"]`);
          if (multiplierInput && multiplierInput.value) {
            actionCategoryMultipliers[category] = parseFloat(multiplierInput.value);
          }
        });
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
        product_multipliers: productMultipliers,
        product_category_multipliers: productCategoryMultipliers,
        action_multipliers: actionMultipliers,
        action_category_multipliers: actionCategoryMultipliers,
        status: formData.get('status'),
        approval_required: formData.get('approval_required'),
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