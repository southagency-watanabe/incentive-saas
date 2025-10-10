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
        <span>ダッシュボード</span>
      </a>
      <a href="/admin/masters/members.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>マスタ管理</span>
      </a>
      <a href="/admin/sales/input.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>売上管理</span>
      </a>
      <a href="/admin/approvals.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>承認管理</span>
      </a>
      <a href="/admin/performance.php" class="flex items-center px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
        <span class="font-medium">実績管理</span>
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
        <h2 class="text-2xl font-bold text-gray-800">実績管理</h2>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
    <!-- フィルタ -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">期間</label>
          <select id="periodFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <option value="current_month">今月</option>
            <option value="last_month">先月</option>
            <option value="current_year">今年</option>
            <option value="all">全期間</option>
            <option value="custom">カスタム</option>
          </select>
        </div>
        <div id="startDateContainer" class="hidden">
          <label class="block text-sm font-medium text-gray-700 mb-1">開始日</label>
          <input type="date" id="startDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
        </div>
        <div id="endDateContainer" class="hidden">
          <label class="block text-sm font-medium text-gray-700 mb-1">終了日</label>
          <input type="date" id="endDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-end">
          <button onclick="loadPerformance()" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            更新
          </button>
        </div>
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
        <h3 class="text-sm font-medium text-gray-500 mb-2">承認済み率</h3>
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

    <!-- ランキングセクション -->
    <div class="mt-8">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">🏆 ランキング</h2>
      <div class="grid grid-cols-2 gap-6">
        <!-- 売上金額ランキング -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-bold text-gray-800 mb-4">売上金額TOP10</h3>
          <div id="salesRanking" class="space-y-2">
            <!-- データはJavaScriptで挿入 -->
          </div>
        </div>

        <!-- ポイント獲得ランキング -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-bold text-gray-800 mb-4">⭐ ポイント獲得TOP10</h3>
          <div id="pointsRanking" class="space-y-2">
            <!-- データはJavaScriptで挿入 -->
          </div>
        </div>
      </div>
    </div>
    </main>
  </div>

  <script>
    let currentTab = 'members';
    let currentApprovalFilter = 'approved';
    let currentGraphTab = 'product_sales';
    let salesChart = null;
    let cachedGraphData = null;

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadPerformance();
      setupPeriodFilter();
      initChart();
    });

    // 期間フィルタの切り替え
    function setupPeriodFilter() {
      const periodFilter = document.getElementById('periodFilter');
      const startDateContainer = document.getElementById('startDateContainer');
      const endDateContainer = document.getElementById('endDateContainer');

      periodFilter.addEventListener('change', (e) => {
        if (e.target.value === 'custom') {
          startDateContainer.classList.remove('hidden');
          endDateContainer.classList.remove('hidden');
        } else {
          startDateContainer.classList.add('hidden');
          endDateContainer.classList.add('hidden');
        }
      });
    }

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

    // 実績データ取得
    async function loadPerformance() {
      try {
        const period = document.getElementById('periodFilter').value;
        let url = `/api/performance.php?period=${period}&approval_filter=${currentApprovalFilter}`;

        if (period === 'custom') {
          const startDate = document.getElementById('startDate').value;
          const endDate = document.getElementById('endDate').value;
          if (!startDate || !endDate) {
            alert('開始日と終了日を入力してください。');
            return;
          }
          url += `&start_date=${startDate}&end_date=${endDate}`;
        }

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
          updateSummary(result.summary);
          renderMembersTable(result.members);
          renderProductsTable(result.products);
          cachedGraphData = result.graphs;
          updateChartByTab(currentGraphTab);
          renderRankings(result.rankings);
        } else {
          alert('データの取得に失敗しました。');
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
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

      // データ再読み込み
      loadPerformance();
    }

    // ランキング描画
    function renderRankings(rankings) {
      // 売上金額ランキング
      const salesRanking = document.getElementById('salesRanking');
      salesRanking.innerHTML = '';

      if (rankings.sales.length === 0) {
        salesRanking.innerHTML = '<p class="text-gray-500 text-center py-4">データがありません</p>';
      } else {
        rankings.sales.forEach((member, index) => {
          const div = document.createElement('div');
          div.className = 'flex justify-between items-center p-3 bg-gray-50 rounded';
          div.innerHTML = `
            <div class="flex items-center gap-3">
              <span class="text-lg font-bold ${index < 3 ? 'text-yellow-500' : 'text-gray-500'}">${index + 1}</span>
              <span class="font-medium">${escapeHtml(member.member_name)}</span>
            </div>
            <span class="font-bold text-gray-900">¥${parseFloat(member.total_sales).toLocaleString()}</span>
          `;
          salesRanking.appendChild(div);
        });
      }

      // ポイント獲得ランキング
      const pointsRanking = document.getElementById('pointsRanking');
      pointsRanking.innerHTML = '';

      if (rankings.points.length === 0) {
        pointsRanking.innerHTML = '<p class="text-gray-500 text-center py-4">データがありません</p>';
      } else {
        rankings.points.forEach((member, index) => {
          const div = document.createElement('div');
          div.className = 'flex justify-between items-center p-3 bg-gray-50 rounded';
          div.innerHTML = `
            <div class="flex items-center gap-3">
              <span class="text-lg font-bold ${index < 3 ? 'text-yellow-500' : 'text-gray-500'}">${index + 1}</span>
              <span class="font-medium">${escapeHtml(member.member_name)}</span>
            </div>
            <span class="font-bold text-blue-600">${member.final_points}pt</span>
          `;
          pointsRanking.appendChild(div);
        });
      }
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
