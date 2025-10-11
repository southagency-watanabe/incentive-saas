<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/database.php';

// 管理者権限チェック
requireAdmin();

// メンバー・商品・チーム一覧取得（プルダウン用）
$pdo = getDB();

$stmt = $pdo->prepare("SELECT member_id, name FROM members WHERE tenant_id = :tenant_id AND status = '有効' ORDER BY member_id ASC");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$members = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT product_id, product_name, price, point FROM products WHERE tenant_id = :tenant_id AND status = '有効' ORDER BY product_id ASC");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$products = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT team_id, team_name FROM teams WHERE tenant_id = :tenant_id ORDER BY team_id ASC");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$teams = $stmt->fetchAll();

// 商品情報をJSON化（JavaScript用）
$productsJson = json_encode($products);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>売上入力 - インセンティブSaaS</title>
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
      <a href="/admin/sales/input.php" class="flex items-center px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
        <span class="font-medium">売上管理</span>
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
        <h2 class="text-2xl font-bold text-gray-800">売上入力</h2>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
    <!-- 売上入力フォーム -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-xl font-bold text-gray-800 mb-4">売上入力</h2>

      <form id="salesForm" class="space-y-4">
        <div class="grid grid-cols-3 gap-4">
          <!-- 売上計上日時 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">売上計上日時 <span class="text-red-500">*</span></label>
            <input type="datetime-local" id="date" name="date" required max="<?= date('Y-m-d\TH:i') ?>" value="<?= date('Y-m-d\TH:i') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- メンバー -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">メンバー <span class="text-red-500">*</span></label>
            <select id="memberId" name="member_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="">選択してください</option>
              <?php foreach ($members as $member): ?>
                <option value="<?= htmlspecialchars($member['member_id']) ?>">
                  <?= htmlspecialchars($member['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 商品 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">商品 <span class="text-red-500">*</span></label>
            <select id="productId" name="product_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="">選択してください</option>
              <?php foreach ($products as $product): ?>
                <option value="<?= htmlspecialchars($product['product_id']) ?>" data-price="<?= htmlspecialchars($product['price']) ?>" data-point="<?= htmlspecialchars($product['point']) ?>">
                  <?= htmlspecialchars($product['product_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 数量 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">数量 <span class="text-red-500">*</span></label>
            <input type="number" id="quantity" name="quantity" min="1" required value="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 単価 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">単価 <span class="text-red-500">*</span></label>
            <input type="number" id="unitPrice" name="unit_price" min="0" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <p id="priceWarning" class="text-sm text-orange-600 mt-1 hidden">⚠ 標準価格と異なります</p>
          </div>

          <!-- 合計金額（表示のみ） -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">合計金額</label>
            <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-900 font-medium">
              ¥<span id="totalAmount">0</span>
            </div>
          </div>
        </div>

        <!-- イベント情報プレビュー -->
        <div id="eventPreview" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h3 class="text-sm font-medium text-blue-900 mb-2">適用されるイベント</h3>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-xs text-blue-700 mb-1">イベント名</label>
              <div class="text-sm font-medium text-blue-900" id="previewEventName">-</div>
            </div>
            <div>
              <label class="block text-xs text-blue-700 mb-1">イベント倍率</label>
              <div class="text-sm font-medium text-blue-900" id="previewEventMultiplier">1.0倍</div>
            </div>
            <div>
              <label class="block text-xs text-blue-700 mb-1">予想付与ポイント（数量1個あたり）</label>
              <div class="text-sm font-medium text-blue-900"><span id="previewFinalPoint">0</span>pt</div>
            </div>
          </div>
        </div>

        <!-- 備考 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">備考</label>
          <textarea id="note" name="note" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <!-- 登録ボタン -->
        <div class="flex justify-end">
          <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
            登録
          </button>
        </div>
      </form>
    </div>

    <!-- フィルタエリア -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="p-6 pb-3">
        <div class="flex-1">
          <!-- 期間フィルタ -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">期間</label>
            <div class="flex gap-2 items-center flex-wrap">
              <input type="date" id="filterStartDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <span>〜</span>
              <input type="date" id="filterEndDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <select id="filterPeriodPreset" onchange="applyFilterPreset()" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                <option value="today">今日</option>
                <option value="this_week">今週</option>
                <option value="this_month" selected>今月</option>
                <option value="last_month">先月</option>
                <option value="this_quarter">今四半期</option>
                <option value="this_year">今年</option>
                <option value="all">全期間</option>
              </select>
              <button onclick="applyFilters()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                適用
              </button>
              <button onclick="resetFilters()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                リセット
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 詳細フィルタ展開ボタン -->
      <div class="flex justify-center pb-3">
        <button onclick="toggleFilterDetails()" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg id="filterArrow" class="w-6 h-6 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
      </div>

      <!-- フィルタ詳細（開閉可能） -->
      <div id="filterDetails" class="hidden">
        <div class="p-6 pt-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- メンバーフィルタ -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">メンバー</label>
              <div class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
                <?php foreach ($members as $member): ?>
                  <label class="flex items-center space-x-2 mb-1">
                    <input type="checkbox" name="filter_member_ids[]" value="<?= htmlspecialchars($member['member_id']) ?>" class="rounded">
                    <span class="text-sm"><?= htmlspecialchars($member['name']) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- チームフィルタ -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">チーム</label>
              <div class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
                <?php foreach ($teams as $team): ?>
                  <label class="flex items-center space-x-2 mb-1">
                    <input type="checkbox" name="filter_team_ids[]" value="<?= htmlspecialchars($team['team_id']) ?>" class="rounded">
                    <span class="text-sm"><?= htmlspecialchars($team['team_name']) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- 商品フィルタ -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">商品</label>
              <div class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
                <?php foreach ($products as $product): ?>
                  <label class="flex items-center space-x-2 mb-1">
                    <input type="checkbox" name="filter_product_ids[]" value="<?= htmlspecialchars($product['product_id']) ?>" class="rounded">
                    <span class="text-sm"><?= htmlspecialchars($product['product_name']) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- 承認状態フィルタ -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">承認状態</label>
              <div class="border border-gray-300 rounded-md p-3 bg-white">
                <label class="flex items-center space-x-2 mb-1">
                  <input type="checkbox" name="filter_approval_status[]" value="ユーザー確認待ち" class="rounded">
                  <span class="text-sm">ユーザー確認待ち</span>
                </label>
                <label class="flex items-center space-x-2 mb-1">
                  <input type="checkbox" name="filter_approval_status[]" value="承認待ち" class="rounded">
                  <span class="text-sm">承認待ち</span>
                </label>
                <label class="flex items-center space-x-2 mb-1">
                  <input type="checkbox" name="filter_approval_status[]" value="承認済み" class="rounded" checked>
                  <span class="text-sm">承認済み</span>
                </label>
                <label class="flex items-center space-x-2 mb-1">
                  <input type="checkbox" name="filter_approval_status[]" value="却下" class="rounded">
                  <span class="text-sm">却下</span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 一覧ヘッダー -->
    <div class="flex justify-between items-center mb-4">
      <div class="flex items-center gap-4">
        <h2 class="text-xl font-bold text-gray-800">売上一覧</h2>
        <div class="flex items-center gap-2">
          <label class="text-sm text-gray-600">表示件数:</label>
          <select id="pageSize" onchange="changePageSize()" class="px-3 py-1 border border-gray-300 rounded-md text-sm">
            <option value="10">10件</option>
            <option value="30">30件</option>
            <option value="50">50件</option>
            <option value="100">100件</option>
          </select>
        </div>
      </div>
      <button onclick="refreshList()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center gap-2">
        <span>🔄</span>
        <span>更新</span>
      </button>
    </div>

    <!-- 合計表示 -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 grid grid-cols-2 gap-4">
      <div>
        <p class="text-lg font-bold text-blue-900">
          合計金額: ¥<span id="listTotal">0</span>
        </p>
      </div>
      <div>
        <p class="text-lg font-bold text-purple-900">
          合計付与PT: <span id="listTotalPoints">0</span>pt
        </p>
      </div>
    </div>

    <!-- テーブル -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th onclick="sortTable('date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
              日付 <span id="sort-date"></span>
            </th>
            <th onclick="sortTable('member_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
              メンバー <span id="sort-member_name"></span>
            </th>
            <th onclick="sortTable('product_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
              商品 <span id="sort-product_name"></span>
            </th>
            <th onclick="sortTable('quantity')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
              数量 <span id="sort-quantity"></span>
            </th>
            <th onclick="sortTable('unit_price')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
              単価 <span id="sort-unit_price"></span>
            </th>
            <th onclick="sortTable('amount')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
              金額 <span id="sort-amount"></span>
            </th>
            <th onclick="sortTable('final_point')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
              付与pt <span id="sort-final_point"></span>
            </th>
            <th onclick="sortTable('event_multiplier')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
              倍率 <span id="sort-event_multiplier"></span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">イベント</th>
            <th onclick="sortTable('approval_status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
              承認状態 <span id="sort-approval_status"></span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">備考</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
          </tr>
        </thead>
        <tbody id="salesTableBody" class="bg-white divide-y divide-gray-200">
          <!-- データはJavaScriptで挿入 -->
        </tbody>
      </table>
    </div>

    <!-- ページネーション -->
    <div class="flex justify-between items-center mt-4">
      <div class="text-sm text-gray-600">
        <span id="pageInfo"></span>
      </div>
      <div class="flex gap-2">
        <button id="prevPage" onclick="prevPage()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed">
          前へ
        </button>
        <div id="pageButtons" class="flex gap-1">
          <!-- ページボタンはJavaScriptで挿入 -->
        </div>
        <button id="nextPage" onclick="nextPage()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed">
          次へ
        </button>
      </div>
    </div>
    </main>
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

    const productsData = <?= $productsJson ?>;
    let currentFilter = 'all';
    let currentSalesData = [];
    let currentSortKey = 'date';
    let currentSortOrder = 'desc';
    let currentPage = 1;
    let pageSize = 10;
    let filterDetailsOpen = false;

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      applyFilterPreset(); // デフォルトで今月を設定
      applyFilters(); // フィルタを適用してデータ読み込み
      setupProductChange();
      setupPriceChange();
      setupQuantityChange();
      setupDateChange();
      setupEventPreview();
    });

    // 商品選択時の処理
    function setupProductChange() {
      const productSelect = document.getElementById('productId');
      const unitPriceInput = document.getElementById('unitPrice');

      productSelect.addEventListener('change', (e) => {
        const selectedOption = e.target.options[e.target.selectedIndex];
        if (selectedOption.value) {
          const price = selectedOption.getAttribute('data-price');
          unitPriceInput.value = price;
          updateTotal();
          checkPriceChange();
        }
      });
    }

    // 単価変更時の警告
    function setupPriceChange() {
      const unitPriceInput = document.getElementById('unitPrice');
      unitPriceInput.addEventListener('input', () => {
        updateTotal();
        checkPriceChange();
      });
    }

    // 数量変更時の合計更新
    function setupQuantityChange() {
      const quantityInput = document.getElementById('quantity');
      quantityInput.addEventListener('input', updateTotal);
    }

    // 日付変更時の処理
    function setupDateChange() {
      const dateInput = document.getElementById('date');
      dateInput.addEventListener('change', updateEventPreview);
    }

    // イベントプレビューセットアップ
    function setupEventPreview() {
      const productSelect = document.getElementById('productId');
      productSelect.addEventListener('change', updateEventPreview);
    }

    // イベントプレビュー更新
    async function updateEventPreview() {
      const dateInput = document.getElementById('date');
      const productSelect = document.getElementById('productId');
      const eventPreviewDiv = document.getElementById('eventPreview');
      const previewEventName = document.getElementById('previewEventName');
      const previewEventMultiplier = document.getElementById('previewEventMultiplier');
      const previewFinalPoint = document.getElementById('previewFinalPoint');

      const date = dateInput.value;
      const productId = productSelect.value;

      // 日付と商品が両方選択されていない場合は非表示
      if (!date || !productId) {
        eventPreviewDiv.classList.add('hidden');
        return;
      }

      try {
        // datetime-local形式をdatetime形式に変換（YYYY-MM-DD HH:MM:SS）
        const dateTime = date.replace('T', ' ') + ':00';

        const response = await fetch(`/api/sales.php?preview=true&date=${encodeURIComponent(dateTime)}&product_id=${encodeURIComponent(productId)}`);
        const result = await response.json();

        if (result.success) {
          // イベント情報を表示
          if (result.applied_event_name) {
            previewEventName.textContent = result.applied_event_name;
            previewEventMultiplier.textContent = parseFloat(result.event_multiplier).toFixed(1) + '倍';
            const finalPoint = Math.floor(result.base_point * result.event_multiplier);
            previewFinalPoint.textContent = finalPoint;
            eventPreviewDiv.classList.remove('hidden');
          } else {
            // イベントが適用されない場合
            previewEventName.textContent = 'なし';
            previewEventMultiplier.textContent = '1.0倍';
            const finalPoint = result.base_point;
            previewFinalPoint.textContent = finalPoint;
            eventPreviewDiv.classList.remove('hidden');
          }
        }
      } catch (error) {
        console.error('イベントプレビューの取得に失敗:', error);
        eventPreviewDiv.classList.add('hidden');
      }
    }

    // 単価変更チェック
    function checkPriceChange() {
      const productSelect = document.getElementById('productId');
      const unitPriceInput = document.getElementById('unitPrice');
      const priceWarning = document.getElementById('priceWarning');

      const selectedOption = productSelect.options[productSelect.selectedIndex];
      if (selectedOption.value) {
        const standardPrice = parseFloat(selectedOption.getAttribute('data-price'));
        const currentPrice = parseFloat(unitPriceInput.value);

        if (Math.abs(standardPrice - currentPrice) > 0.01) {
          priceWarning.classList.remove('hidden');
        } else {
          priceWarning.classList.add('hidden');
        }
      }
    }

    // 合計金額更新
    function updateTotal() {
      const quantity = parseFloat(document.getElementById('quantity').value) || 0;
      const unitPrice = parseFloat(document.getElementById('unitPrice').value) || 0;
      const total = quantity * unitPrice;
      document.getElementById('totalAmount').textContent = total.toLocaleString();
    }

    // フィルタ詳細の開閉
    function toggleFilterDetails() {
      filterDetailsOpen = !filterDetailsOpen;
      const details = document.getElementById('filterDetails');
      const arrow = document.getElementById('filterArrow');

      if (filterDetailsOpen) {
        details.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
      } else {
        details.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
      }
    }

    // フィルタプリセット適用
    function applyFilterPreset() {
      const preset = document.getElementById('filterPeriodPreset').value;
      const startDateInput = document.getElementById('filterStartDate');
      const endDateInput = document.getElementById('filterEndDate');
      const today = new Date();

      let startDate, endDate;

      switch (preset) {
        case 'today':
          startDate = endDate = new Date();
          break;
        case 'this_week':
          const dayOfWeek = today.getDay();
          const diff = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
          startDate = new Date(today);
          startDate.setDate(today.getDate() - diff);
          endDate = new Date(startDate);
          endDate.setDate(startDate.getDate() + 6);
          break;
        case 'this_month':
          startDate = new Date(today.getFullYear(), today.getMonth(), 1);
          endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
          break;
        case 'last_month':
          startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
          endDate = new Date(today.getFullYear(), today.getMonth(), 0);
          break;
        case 'this_quarter':
          const quarter = Math.floor(today.getMonth() / 3);
          startDate = new Date(today.getFullYear(), quarter * 3, 1);
          endDate = new Date(today.getFullYear(), quarter * 3 + 3, 0);
          break;
        case 'this_year':
          startDate = new Date(today.getFullYear(), 0, 1);
          endDate = new Date(today.getFullYear(), 11, 31);
          break;
        case 'all':
          startDateInput.value = '';
          endDateInput.value = '';
          return;
      }

      startDateInput.value = formatDate(startDate);
      endDateInput.value = formatDate(endDate);
    }

    // 日付フォーマット（YYYY-MM-DD）
    function formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }

    // フィルタ適用
    function applyFilters() {
      currentPage = 1;
      loadSales();
    }

    // フィルタリセット
    function resetFilters() {
      // 日付をデフォルト（今月）に戻す
      document.getElementById('filterPeriodPreset').value = 'this_month';
      applyFilterPreset();

      // チェックボックスをすべてリセット
      document.querySelectorAll('input[name^="filter_member_ids"]').forEach(cb => cb.checked = false);
      document.querySelectorAll('input[name^="filter_team_ids"]').forEach(cb => cb.checked = false);
      document.querySelectorAll('input[name^="filter_product_ids"]').forEach(cb => cb.checked = false);

      // 承認状態は承認済みのみチェック
      document.querySelectorAll('input[name^="filter_approval_status"]').forEach(cb => {
        cb.checked = cb.value === '承認済み';
      });

      // フィルタ適用
      applyFilters();
    }

    // フィルタ設定
    function setFilter(filter) {
      currentFilter = filter;

      // ボタンのスタイル更新
      document.getElementById('filterAll').classList.toggle('bg-blue-600', filter === 'all');
      document.getElementById('filterAll').classList.toggle('bg-gray-500', filter !== 'all');
      document.getElementById('filterPending').classList.toggle('bg-blue-600', filter === 'pending');
      document.getElementById('filterPending').classList.toggle('bg-orange-500', filter !== 'pending');

      loadSales();
    }

    // 売上一覧取得
    async function loadSales() {
      try {
        // フィルタパラメータを収集
        const params = new URLSearchParams();

        // 期間フィルタ
        const startDate = document.getElementById('filterStartDate').value;
        const endDate = document.getElementById('filterEndDate').value;
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        // メンバーフィルタ
        const selectedMembers = Array.from(document.querySelectorAll('input[name^="filter_member_ids"]:checked'))
          .map(cb => cb.value);
        if (selectedMembers.length > 0) {
          selectedMembers.forEach(id => params.append('member_ids[]', id));
        }

        // チームフィルタ
        const selectedTeams = Array.from(document.querySelectorAll('input[name^="filter_team_ids"]:checked'))
          .map(cb => cb.value);
        if (selectedTeams.length > 0) {
          selectedTeams.forEach(id => params.append('team_ids[]', id));
        }

        // 商品フィルタ
        const selectedProducts = Array.from(document.querySelectorAll('input[name^="filter_product_ids"]:checked'))
          .map(cb => cb.value);
        if (selectedProducts.length > 0) {
          selectedProducts.forEach(id => params.append('product_ids[]', id));
        }

        // 承認状態フィルタ
        const selectedStatuses = Array.from(document.querySelectorAll('input[name^="filter_approval_status"]:checked'))
          .map(cb => cb.value);
        if (selectedStatuses.length > 0) {
          selectedStatuses.forEach(status => params.append('approval_statuses[]', status));
        }

        const response = await fetch(`/api/sales.php?${params.toString()}`);
        const result = await response.json();

        if (result.success) {
          currentSalesData = result.data;
          applySortAndRender();
          document.getElementById('listTotal').textContent = result.total.toLocaleString();
          document.getElementById('listTotalPoints').textContent = result.total_points.toLocaleString();
        } else {
          alert('データの取得に失敗しました。');
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // テーブル描画
    function renderTable(sales) {
      const tbody = document.getElementById('salesTableBody');
      tbody.innerHTML = '';

      if (sales.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      sales.forEach(sale => {
        const amount = sale.quantity * sale.unit_price;
        const eventDisplay = sale.applied_event_name ? escapeHtml(sale.applied_event_name) : '-';
        const eventClass = sale.applied_event_name ? 'text-blue-600 font-medium' : 'text-gray-500';
        // 日付のみ表示（YYYY-MM-DD）
        const dateOnly = sale.date ? sale.date.substring(0, 10) : '';

        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${dateOnly}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(sale.member_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(sale.product_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${sale.quantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(sale.unit_price).toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${amount.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${sale.final_point}pt</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(sale.event_multiplier).toFixed(1)}倍</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm ${eventClass}">${eventDisplay}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(sale.approval_status)}">
                            ${escapeHtml(sale.approval_status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">${escapeHtml(sale.note || '-')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick='deleteSale(${sale.id}, "${escapeHtml(sale.product_name)}")' class="text-red-600 hover:text-red-900">削除</button>
                    </td>
                `;
        tbody.appendChild(tr);
      });
    }

    // 承認状態の色
    function getStatusColor(status) {
      switch (status) {
        case 'ユーザー確認待ち':
          return 'bg-orange-100 text-orange-800';
        case '承認待ち':
          return 'bg-yellow-100 text-yellow-800';
        case '承認済み':
          return 'bg-green-100 text-green-800';
        case '却下':
          return 'bg-red-100 text-red-800';
        default:
          return 'bg-gray-100 text-gray-800';
      }
    }

    // フォーム送信
    document.getElementById('salesForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);

      // datetime-local形式をdatetime形式に変換（YYYY-MM-DD HH:MM:SS）
      const dateTime = formData.get('date');
      const date = dateTime ? dateTime.replace('T', ' ') + ':00' : '';

      const data = {
        date: date,
        member_id: formData.get('member_id'),
        product_id: formData.get('product_id'),
        quantity: formData.get('quantity'),
        unit_price: formData.get('unit_price'),
        note: formData.get('note')
      };

      try {
        const response = await fetch('/api/sales.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          e.target.reset();
          document.getElementById('date').value = '<?= date('Y-m-d\TH:i') ?>';
          document.getElementById('totalAmount').textContent = '0';
          document.getElementById('priceWarning').classList.add('hidden');
          loadSales();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    });

    // ソート機能
    function sortTable(key) {
      // 同じカラムをクリックした場合は昇順/降順を切り替え
      if (currentSortKey === key) {
        currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        currentSortKey = key;
        currentSortOrder = 'asc';
      }

      applySortAndRender();
    }

    // ソート適用とレンダリング
    function applySortAndRender() {
      // データのソート
      const sortedData = [...currentSalesData].sort((a, b) => {
        let valA, valB;

        switch (currentSortKey) {
          case 'date':
            valA = a.date;
            valB = b.date;
            break;
          case 'member_name':
            valA = a.member_name;
            valB = b.member_name;
            break;
          case 'product_name':
            valA = a.product_name;
            valB = b.product_name;
            break;
          case 'quantity':
            valA = parseFloat(a.quantity);
            valB = parseFloat(b.quantity);
            break;
          case 'unit_price':
            valA = parseFloat(a.unit_price);
            valB = parseFloat(b.unit_price);
            break;
          case 'amount':
            valA = parseFloat(a.quantity) * parseFloat(a.unit_price);
            valB = parseFloat(b.quantity) * parseFloat(b.unit_price);
            break;
          case 'final_point':
            valA = parseInt(a.final_point);
            valB = parseInt(b.final_point);
            break;
          case 'event_multiplier':
            valA = parseFloat(a.event_multiplier);
            valB = parseFloat(b.event_multiplier);
            break;
          case 'approval_status':
            valA = a.approval_status;
            valB = b.approval_status;
            break;
          default:
            return 0;
        }

        // 比較
        let result = 0;
        if (typeof valA === 'string') {
          result = valA.localeCompare(valB);
        } else {
          result = valA - valB;
        }

        return currentSortOrder === 'asc' ? result : -result;
      });

      // ソートインジケーター更新
      document.querySelectorAll('[id^="sort-"]').forEach(el => el.textContent = '');
      const indicator = document.getElementById(`sort-${currentSortKey}`);
      if (indicator) {
        indicator.textContent = currentSortOrder === 'asc' ? '▲' : '▼';
      }

      // ページネーション適用
      const totalCount = sortedData.length;
      const totalPages = Math.ceil(totalCount / pageSize);
      const startIndex = (currentPage - 1) * pageSize;
      const endIndex = Math.min(startIndex + pageSize, totalCount);
      const pagedData = sortedData.slice(startIndex, endIndex);

      // テーブルレンダリング
      renderTable(pagedData);

      // ページネーション情報更新
      updatePagination(totalCount, totalPages);
    }

    // ページネーション更新
    function updatePagination(totalCount, totalPages) {
      // ページ情報
      const startIndex = (currentPage - 1) * pageSize + 1;
      const endIndex = Math.min(currentPage * pageSize, totalCount);
      document.getElementById('pageInfo').textContent = `全${totalCount}件中 ${startIndex}-${endIndex}件を表示`;

      // 前へボタン
      const prevBtn = document.getElementById('prevPage');
      prevBtn.disabled = currentPage === 1;

      // 次へボタン
      const nextBtn = document.getElementById('nextPage');
      nextBtn.disabled = currentPage === totalPages || totalPages === 0;

      // ページ番号ボタン
      const pageButtonsContainer = document.getElementById('pageButtons');
      pageButtonsContainer.innerHTML = '';

      // 表示するページ番号の範囲を計算（最大5個表示）
      let startPage = Math.max(1, currentPage - 2);
      let endPage = Math.min(totalPages, currentPage + 2);

      // 最初のページ番号と...を追加
      if (startPage > 1) {
        addPageButton(1, pageButtonsContainer);
        if (startPage > 2) {
          const ellipsis = document.createElement('span');
          ellipsis.className = 'px-2 py-2 text-gray-500';
          ellipsis.textContent = '...';
          pageButtonsContainer.appendChild(ellipsis);
        }
      }

      // ページ番号ボタンを追加
      for (let i = startPage; i <= endPage; i++) {
        addPageButton(i, pageButtonsContainer);
      }

      // 最後のページ番号と...を追加
      if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
          const ellipsis = document.createElement('span');
          ellipsis.className = 'px-2 py-2 text-gray-500';
          ellipsis.textContent = '...';
          pageButtonsContainer.appendChild(ellipsis);
        }
        addPageButton(totalPages, pageButtonsContainer);
      }
    }

    // ページボタン追加
    function addPageButton(pageNum, container) {
      const btn = document.createElement('button');
      btn.textContent = pageNum;
      btn.onclick = () => goToPage(pageNum);
      if (pageNum === currentPage) {
        btn.className = 'px-4 py-2 bg-blue-600 text-white rounded font-medium';
      } else {
        btn.className = 'px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300';
      }
      container.appendChild(btn);
    }

    // ページ移動
    function goToPage(page) {
      currentPage = page;
      applySortAndRender();
    }

    function prevPage() {
      if (currentPage > 1) {
        currentPage--;
        applySortAndRender();
      }
    }

    function nextPage() {
      const totalPages = Math.ceil(currentSalesData.length / pageSize);
      if (currentPage < totalPages) {
        currentPage++;
        applySortAndRender();
      }
    }

    // ページサイズ変更
    function changePageSize() {
      pageSize = parseInt(document.getElementById('pageSize').value);
      currentPage = 1;
      applySortAndRender();
    }

    // 削除機能
    async function deleteSale(saleId, productName) {
      if (!confirm(`「${productName}」の売上レコードを削除しますか？\nこの操作は取り消せません。`)) {
        return;
      }

      try {
        const response = await fetch(`/api/sales.php?id=${saleId}`, {
          method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadSales();
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
      loadSales();
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