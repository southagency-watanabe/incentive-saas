<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

// 管理者権限チェック
requireAdmin();

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
  <title>ダッシュボード - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
      <a href="/admin/dashboard.php" class="flex items-center px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
        <span class="font-medium">ダッシュボード</span>
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
        <h2 class="text-2xl font-bold text-gray-800">ダッシュボード</h2>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
    <!-- フィルタエリア -->
    <div class="bg-white rounded-lg shadow mb-6">
      <!-- フィルタヘッダー（常に表示） -->
      <div class="p-6 cursor-pointer" onclick="toggleFilterDetails()">
        <div class="flex justify-between items-center">
          <div class="flex-1">
            <div class="flex justify-between items-center mb-2">
              <h2 class="text-lg font-bold text-gray-800">フィルタ</h2>
              <button onclick="event.stopPropagation(); resetFilters()" class="text-sm text-gray-600 hover:text-gray-900">リセット</button>
            </div>
            <!-- 期間フィルタ（常に表示） -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">期間</label>
              <div class="flex gap-2 items-center" onclick="event.stopPropagation()">
                <input type="date" id="startDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                <span>〜</span>
                <input type="date" id="endDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                <select id="periodPreset" onchange="applyPreset()" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                  <option value="">プリセット</option>
                  <option value="today">今日</option>
                  <option value="this_week">今週</option>
                  <option value="this_month" selected>今月</option>
                  <option value="last_month">先月</option>
                  <option value="this_quarter">今四半期</option>
                  <option value="this_year">今年</option>
                  <option value="last_30days">過去30日</option>
                </select>
              </div>
            </div>
          </div>
          <div class="ml-4">
            <svg id="filterArrow" class="w-6 h-6 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- フィルタ詳細（開閉可能） -->
      <div id="filterDetails" class="hidden">
        <div class="p-6 pt-4">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- テキスト検索 -->
            <div class="col-span-full">
              <label class="block text-sm font-medium text-gray-700 mb-2">テキスト検索</label>
              <input type="text" id="searchText" placeholder="商品名、メンバー名で検索..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- メンバーフィルタ -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">メンバー</label>
              <div class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
                <?php foreach ($members as $member): ?>
                  <label class="flex items-center space-x-2 mb-1">
                    <input type="checkbox" name="member_ids[]" value="<?= htmlspecialchars($member['member_id']) ?>" class="rounded">
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
                    <input type="checkbox" name="team_ids[]" value="<?= htmlspecialchars($team['team_id']) ?>" class="rounded">
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
                    <input type="checkbox" name="product_ids[]" value="<?= htmlspecialchars($product['product_id']) ?>" class="rounded">
                    <span class="text-sm"><?= htmlspecialchars($product['product_name']) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="mt-4 flex justify-end">
            <button onclick="applyFilters()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
              適用
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- スコアカード -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <!-- 売上金額 -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">商品別売上金額</h3>
        <div class="text-3xl font-bold text-gray-900 mb-2" id="salesAmount">¥0</div>
        <div id="salesDiff" class="text-sm">
          <span class="font-medium">-</span>
          <span class="text-gray-500">対前期間</span>
        </div>
      </div>

      <!-- 売上件数 -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">商品別売上件数</h3>
        <div class="text-3xl font-bold text-gray-900 mb-2" id="salesCount">0件</div>
        <div id="countDiff" class="text-sm">
          <span class="font-medium">-</span>
          <span class="text-gray-500">対前期間</span>
        </div>
      </div>

      <!-- 粗利益 -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-sm font-medium text-gray-500 mb-2">商品別粗利益</h3>
        <div class="text-3xl font-bold text-gray-900 mb-2" id="profitAmount">¥0</div>
        <div id="profitDiff" class="text-sm">
          <span class="font-medium">-</span>
          <span class="text-gray-500">対前期間</span>
        </div>
      </div>
    </div>

    <!-- 売上推移グラフ -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-800">売上推移</h3>
        <select id="granularity" onchange="applyFilters()" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          <option value="daily">日別</option>
          <option value="weekly">週別</option>
          <option value="monthly" selected>月別</option>
          <option value="quarterly">四半期</option>
          <option value="yearly">年間</option>
        </select>
      </div>
      <canvas id="trendChart" height="80"></canvas>
    </div>

    <!-- 商品別売上/粗利テーブル -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="p-6 border-b">
        <h3 class="text-lg font-bold text-gray-800">商品別売上/粗利</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortTable('product_name')">
                商品名 <span id="sort_product_name"></span>
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortTable('sales')">
                売上金額 <span id="sort_sales"></span>
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortTable('profit')">
                粗利益 <span id="sort_profit"></span>
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortTable('quantity')">
                数量 <span id="sort_quantity"></span>
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortTable('count')">
                件数 <span id="sort_count"></span>
              </th>
            </tr>
          </thead>
          <tbody id="productTableBody" class="bg-white divide-y divide-gray-200">
            <!-- データはJavaScriptで挿入 -->
          </tbody>
        </table>
      </div>
    </div>
    </main>
  </div>

  <script>
    let currentData = null;
    let trendChart = null;
    let currentSort = {
      column: 'sales',
      direction: 'desc'
    };
    let filterDetailsOpen = false;

    // 初期化
    document.addEventListener('DOMContentLoaded', () => {
      applyPreset(); // デフォルトで今月を設定
      applyFilters();
    });

    // フィルタ詳細の開閉
    function toggleFilterDetails() {
      const details = document.getElementById('filterDetails');
      const arrow = document.getElementById('filterArrow');

      filterDetailsOpen = !filterDetailsOpen;

      if (filterDetailsOpen) {
        details.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
      } else {
        details.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
      }
    }

    // プリセット適用
    function applyPreset() {
      const preset = document.getElementById('periodPreset').value;
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
        case 'last_30days':
          const past30 = new Date(today);
          past30.setDate(today.getDate() - 30);
          startDate = past30.toISOString().split('T')[0];
          endDate = today.toISOString().split('T')[0];
          break;
        default:
          return;
      }

      document.getElementById('startDate').value = startDate;
      document.getElementById('endDate').value = endDate;
    }

    // フィルタリセット
    function resetFilters() {
      document.getElementById('periodPreset').value = 'this_month';
      applyPreset();
      document.getElementById('searchText').value = '';
      document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
      applyFilters();
    }

    // フィルタ適用
    async function applyFilters() {
      try {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const searchText = document.getElementById('searchText').value;
        const granularity = document.getElementById('granularity').value;

        const memberIds = Array.from(document.querySelectorAll('input[name="member_ids[]"]:checked'))
          .map(cb => cb.value).join(',');
        const teamIds = Array.from(document.querySelectorAll('input[name="team_ids[]"]:checked'))
          .map(cb => cb.value).join(',');
        const productIds = Array.from(document.querySelectorAll('input[name="product_ids[]"]:checked'))
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
          currentData = result;
          updateScoreCards(result.score_cards);
          updateTrendChart(result.trend);
          updateProductTable(result.products);
        } else {
          alert('データの取得に失敗しました。');
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // スコアカード更新
    function updateScoreCards(scoreCards) {
      // 売上金額
      document.getElementById('salesAmount').textContent = '¥' + scoreCards.sales.current.toLocaleString();
      document.getElementById('salesDiff').innerHTML = formatDiff(scoreCards.sales.diff, scoreCards.sales.diff_percent);

      // 売上件数
      document.getElementById('salesCount').textContent = scoreCards.count.current.toLocaleString() + '件';
      document.getElementById('countDiff').innerHTML = formatDiff(scoreCards.count.diff, scoreCards.count.diff_percent);

      // 粗利益
      document.getElementById('profitAmount').textContent = '¥' + scoreCards.profit.current.toLocaleString();
      document.getElementById('profitDiff').innerHTML = formatDiff(scoreCards.profit.diff, scoreCards.profit.diff_percent);
    }

    // 差分フォーマット
    function formatDiff(diff, percent) {
      const sign = diff >= 0 ? '+' : '';
      const color = diff >= 0 ? 'text-green-600' : 'text-red-600';
      const percentStr = percent.toFixed(1) + '%';
      const diffStr = '¥' + Math.abs(diff).toLocaleString();
      return `<span class="font-medium ${color}">${sign}${percentStr} (${sign}${diffStr})</span> <span class="text-gray-500">対前期間</span>`;
    }

    // グラフ更新
    function updateTrendChart(trendData) {
      const ctx = document.getElementById('trendChart').getContext('2d');

      if (trendChart) {
        trendChart.destroy();
      }

      trendChart = new Chart(ctx, {
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
    function updateProductTable(products) {
      const tbody = document.getElementById('productTableBody');
      tbody.innerHTML = '';

      if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      // ソート適用
      const sortedProducts = sortData(products);

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

      updateSortIndicators();
    }

    // テーブルソート
    function sortTable(column) {
      if (currentSort.column === column) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
      } else {
        currentSort.column = column;
        currentSort.direction = 'desc';
      }

      if (currentData) {
        updateProductTable(currentData.products);
      }
    }

    // データソート
    function sortData(data) {
      const sorted = [...data];
      sorted.sort((a, b) => {
        let aVal = a[currentSort.column];
        let bVal = b[currentSort.column];

        // 数値の場合は数値として比較
        if (typeof aVal === 'string' && !isNaN(parseFloat(aVal))) {
          aVal = parseFloat(aVal);
          bVal = parseFloat(bVal);
        }

        if (currentSort.direction === 'asc') {
          return aVal > bVal ? 1 : -1;
        } else {
          return aVal < bVal ? 1 : -1;
        }
      });
      return sorted;
    }

    // ソートインジケーター更新
    function updateSortIndicators() {
      ['product_name', 'sales', 'profit', 'quantity', 'count'].forEach(col => {
        const indicator = document.getElementById(`sort_${col}`);
        if (currentSort.column === col) {
          indicator.textContent = currentSort.direction === 'asc' ? '▲' : '▼';
        } else {
          indicator.textContent = '';
        }
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
