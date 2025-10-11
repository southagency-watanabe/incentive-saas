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
  <title>ランキングサマリー - インセンティブSaaS</title>
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
      <a href="/admin/dashboard.php" class="flex items-center px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
        <span class="font-medium">ランキングサマリー</span>
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
        <h2 class="text-2xl font-bold text-gray-800">ランキングサマリー</h2>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
    <!-- フィルタエリア -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="p-6 pb-3">
        <div class="flex-1">
            <!-- 期間フィルタ -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">期間</label>
              <div class="flex gap-2 items-center flex-wrap">
                <input type="date" id="startDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                <span>〜</span>
                <input type="date" id="endDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                <select id="periodPreset" onchange="applyPreset()" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                  <option value="">プリセット選択</option>
                  <option value="today">今日</option>
                  <option value="this_week">今週</option>
                  <option value="this_month" selected>今月</option>
                  <option value="last_month">先月</option>
                  <option value="this_quarter">今四半期</option>
                  <option value="this_year">今年</option>
                  <option value="last_30days">過去30日</option>
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
        </div>
      </div>
    </div>

    <!-- ランキングセクション -->
    <div class="mt-8">
      <div class="flex items-center gap-4 mb-6">
        <h2 class="text-2xl font-bold text-gray-800">🏆 ランキング TOP10</h2>
        <!-- タブ切り替え -->
        <div class="flex gap-2">
          <button id="tabMember" onclick="switchRankingTab('member')" class="px-4 py-2 rounded bg-blue-600 text-white font-medium">
            個人
          </button>
          <button id="tabTeam" onclick="switchRankingTab('team')" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
            チーム
          </button>
        </div>
      </div>

      <!-- ランキングテーブル -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">順位</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">名前</th>
                <th id="teamColumnHeader" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">チーム</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">売上金額</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">売上比較</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ポイント</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">ポイント比較</th>
              </tr>
            </thead>
            <tbody id="rankingTableBody" class="bg-white divide-y divide-gray-200">
              <tr>
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">データを読み込み中...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    </main>
  </div>

  <script>
    let filterDetailsOpen = false;
    let currentRankingTab = 'member'; // 'member' or 'team'
    let cachedRankingsData = null;

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

        if (!startDate || !endDate) {
          alert('開始日と終了日を選択してください。');
          return;
        }

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
          granularity: 'monthly'
        });

        if (memberIds) params.append('member_ids', memberIds);
        if (teamIds) params.append('team_ids', teamIds);
        if (productIds) params.append('product_ids', productIds);

        const response = await fetch(`/api/dashboard.php?${params}`);
        const result = await response.json();

        if (result.success) {
          if (result.rankings) {
            cachedRankingsData = result.rankings;
            updateRankings(result.rankings);
          }
        } else {
          alert('データの取得に失敗しました。');
        }
      } catch (error) {
        console.error('Error in applyFilters:', error);
        alert('エラーが発生しました。');
      }
    }

    // ランキングタブ切り替え
    function switchRankingTab(tab) {
      currentRankingTab = tab;

      // タブボタンのスタイル更新
      const tabMember = document.getElementById('tabMember');
      const tabTeam = document.getElementById('tabTeam');

      if (tab === 'member') {
        tabMember.className = 'px-4 py-2 rounded bg-blue-600 text-white font-medium';
        tabTeam.className = 'px-4 py-2 rounded bg-gray-200 text-gray-700';
      } else {
        tabMember.className = 'px-4 py-2 rounded bg-gray-200 text-gray-700';
        tabTeam.className = 'px-4 py-2 rounded bg-blue-600 text-white font-medium';
      }

      // ランキング再描画
      if (cachedRankingsData) {
        updateRankings(cachedRankingsData);
      }
    }

    // ランキング更新
    function updateRankings(rankings) {
      const tbody = document.getElementById('rankingTableBody');
      const teamHeader = document.getElementById('teamColumnHeader');
      tbody.innerHTML = '';

      // タブに応じてデータを切り替え
      const rankingData = currentRankingTab === 'member' ? rankings.members : rankings.teams;
      const nameField = currentRankingTab === 'member' ? 'member_name' : 'team_name';

      // チーム列の表示制御
      if (currentRankingTab === 'team') {
        teamHeader.style.display = 'none';
      } else {
        teamHeader.style.display = '';
      }

      if (rankingData && rankingData.length > 0) {
        rankingData.forEach((item, index) => {
          const rank = index + 1;
          const rankClass = rank <= 3 ? 'text-yellow-500 font-bold text-lg' : 'text-gray-700';
          const rankMedal = rank === 1 ? '🥇' : rank === 2 ? '🥈' : rank === 3 ? '🥉' : '';

          // 売上比較の表示
          const salesDiffPercent = item.sales_diff_percent;
          const salesDiffAbs = item.sales_diff;
          const salesDiffColor = salesDiffAbs >= 0 ? 'text-green-600' : 'text-red-600';
          const salesDiffSign = salesDiffAbs >= 0 ? '+' : '';
          const salesDiffHtml = `
            <div class="${salesDiffColor}">
              <div class="font-medium">${salesDiffSign}${salesDiffPercent.toFixed(1)}%</div>
              <div class="text-xs">(${salesDiffSign}¥${Math.abs(salesDiffAbs).toLocaleString()})</div>
            </div>
          `;

          // ポイント比較の表示
          const pointsDiffPercent = item.points_diff_percent;
          const pointsDiffAbs = item.points_diff;
          const pointsDiffColor = pointsDiffAbs >= 0 ? 'text-green-600' : 'text-red-600';
          const pointsDiffSign = pointsDiffAbs >= 0 ? '+' : '';
          const pointsDiffHtml = `
            <div class="${pointsDiffColor}">
              <div class="font-medium">${pointsDiffSign}${pointsDiffPercent.toFixed(1)}%</div>
              <div class="text-xs">(${pointsDiffSign}${Math.abs(pointsDiffAbs).toLocaleString()}pt)</div>
            </div>
          `;

          const tr = document.createElement('tr');
          tr.className = rank <= 3 ? 'bg-yellow-50' : '';

          // チーム列は個人ランキングの時だけ表示
          const teamCell = currentRankingTab === 'member'
            ? `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(item.team_name || '-')}</td>`
            : '';

          tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span class="${rankClass}">${rankMedal} ${rank}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              ${escapeHtml(item[nameField])}
            </td>
            ${teamCell}
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
              ¥${parseFloat(item.total_sales).toLocaleString()}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
              ${salesDiffHtml}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 text-right font-medium">
              ${parseFloat(item.total_points).toLocaleString()}pt
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
              ${pointsDiffHtml}
            </td>
          `;
          tbody.appendChild(tr);
        });
      } else {
        const colspan = currentRankingTab === 'member' ? '7' : '6';
        tbody.innerHTML = `<tr><td colspan="${colspan}" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>`;
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
