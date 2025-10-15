<?php
require_once '../../includes/session.php';
require_once '../../config/database.php';
requireLogin();

$page_title = '日別実績管理';
$active_page = 'daily';
$default_period = 'today'; // デフォルト期間

// メンバー・チーム・商品一覧取得（フィルタ用）
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
  <title><?= $page_title ?> - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gray-50">
  <div class="flex h-screen">
    <?php include '../../includes/performance/sidebar.php'; ?>

    <main class="flex-1 overflow-y-auto">
      <!-- ページヘッダー -->
      <header class="bg-white shadow-sm border-b">
        <div class="px-8 py-6">
          <h2 class="text-2xl font-bold text-gray-800"><?= $page_title ?></h2>
        </div>
      </header>

      <div class="p-8">
        <!-- ダッシュボード機能エリア -->
        
        <!-- フィルタエリア（詳細フィルタ付き） -->
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
                    <option value="today" <?= $default_period === 'today' ? 'selected' : '' ?>>今日</option>
                    <option value="this_week" <?= $default_period === 'this_week' ? 'selected' : '' ?>>今週</option>
                    <option value="this_month" <?= $default_period === 'this_month' ? 'selected' : '' ?>>今月</option>
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

        <?php include '../../includes/performance/dashboard_scorecards.php'; ?>
        <?php include '../../includes/performance/trend_chart.php'; ?>

        <!-- 日毎の売上テーブル -->
        <div class="bg-white rounded-lg shadow mb-6">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">日毎の売上</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortDailyTable('date')">
                    日付
                    <span id="sortIndicatorDailyDate" class="ml-1">▼</span>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortDailyTable('sales_count')">
                    売上件数
                    <span id="sortIndicatorDailySalesCount" class="ml-1"></span>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortDailyTable('total_sales')">
                    売上金額
                    <span id="sortIndicatorDailyTotalSales" class="ml-1"></span>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortDailyTable('total_profit')">
                    粗利
                    <span id="sortIndicatorDailyTotalProfit" class="ml-1"></span>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortDailyTable('total_points')">
                    付与ポイント
                    <span id="sortIndicatorDailyTotalPoints" class="ml-1"></span>
                  </th>
                </tr>
              </thead>
              <tbody id="dailySalesTableBody" class="bg-white divide-y divide-gray-200">
                <!-- データはJavaScriptで挿入 -->
              </tbody>
            </table>
          </div>
          <!-- ページネーション -->
          <div id="dailyPagination" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-500">
              <span id="dailyPageInfo">1-20 / 0件</span>
            </div>
            <div class="flex gap-1" id="dailyPageButtons">
              <!-- ページボタンはJavaScriptで挿入 -->
            </div>
          </div>
        </div>

        <?php include '../../includes/performance/summary.php'; ?>
        <?php include '../../includes/performance/graph_section.php'; ?>
        <?php include '../../includes/performance/data_tables.php'; ?>
      </div>
    </main>
  </div>

  <?php include '../../includes/performance/scripts.php'; ?>

  <script>
    // フィルタデータをグローバル変数として定義
    const members = <?= json_encode($members) ?>;
    const teams = <?= json_encode($teams) ?>;
    const products = <?= json_encode($products) ?>;

    // ページ固有の変数
    let dailySalesData = [];
    let dailySortColumn = 'date';
    let dailySortOrder = 'desc';
    let dailyCurrentPage = 1;
    let dailyItemsPerPage = 20;
    let dashFilterDetailsOpen = false;

    // 共通スクリプトより先に変数を定義
    let isPageInitialized = false;

    // 共通スクリプトで使用される関数をオーバーライド
    // loadData: データ読み込み（承認フィルタ切り替え時に呼ばれる）
    window.loadData = async function() {
      const startDate = document.getElementById('dashStartDate').value;
      const endDate = document.getElementById('dashEndDate').value;
      
      if (startDate && endDate && isPageInitialized) {
        console.log('承認フィルタ変更によるデータ再取得:', { startDate, endDate });
        await loadPerformanceData(startDate, endDate);
      } else {
        console.log('日付が設定されていないか、ページ初期化中のため、データ取得をスキップ');
      }
    };

    // applyPreset: 共通スクリプトの初期化で呼ばれるが、このページでは使わない
    window.applyPreset = function() {
      // daily.phpではapplyDashPreset()を使うため、何もしない
      console.log('applyPreset: スキップ（applyDashPresetを使用）');
    };

    // loadFilterOptions: 共通スクリプトの初期化で呼ばれるが、このページでは独自実装
    window.loadFilterOptions = function() {
      // daily.phpではloadDashboardFilters()を使うため、何もしない
      console.log('loadFilterOptions: スキップ（loadDashboardFiltersを使用）');
    };

    // ダッシュボードフィルタの初期化
    document.addEventListener('DOMContentLoaded', () => {
      console.log('ページ初期化開始');
      loadDashboardFilters();
      applyDashPreset();
      // ページ初期化完了フラグを立ててから、初回データ取得
      setTimeout(() => {
        isPageInitialized = true;
        console.log('ページ初期化完了、データ取得開始');
        applyDashFilters();
      }, 100);
    });

    // ダッシュボードフィルタ選択肢の読み込み
    function loadDashboardFilters() {
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

    // ダッシュボードフィルタ詳細の開閉
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

    // ダッシュボードプリセット適用
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

    // ダッシュボードフィルタリセット
    function resetDashFilters() {
      document.getElementById('dashPeriodPreset').value = 'today';
      applyDashPreset();
      document.getElementById('dashSearchText').value = '';
      document.querySelectorAll('input[name^="dash_"][type="checkbox"]').forEach(cb => cb.checked = false);
      applyDashFilters();
    }

    // ダッシュボードフィルタ適用
    async function applyDashFilters() {
      try {
        const startDate = document.getElementById('dashStartDate').value;
        const endDate = document.getElementById('dashEndDate').value;
        const searchText = document.getElementById('dashSearchText').value;

        if (!startDate || !endDate) {
          alert('開始日と終了日を選択してください。');
          return;
        }

        // 共通スクリプトで使用される変数を更新
        currentStartDate = startDate;
        currentEndDate = endDate;
        
        console.log('日付フィルタ設定:', { startDate, endDate });

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
          updateDashScoreCards(result.score_cards);
          updateDashTrendChart(result.trend);
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
        console.log('実績管理データ取得開始:', { startDate, endDate, approval_filter: currentGraphApprovalFilter });
        
        // 詳細フィルタのパラメータを収集
        const memberIds = Array.from(document.querySelectorAll('input[name="dash_member_ids[]"]:checked'))
          .map(cb => cb.value).join(',');
        const teamIds = Array.from(document.querySelectorAll('input[name="dash_team_ids[]"]:checked'))
          .map(cb => cb.value).join(',');
        const productIds = Array.from(document.querySelectorAll('input[name="dash_product_ids[]"]:checked'))
          .map(cb => cb.value).join(',');
        const searchText = document.getElementById('dashSearchText')?.value || '';
        
        const params = new URLSearchParams({
          period: 'custom',
          start_date: startDate,
          end_date: endDate,
          approval_filter: currentGraphApprovalFilter
        });
        
        if (memberIds) params.append('member_ids', memberIds);
        if (teamIds) params.append('team_ids', teamIds);
        if (productIds) params.append('product_ids', productIds);
        if (searchText) params.append('search_text', searchText);
        
        let url = `/api/performance.php?${params}`;

        console.log('APIリクエストURL:', url);
        
        const response = await fetch(url);
        const result = await response.json();

        console.log('実績管理データ取得結果:', result);

        if (result.success) {
          // サマリーカード更新
          updateSummary(result.summary);
          
          // メンバー別実績テーブル更新（日付フィルタ適用済み）
          console.log('メンバー別実績データ件数:', result.members?.length || 0);
          renderMembersTable(result.members);
          
          // 商品別実績テーブル更新（日付フィルタ適用済み）
          console.log('商品別実績データ件数:', result.products?.length || 0);
          renderProductsTable(result.products);
          
          // グラフデータ更新
          cachedGraphData = result.graphs;
          
          // メンバーが選択されているかチェック
          const hasMemberFilter = memberIds && memberIds.length > 0;
          updateGraphTabsVisibility(hasMemberFilter);
          
          updateChartByTab(currentGraphTab);
          
          // 日毎の売上テーブル更新
          dailySalesData = result.daily_sales || [];
          console.log('日毎の売上データ件数:', dailySalesData.length);
          
          // 降順（最新が上）でソート
          dailySalesData.sort((a, b) => b.date.localeCompare(a.date));
          dailySortColumn = 'date';
          dailySortOrder = 'desc';
          dailyCurrentPage = 1;
          renderDailySalesTable();
          
          // ソートインジケーター更新
          updateDailySortIndicators();
          
          console.log('実績管理データの更新が完了しました');
        } else {
          console.error('実績管理データの取得に失敗しました:', result.message);
          alert('実績管理データの取得に失敗しました: ' + (result.message || '不明なエラー'));
        }
      } catch (error) {
        console.error('Error in loadPerformanceData:', error);
        alert('実績管理データの取得中にエラーが発生しました: ' + error.message);
      }
    }

    // 日毎の売上テーブル描画
    function renderDailySalesTable() {
      const tbody = document.getElementById('dailySalesTableBody');
      tbody.innerHTML = '';

      if (dailySalesData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        document.getElementById('dailyPagination').style.display = 'none';
        return;
      }

      document.getElementById('dailyPagination').style.display = 'flex';

      const totalItems = dailySalesData.length;
      const totalPages = Math.ceil(totalItems / dailyItemsPerPage);
      const startIndex = (dailyCurrentPage - 1) * dailyItemsPerPage;
      const endIndex = Math.min(startIndex + dailyItemsPerPage, totalItems);
      
      const pageData = dailySalesData.slice(startIndex, endIndex);
      
      pageData.forEach(daily => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${daily.date}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${daily.sales_count}件</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(daily.total_sales).toLocaleString()}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">¥${parseFloat(daily.total_profit || 0).toLocaleString()}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">${daily.total_points}pt</td>
        `;
        tbody.appendChild(tr);
      });

      document.getElementById('dailyPageInfo').textContent = `${startIndex + 1}-${endIndex} / ${totalItems}件`;
      renderDailyPagination(totalPages);
    }

    // ページネーションボタン生成
    function renderDailyPagination(totalPages) {
      const container = document.getElementById('dailyPageButtons');
      container.innerHTML = '';

      if (totalPages <= 1) return;

      // 前へボタン
      if (dailyCurrentPage > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.textContent = '‹';
        prevBtn.className = 'px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300';
        prevBtn.onclick = () => changeDailyPage(dailyCurrentPage - 1);
        container.appendChild(prevBtn);
      }

      let startPage = Math.max(1, dailyCurrentPage - 2);
      let endPage = Math.min(totalPages, dailyCurrentPage + 2);

      if (startPage > 1) {
        const firstBtn = document.createElement('button');
        firstBtn.textContent = '1';
        firstBtn.className = 'px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300';
        firstBtn.onclick = () => changeDailyPage(1);
        container.appendChild(firstBtn);
        
        if (startPage > 2) {
          const dots = document.createElement('span');
          dots.textContent = '...';
          dots.className = 'px-2 text-gray-500';
          container.appendChild(dots);
        }
      }

      for (let i = startPage; i <= endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i;
        pageBtn.className = i === dailyCurrentPage
          ? 'px-3 py-1 rounded bg-blue-600 text-white font-medium'
          : 'px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300';
        pageBtn.onclick = () => changeDailyPage(i);
        container.appendChild(pageBtn);
      }

      if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
          const dots = document.createElement('span');
          dots.textContent = '...';
          dots.className = 'px-2 text-gray-500';
          container.appendChild(dots);
        }
        
        const lastBtn = document.createElement('button');
        lastBtn.textContent = totalPages;
        lastBtn.className = 'px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300';
        lastBtn.onclick = () => changeDailyPage(totalPages);
        container.appendChild(lastBtn);
      }

      // 次へボタン
      if (dailyCurrentPage < totalPages) {
        const nextBtn = document.createElement('button');
        nextBtn.textContent = '›';
        nextBtn.className = 'px-3 py-1 rounded bg-gray-200 text-gray-700 hover:bg-gray-300';
        nextBtn.onclick = () => changeDailyPage(dailyCurrentPage + 1);
        container.appendChild(nextBtn);
      }
    }

    // ページ変更
    function changeDailyPage(page) {
      dailyCurrentPage = page;
      renderDailySalesTable();
    }

    // ソート
    function sortDailyTable(column) {
      // 同じカラムをクリックした場合は昇順/降順を切り替え
      if (dailySortColumn === column) {
        dailySortOrder = dailySortOrder === 'desc' ? 'asc' : 'desc';
      } else {
        dailySortColumn = column;
        dailySortOrder = 'desc'; // 新しいカラムは降順から開始
      }

      // データをソート
      dailySalesData.sort((a, b) => {
        let aVal, bVal;
        
        switch(column) {
          case 'date':
            aVal = a.date;
            bVal = b.date;
            break;
          case 'sales_count':
            aVal = parseInt(a.sales_count) || 0;
            bVal = parseInt(b.sales_count) || 0;
            break;
          case 'total_sales':
            aVal = parseFloat(a.total_sales) || 0;
            bVal = parseFloat(b.total_sales) || 0;
            break;
          case 'total_profit':
            aVal = parseFloat(a.total_profit) || 0;
            bVal = parseFloat(b.total_profit) || 0;
            break;
          case 'total_points':
            aVal = parseInt(a.total_points) || 0;
            bVal = parseInt(b.total_points) || 0;
            break;
          default:
            return 0;
        }

        if (dailySortOrder === 'desc') {
          return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
        } else {
          return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
        }
      });
      
      // ソートインジケーター更新
      updateDailySortIndicators();
      
      // ページを1にリセット
      dailyCurrentPage = 1;
      
      renderDailySalesTable();
    }

    // 日毎の売上ソートインジケーター更新
    function updateDailySortIndicators() {
      const indicators = {
        'date': 'sortIndicatorDailyDate',
        'sales_count': 'sortIndicatorDailySalesCount',
        'total_sales': 'sortIndicatorDailyTotalSales',
        'total_profit': 'sortIndicatorDailyTotalProfit',
        'total_points': 'sortIndicatorDailyTotalPoints'
      };

      Object.entries(indicators).forEach(([column, indicatorId]) => {
        const indicator = document.getElementById(indicatorId);
        if (!indicator) return;
        
        if (column === dailySortColumn) {
          indicator.textContent = dailySortOrder === 'desc' ? '▼' : '▲';
        } else {
          indicator.textContent = '';
        }
      });
    }

    // グラフタブの表示/非表示を切り替え
    function updateGraphTabsVisibility(hasMemberFilter) {
      const memberSalesBtn = document.getElementById('graphTabMemberSales');
      const memberProfitBtn = document.getElementById('graphTabMemberProfit');
      const teamSalesBtn = document.getElementById('graphTabTeamSales');
      
      if (hasMemberFilter) {
        // メンバーフィルタが適用されている場合は、商品別のみ表示
        console.log('メンバーフィルタが適用されているため、商品別グラフのみ表示');
        
        if (memberSalesBtn) memberSalesBtn.style.display = 'none';
        if (memberProfitBtn) memberProfitBtn.style.display = 'none';
        if (teamSalesBtn) teamSalesBtn.style.display = 'none';
        
        // 現在のタブがメンバー別やチーム別の場合は、商品別売上に切り替え
        if (currentGraphTab === 'member_sales' || 
            currentGraphTab === 'member_profit' || 
            currentGraphTab === 'team_sales') {
          console.log('現在のタブを商品別売上に切り替え');
          switchGraphTab('product_sales');
        }
      } else {
        // メンバーフィルタが適用されていない場合は、全て表示
        console.log('メンバーフィルタなし、全てのグラフタブを表示');
        
        if (memberSalesBtn) memberSalesBtn.style.display = '';
        if (memberProfitBtn) memberProfitBtn.style.display = '';
        if (teamSalesBtn) teamSalesBtn.style.display = '';
      }
    }

  </script>
</body>
</html>
