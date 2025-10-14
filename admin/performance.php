<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

// 管理者権限チェック
requireAdmin();

// メンバー・チーム・商品一覧取得（ダッシュボードフィルタ用）
$pdo = getDB();

$stmt = $pdo->prepare("SELECT member_id, name FROM members WHERE tenant_id = :tenant_id AND status = '有効' ORDER BY member_id ASC");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$members = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT team_id, team_name FROM teams WHERE tenant_id = :tenant_id ORDER BY team_id ASC");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$teams = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT product_id, product_name FROM products WHERE tenant_id = :tenant_id AND status = '有効' ORDER BY product_id ASC");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>実績管理 - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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

      <a href="/admin/performance.php" class="flex items-center px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
        <span class="font-medium">実績管理</span>
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
        <h2 class="text-2xl font-bold text-gray-800">実績管理</h2>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
    <!-- ダッシュボード機能エリア（ダッシュボードから移動） -->
    <!-- フィルタエリア -->
    <div class="bg-white rounded-lg shadow mb-6">
      <!-- フィルタヘッダー（常に表示） -->
      <div class="p-6 pb-3">
        <div class="flex-1">
            <!-- 期間フィルタ（常に表示） -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">期間</label>
              <div class="flex gap-2 items-center flex-wrap">
                <input type="date" id="dashStartDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                <span>〜</span>
                <input type="date" id="dashEndDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                <select id="dashPeriodPreset" onchange="applyDashPreset()" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                  <option value="today">今日</option>
                  <option value="this_week">今週</option>
                  <option value="this_month" selected>今月</option>
                  <option value="last_month">先月</option>
                  <option value="this_quarter">今四半期</option>
                  <option value="this_year">今年</option>
                </select>
                <button onclick="applyDashFilters()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                  適用
                </button>
                <button onclick="resetDashFilters()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                  リセット
                </button>
              </div>
            </div>
        </div>
      </div>

      <!-- 詳細フィルタ展開ボタン -->
      <div class="flex justify-center pb-3">
        <button onclick="toggleDashFilterDetails()" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg id="dashFilterArrow" class="w-6 h-6 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
      </div>

      <!-- フィルタ詳細（開閉可能） -->
      <div id="dashFilterDetails" class="hidden">
        <div class="p-6 pt-4">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- テキスト検索 -->
            <div class="col-span-full">
              <label class="block text-sm font-medium text-gray-700 mb-2">テキスト検索</label>
              <input type="text" id="dashSearchText" placeholder="商品名、メンバー名で検索..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- メンバーフィルタ -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">メンバー</label>
              <div id="dashMemberFilters" class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
                <!-- JavaScriptで動的に挿入 -->
              </div>
            </div>

            <!-- チームフィルタ -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">チーム</label>
              <div id="dashTeamFilters" class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
                <!-- JavaScriptで動的に挿入 -->
              </div>
            </div>

            <!-- 商品フィルタ -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">商品</label>
              <div id="dashProductFilters" class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
                <!-- JavaScriptで動的に挿入 -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- スコアカード -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <!-- 売上金額 -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">商品別売上金額</h3>
        <div class="text-3xl font-bold text-gray-900 mb-2" id="dashSalesAmount">¥0</div>
        <div id="dashSalesDiff" class="text-sm">
          <span class="font-medium">-</span>
          <span class="text-gray-500">対前期間</span>
        </div>
      </div>

      <!-- 売上件数 -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">商品別売上件数</h3>
        <div class="text-3xl font-bold text-gray-900 mb-2" id="dashSalesCount">0件</div>
        <div id="dashCountDiff" class="text-sm">
          <span class="font-medium">-</span>
          <span class="text-gray-500">対前期間</span>
        </div>
      </div>

      <!-- 粗利益 -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">商品別粗利益</h3>
        <div class="text-3xl font-bold text-gray-900 mb-2" id="dashProfitAmount">¥0</div>
        <div id="dashProfitDiff" class="text-sm">
          <span class="font-medium">-</span>
          <span class="text-gray-500">対前期間</span>
        </div>
      </div>
    </div>

    <!-- 売上推移グラフ -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="mb-4">
        <h3 class="text-lg font-bold text-gray-800">売上推移</h3>
      </div>
      <canvas id="dashTrendChart" height="80"></canvas>
    </div>

    <!-- 商品別売上/粗利テーブル -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
      <div class="p-6 border-b">
        <h3 class="text-lg font-bold text-gray-800">商品別売上/粗利</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortDashTable('product_name')">
                商品名 <span id="dash_sort_product_name"></span>
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortDashTable('sales')">
                売上金額 <span id="dash_sort_sales"></span>
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortDashTable('profit')">
                粗利益 <span id="dash_sort_profit"></span>
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortDashTable('quantity')">
                数量 <span id="dash_sort_quantity"></span>
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortDashTable('count')">
                件数 <span id="dash_sort_count"></span>
              </th>
            </tr>
          </thead>
          <tbody id="dashProductTableBody" class="bg-white divide-y divide-gray-200">
            <!-- データはJavaScriptで挿入 -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- サマリーカード -->
    <div class="grid grid-cols-5 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">総売上金額</h3>
        <p class="text-3xl font-bold text-gray-900">¥<span id="totalSales">0</span></p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">総粗利益</h3>
        <p class="text-3xl font-bold text-purple-600">¥<span id="totalProfit">0</span></p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">総付与ポイント</h3>
        <p class="text-3xl font-bold text-blue-600"><span id="totalPoints">0</span>pt</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">売上件数</h3>
        <p class="text-3xl font-bold text-gray-900"><span id="totalCount">0</span>件</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">承認率</h3>
        <p class="text-3xl font-bold text-green-600"><span id="approvalRate">0</span>%</p>
      </div>
    </div>

    <!-- グラフセクション -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex justify-between items-center mb-4">
        <div class="flex gap-2">
          <button id="graphTabProductSales" onclick="switchGraphTab('product_sales')" class="px-4 py-2 rounded bg-blue-600 text-white font-medium">
            商品別売上
          </button>
          <button id="graphTabProductProfit" onclick="switchGraphTab('product_profit')" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
            商品別粗利
          </button>
          <button id="graphTabMemberSales" onclick="switchGraphTab('member_sales')" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
            メンバー別売上
          </button>
          <button id="graphTabMemberProfit" onclick="switchGraphTab('member_profit')" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
            メンバー別粗利益
          </button>
        </div>
        <div class="flex gap-2">
          <button id="btnApproved" onclick="toggleApprovalFilter('approved')" class="px-4 py-2 rounded bg-blue-600 text-white font-medium">
            承認済みのみ
          </button>
          <button id="btnAll" onclick="toggleApprovalFilter('all')" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
            全データ
          </button>
        </div>
      </div>
      <div class="h-96">
        <canvas id="salesChart"></canvas>
      </div>
    </div>

    <!-- タブ -->
    <div class="bg-white border-b rounded-t-lg">
      <div class="flex space-x-6 px-4">
        <button id="tabMembers" onclick="switchTab('members')" class="py-3 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">
          メンバー別実績
        </button>
        <button id="tabProducts" onclick="switchTab('products')" class="py-3 px-2 text-gray-600 hover:text-gray-900">
          商品別実績
        </button>
      </div>
    </div>

    <!-- メンバー別実績タブ -->
    <div id="membersTab" class="bg-white rounded-b-lg shadow overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">メンバー</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">チーム</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">売上件数</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">売上金額</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">基本ポイント</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">最終ポイント</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">平均倍率</th>
          </tr>
        </thead>
        <tbody id="membersTableBody" class="bg-white divide-y divide-gray-200">
          <!-- データはJavaScriptで挿入 -->
        </tbody>
      </table>
    </div>

    <!-- 商品別実績タブ -->
    <div id="productsTab" class="hidden bg-white rounded-b-lg shadow overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">商品名</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">販売数量</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">売上金額</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">付与ポイント</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">平均単価</th>
          </tr>
        </thead>
        <tbody id="productsTableBody" class="bg-white divide-y divide-gray-200">
          <!-- データはJavaScriptで挿入 -->
        </tbody>
      </table>
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

    let currentTab = 'members';
    let currentApprovalFilter = 'approved';
    let currentGraphTab = 'product_sales';
    let salesChart = null;
    let cachedGraphData = null;

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      initChart();
    });

    // タブ切り替え
    function switchTab(tab) {
      currentTab = tab;

      // タブボタンのスタイル更新
      ['Members', 'Products'].forEach(t => {
        const btn = document.getElementById(`tab${t}`);
        const isActive = t.toLowerCase() === tab;
        btn.classList.toggle('border-blue-500', isActive);
        btn.classList.toggle('text-blue-600', isActive);
        btn.classList.toggle('font-medium', isActive);
        btn.classList.toggle('text-gray-600', !isActive);
      });

      // コンテンツ表示切り替え
      document.getElementById('membersTab').classList.toggle('hidden', tab !== 'members');
      document.getElementById('productsTab').classList.toggle('hidden', tab !== 'products');
    }


    // サマリー更新
    function updateSummary(summary) {
      document.getElementById('totalSales').textContent = summary.total_sales.toLocaleString();
      document.getElementById('totalProfit').textContent = summary.total_profit.toLocaleString();
      document.getElementById('totalPoints').textContent = summary.total_points.toLocaleString();
      document.getElementById('totalCount').textContent = summary.total_count.toLocaleString();
      document.getElementById('approvalRate').textContent = summary.approval_rate.toFixed(1);
    }

    // メンバー別実績テーブル描画
    function renderMembersTable(members) {
      const tbody = document.getElementById('membersTableBody');
      tbody.innerHTML = '';

      if (members.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      members.forEach(member => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(member.member_name)}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(member.team_name || '-')}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${member.sales_count}件</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(member.total_sales).toLocaleString()}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${member.base_points}pt</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">${member.final_points}pt</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(member.avg_multiplier).toFixed(2)}倍</td>
        `;
        tbody.appendChild(tr);
      });
    }

    // 商品別実績テーブル描画
    function renderProductsTable(products) {
      const tbody = document.getElementById('productsTableBody');
      tbody.innerHTML = '';

      if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      products.forEach(product => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(product.product_name)}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.total_quantity}個</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(product.total_sales).toLocaleString()}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">${product.total_points}pt</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(product.avg_price).toLocaleString()}</td>
        `;
        tbody.appendChild(tr);
      });
    }

    // グラフ初期化
    function initChart() {
      const ctx = document.getElementById('salesChart').getContext('2d');
      salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: [],
          datasets: [{
            label: '売上金額',
            data: [],
            backgroundColor: 'rgba(59, 130, 246, 0.8)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return '¥' + value.toLocaleString();
                }
              }
            }
          },
          plugins: {
            legend: {
              display: true,
              position: 'top'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return '売上: ¥' + context.parsed.y.toLocaleString();
                }
              }
            }
          }
        }
      });
    }

    // グラフタブ切り替え
    function switchGraphTab(tab) {
      currentGraphTab = tab;

      // タブボタンのスタイル更新
      ['ProductSales', 'MemberSales', 'MemberProfit', 'ProductProfit'].forEach(t => {
        const btn = document.getElementById(`graphTab${t}`);
        const tabKey = t.charAt(0).toLowerCase() + t.slice(1).replace(/([A-Z])/g, '_$1').toLowerCase();
        const isActive = tabKey === tab;
        btn.classList.toggle('bg-blue-600', isActive);
        btn.classList.toggle('text-white', isActive);
        btn.classList.toggle('font-medium', isActive);
        btn.classList.toggle('bg-gray-200', !isActive);
        btn.classList.toggle('text-gray-700', !isActive);
      });

      // グラフ更新
      updateChartByTab(tab);
    }

    // グラフタブに応じてグラフを更新
    function updateChartByTab(tab) {
      if (!salesChart || !cachedGraphData) return;

      const data = cachedGraphData[tab] || [];
      const labels = data.map(d => d.label);
      const values = data.map(d => d.value);

      // グラフタイトルとラベルを変更
      let chartLabel = '';
      let yAxisLabel = '';
      let valuePrefix = '';

      switch(tab) {
        case 'product_sales':
          chartLabel = '商品別売上';
          valuePrefix = '¥';
          break;
        case 'member_sales':
          chartLabel = 'メンバー別売上';
          valuePrefix = '¥';
          break;
        case 'member_profit':
          chartLabel = 'メンバー別粗利益';
          valuePrefix = '¥';
          break;
        case 'product_profit':
          chartLabel = '商品別粗利益';
          valuePrefix = '¥';
          break;
      }

      salesChart.data.labels = labels;
      salesChart.data.datasets[0].label = chartLabel;
      salesChart.data.datasets[0].data = values;

      // Y軸のフォーマット更新
      salesChart.options.scales.y.ticks.callback = function(value) {
        return valuePrefix + value.toLocaleString();
      };

      // ツールチップのフォーマット更新
      salesChart.options.plugins.tooltip.callbacks.label = function(context) {
        return chartLabel + ': ' + valuePrefix + context.parsed.y.toLocaleString();
      };

      salesChart.update();
    }

    // グラフ更新（後方互換性のため残す）
    function updateChart(members) {
      updateChartByTab(currentGraphTab);
    }

    // 承認フィルタ切り替え
    function toggleApprovalFilter(filter) {
      currentApprovalFilter = filter;

      // ボタンのスタイル更新
      const btnApproved = document.getElementById('btnApproved');
      const btnAll = document.getElementById('btnAll');

      if (filter === 'approved') {
        btnApproved.className = 'px-4 py-2 rounded bg-blue-600 text-white font-medium';
        btnAll.className = 'px-4 py-2 rounded bg-gray-200 text-gray-700';
      } else {
        btnApproved.className = 'px-4 py-2 rounded bg-gray-200 text-gray-700';
        btnAll.className = 'px-4 py-2 rounded bg-blue-600 text-white font-medium';
      }

      // データ再読み込み（現在の期間フィルタを使用）
      const startDate = document.getElementById('dashStartDate').value;
      const endDate = document.getElementById('dashEndDate').value;
      if (startDate && endDate) {
        loadPerformanceData(startDate, endDate);
      }
    }

    // HTMLエスケープ
    function escapeHtml(text) {
      if (text === null || text === undefined) return '';
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // ========================================
    // ダッシュボード機能（ダッシュボードから移動）
    // ========================================

    let dashCurrentData = null;
    let dashTrendChart = null;
    let dashCurrentSort = {
      column: 'sales',
      direction: 'desc'
    };
    let dashFilterDetailsOpen = false;

    // ダッシュボード機能の初期化
    document.addEventListener('DOMContentLoaded', () => {
      loadDashboardFilters();
      applyDashPreset(); // デフォルトで今月を設定
      applyDashFilters();
    });

    // フィルタ選択肢の読み込み
    function loadDashboardFilters() {
      const members = <?= json_encode($members) ?>;
      const teams = <?= json_encode($teams) ?>;
      const products = <?= json_encode($products) ?>;

      // メンバーフィルタ
      const memberFilters = document.getElementById('dashMemberFilters');
      memberFilters.innerHTML = '';
      members.forEach(member => {
        const label = document.createElement('label');
        label.className = 'flex items-center space-x-2 mb-1';
        label.innerHTML = `
          <input type="checkbox" name="dash_member_ids[]" value="${escapeHtml(member.member_id)}" class="rounded">
          <span class="text-sm">${escapeHtml(member.name)}</span>
        `;
        memberFilters.appendChild(label);
      });

      // チームフィルタ
      const teamFilters = document.getElementById('dashTeamFilters');
      teamFilters.innerHTML = '';
      teams.forEach(team => {
        const label = document.createElement('label');
        label.className = 'flex items-center space-x-2 mb-1';
        label.innerHTML = `
          <input type="checkbox" name="dash_team_ids[]" value="${escapeHtml(team.team_id)}" class="rounded">
          <span class="text-sm">${escapeHtml(team.team_name)}</span>
        `;
        teamFilters.appendChild(label);
      });

      // 商品フィルタ
      const productFilters = document.getElementById('dashProductFilters');
      productFilters.innerHTML = '';
      products.forEach(product => {
        const label = document.createElement('label');
        label.className = 'flex items-center space-x-2 mb-1';
        label.innerHTML = `
          <input type="checkbox" name="dash_product_ids[]" value="${escapeHtml(product.product_id)}" class="rounded">
          <span class="text-sm">${escapeHtml(product.product_name)}</span>
        `;
        productFilters.appendChild(label);
      });
    }

    // フィルタ詳細の開閉
    function toggleDashFilterDetails() {
      const details = document.getElementById('dashFilterDetails');
      const arrow = document.getElementById('dashFilterArrow');

      dashFilterDetailsOpen = !dashFilterDetailsOpen;

      if (dashFilterDetailsOpen) {
        details.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
      } else {
        details.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
      }
    }

    // プリセット適用
    function applyDashPreset() {
      const preset = document.getElementById('dashPeriodPreset').value;
      const today = new Date();
      let startDate, endDate;

      switch (preset) {
        case 'today':
          startDate = endDate = today.toISOString().split('T')[0];
          break;
        case 'this_week':
          const dayOfWeek = today.getDay();
          const monday = new Date(today);
          monday.setDate(today.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));
          startDate = monday.toISOString().split('T')[0];
          endDate = today.toISOString().split('T')[0];
          break;
        case 'this_month':
          startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
          endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
          break;
        case 'last_month':
          startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1).toISOString().split('T')[0];
          endDate = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
          break;
        case 'this_quarter':
          const quarter = Math.floor(today.getMonth() / 3);
          startDate = new Date(today.getFullYear(), quarter * 3, 1).toISOString().split('T')[0];
          endDate = new Date(today.getFullYear(), (quarter + 1) * 3, 0).toISOString().split('T')[0];
          break;
        case 'this_year':
          startDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
          endDate = new Date(today.getFullYear(), 11, 31).toISOString().split('T')[0];
          break;
        default:
          return;
      }

      document.getElementById('dashStartDate').value = startDate;
      document.getElementById('dashEndDate').value = endDate;
    }

    // フィルタリセット
    function resetDashFilters() {
      document.getElementById('dashPeriodPreset').value = 'this_month';
      applyDashPreset();
      document.getElementById('dashSearchText').value = '';
      document.querySelectorAll('input[name^="dash_"][type="checkbox"]').forEach(cb => cb.checked = false);
      applyDashFilters();
    }

    // 期間に基づいて自動的にグラフ表示単位を計算
    function calculateDashGranularity(startDate, endDate) {
      if (!startDate || !endDate) {
        return 'monthly'; // デフォルト
      }

      const start = new Date(startDate);
      const end = new Date(endDate);
      const daysDiff = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;

      if (isNaN(daysDiff)) {
        return 'monthly'; // デフォルト
      }

      // 先月・今月・今週は日次、それ以外は従来のロジック
      if (daysDiff <= 31) {
        return 'daily';  // 31日以内は日次表示
      } else if (daysDiff <= 92) {
        return 'weekly'; // 約3ヶ月以内は週次表示
      } else if (daysDiff <= 365) {
        return 'monthly'; // 1年以内は月次表示
      } else if (daysDiff <= 730) {
        return 'quarterly'; // 2年以内は四半期表示
      } else {
        return 'yearly'; // それ以上は年次表示
      }
    }

    // フィルタ適用
    async function applyDashFilters() {
      try {
        const startDate = document.getElementById('dashStartDate').value;
        const endDate = document.getElementById('dashEndDate').value;
        const searchText = document.getElementById('dashSearchText').value;

        if (!startDate || !endDate) {
          alert('開始日と終了日を選択してください。');
          return;
        }

        // 期間に基づいて自動的にグラフ表示単位を計算
        const granularity = calculateDashGranularity(startDate, endDate);

        const memberIds = Array.from(document.querySelectorAll('input[name="dash_member_ids[]"]:checked'))
          .map(cb => cb.value).join(',');
        const teamIds = Array.from(document.querySelectorAll('input[name="dash_team_ids[]"]:checked'))
          .map(cb => cb.value).join(',');
        const productIds = Array.from(document.querySelectorAll('input[name="dash_product_ids[]"]:checked'))
          .map(cb => cb.value).join(',');

        const params = new URLSearchParams({
          start_date: startDate,
          end_date: endDate,
          search_text: searchText,
          granularity: granularity
        });

        if (memberIds) params.append('member_ids', memberIds);
        if (teamIds) params.append('team_ids', teamIds);
        if (productIds) params.append('product_ids', productIds);

        const response = await fetch(`/api/dashboard.php?${params}`);
        const result = await response.json();

        if (result.success) {
          dashCurrentData = result;
          updateDashScoreCards(result.score_cards);
          updateDashTrendChart(result.trend);
          updateDashProductTable(result.products);
        } else {
          alert('データの取得に失敗しました。');
        }

        // 実績管理データも同時に取得・更新
        await loadPerformanceData(startDate, endDate);
      } catch (error) {
        console.error('Error in applyDashFilters:', error);
        alert('エラーが発生しました。');
      }
    }

    // 実績管理データ取得
    async function loadPerformanceData(startDate, endDate) {
      try {
        let url = `/api/performance.php?period=custom&start_date=${startDate}&end_date=${endDate}&approval_filter=${currentApprovalFilter}`;

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
          updateSummary(result.summary);
          renderMembersTable(result.members);
          renderProductsTable(result.products);
          cachedGraphData = result.graphs;
          updateChartByTab(currentGraphTab);
        } else {
          console.error('実績管理データの取得に失敗しました。');
        }
      } catch (error) {
        console.error('Error in loadPerformanceData:', error);
      }
    }

    // スコアカード更新
    function updateDashScoreCards(scoreCards) {
      // 売上金額
      document.getElementById('dashSalesAmount').textContent = '¥' + scoreCards.sales.current.toLocaleString();
      document.getElementById('dashSalesDiff').innerHTML = formatDashDiff(scoreCards.sales.diff, scoreCards.sales.diff_percent);

      // 売上件数
      document.getElementById('dashSalesCount').textContent = scoreCards.count.current.toLocaleString() + '件';
      document.getElementById('dashCountDiff').innerHTML = formatDashDiff(scoreCards.count.diff, scoreCards.count.diff_percent);

      // 粗利益
      document.getElementById('dashProfitAmount').textContent = '¥' + scoreCards.profit.current.toLocaleString();
      document.getElementById('dashProfitDiff').innerHTML = formatDashDiff(scoreCards.profit.diff, scoreCards.profit.diff_percent);
    }

    // 差分フォーマット
    function formatDashDiff(diff, percent) {
      const sign = diff >= 0 ? '+' : '';
      const color = diff >= 0 ? 'text-green-600' : 'text-red-600';
      const percentStr = percent.toFixed(1) + '%';
      const diffStr = '¥' + Math.abs(diff).toLocaleString();
      return `<span class="font-medium ${color}">${sign}${percentStr} (${sign}${diffStr})</span> <span class="text-gray-500">対前期間</span>`;
    }

    // グラフ更新
    function updateDashTrendChart(trendData) {
      const ctx = document.getElementById('dashTrendChart').getContext('2d');

      if (dashTrendChart) {
        dashTrendChart.destroy();
      }

      dashTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: trendData.map(d => d.period),
          datasets: [{
              label: '売上金額',
              data: trendData.map(d => d.sales),
              borderColor: 'rgb(59, 130, 246)',
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              tension: 0.4
            },
            {
              label: '粗利益',
              data: trendData.map(d => d.profit),
              borderColor: 'rgb(34, 197, 94)',
              backgroundColor: 'rgba(34, 197, 94, 0.1)',
              tension: 0.4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              position: 'top',
            }
          },
          scales: {
            x: {
              ticks: {
                callback: function(value, index, ticks) {
                  const label = this.getLabelForValue(value);
                  // 一番左（最初）と一番右（最後）だけ西暦を表示
                  if (index === 0 || index === ticks.length - 1) {
                    return label; // 西暦付き（YYYY-MM-DD）
                  }
                  // それ以外は月日のみ（MM-DD）
                  if (label && label.includes('-')) {
                    const parts = label.split('-');
                    if (parts.length >= 3) {
                      return parts[1] + '-' + parts[2]; // MM-DD
                    } else if (parts.length === 2) {
                      return parts[1]; // 月次の場合はMM
                    }
                  }
                  return label;
                }
              }
            },
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return '¥' + value.toLocaleString();
                }
              }
            }
          }
        }
      });
    }

    // 商品テーブル更新
    function updateDashProductTable(products) {
      const tbody = document.getElementById('dashProductTableBody');
      tbody.innerHTML = '';

      if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      // ソート適用
      const sortedProducts = sortDashData(products);

      sortedProducts.forEach(product => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(product.product_name)}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">¥${parseFloat(product.sales).toLocaleString()}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">¥${parseFloat(product.profit).toLocaleString()}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${parseInt(product.quantity).toLocaleString()}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${parseInt(product.count).toLocaleString()}</td>
        `;
        tbody.appendChild(tr);
      });

      updateDashSortIndicators();
    }

    // テーブルソート
    function sortDashTable(column) {
      if (dashCurrentSort.column === column) {
        dashCurrentSort.direction = dashCurrentSort.direction === 'asc' ? 'desc' : 'asc';
      } else {
        dashCurrentSort.column = column;
        dashCurrentSort.direction = 'desc';
      }

      if (dashCurrentData) {
        updateDashProductTable(dashCurrentData.products);
      }
    }

    // データソート
    function sortDashData(data) {
      const sorted = [...data];
      sorted.sort((a, b) => {
        let aVal = a[dashCurrentSort.column];
        let bVal = b[dashCurrentSort.column];

        // 数値の場合は数値として比較
        if (typeof aVal === 'string' && !isNaN(parseFloat(aVal))) {
          aVal = parseFloat(aVal);
          bVal = parseFloat(bVal);
        }

        if (dashCurrentSort.direction === 'asc') {
          return aVal > bVal ? 1 : -1;
        } else {
          return aVal < bVal ? 1 : -1;
        }
      });
      return sorted;
    }

    // ソートインジケーター更新
    function updateDashSortIndicators() {
      ['product_name', 'sales', 'profit', 'quantity', 'count'].forEach(col => {
        const indicator = document.getElementById(`dash_sort_${col}`);
        if (dashCurrentSort.column === col) {
          indicator.textContent = dashCurrentSort.direction === 'asc' ? '▲' : '▼';
        } else {
          indicator.textContent = '';
        }
      });
    }
  </script>
</body>

</html>
