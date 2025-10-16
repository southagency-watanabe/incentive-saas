<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

// 管理者権限チェック
requireAdmin();

// ページタイトルとアクティブページ設定
$page_title = 'ランキングサマリー';
$active_page = 'dashboard';

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
</head>

<body class="bg-gray-50">
  <div class="flex h-screen">
    <?php include __DIR__ . '/../includes/performance/sidebar.php'; ?>

    <!-- メインコンテンツエリア -->
    <main class="flex-1 overflow-y-auto">
      <!-- ページヘッダー -->
      <header class="bg-white shadow-sm border-b">
        <div class="px-8 py-6">
          <h2 class="text-2xl font-bold text-gray-800">🏆 ランキングサマリー</h2>
        </div>
      </header>

      <!-- メインコンテンツ -->
      <div class="px-8 py-8">
        <!-- フィルタエリア -->
        <div class="bg-white rounded-lg shadow mb-6">
          <div class="p-6">
            <div class="flex-1">
              <!-- 期間フィルタ -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">期間</label>
                <div class="flex gap-2 items-center flex-wrap">
                  <input type="date" id="startDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                  <span>〜</span>
                  <input type="date" id="endDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                  <select id="periodPreset" onchange="applyPreset()" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    <option value="today">今日</option>
                    <option value="this_week">今週</option>
                    <option value="this_month" selected>今月</option>
                    <option value="last_month">先月</option>
                    <option value="this_quarter">今四半期</option>
                    <option value="this_year">今年</option>
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
        </div>

        <!-- ランキングセクション -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- 売上金額ランキング -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">💰 売上金額TOP10</h3>
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
    let currentApprovalFilter = 'approved';

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      applyPreset(); // デフォルトで今月を設定
      applyFilters();
    });

    // プリセット適用
    // 日付をローカルタイムゾーンでYYYY-MM-DD形式にフォーマット
    function formatDateLocal(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }

    function applyPreset() {
      const preset = document.getElementById('periodPreset').value;
      const today = new Date();
      let startDate, endDate;

      switch (preset) {
        case 'today':
          startDate = endDate = formatDateLocal(today);
          break;
        case 'this_week':
          const dayOfWeek = today.getDay();
          const monday = new Date(today);
          monday.setDate(today.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));
          startDate = formatDateLocal(monday);
          endDate = formatDateLocal(today);
          break;
        case 'this_month':
          startDate = formatDateLocal(new Date(today.getFullYear(), today.getMonth(), 1));
          endDate = formatDateLocal(new Date(today.getFullYear(), today.getMonth() + 1, 0));
          break;
        case 'last_month':
          startDate = formatDateLocal(new Date(today.getFullYear(), today.getMonth() - 1, 1));
          endDate = formatDateLocal(new Date(today.getFullYear(), today.getMonth(), 0));
          break;
        case 'this_quarter':
          const quarter = Math.floor(today.getMonth() / 3);
          startDate = formatDateLocal(new Date(today.getFullYear(), quarter * 3, 1));
          endDate = formatDateLocal(new Date(today.getFullYear(), (quarter + 1) * 3, 0));
          break;
        case 'this_year':
          startDate = formatDateLocal(new Date(today.getFullYear(), 0, 1));
          endDate = formatDateLocal(new Date(today.getFullYear(), 11, 31));
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
      applyFilters();
    }

    // フィルタ適用
    async function applyFilters() {
      try {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (!startDate || !endDate) {
          alert('開始日と終了日を選択してください。');
          return;
        }

        // 実績管理データ取得
        const url = `/api/performance.php?period=custom&start_date=${startDate}&end_date=${endDate}&approval_filter=${currentApprovalFilter}`;
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
          renderRankings(result.rankings);
        } else {
          alert('データの取得に失敗しました。');
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
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
