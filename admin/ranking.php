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
  <title>ランキング - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
  <!-- ヘッダー -->
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">インセンティブSaaS</h1>
        <p class="text-sm text-gray-600">ランキング</p>
      </div>
      <div class="flex items-center gap-4">
        <span class="text-gray-700"><?= htmlspecialchars($_SESSION['name']) ?> さん</span>
        <a href="/api/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">ログアウト</a>
      </div>
    </div>
  </header>

  <!-- ナビゲーション -->
  <nav class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex space-x-8">
        <a href="/admin/dashboard.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">ダッシュボード</a>
        <a href="/admin/masters/members.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">マスタ管理</a>
        <a href="/admin/sales/input.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">売上管理</a>
        <a href="/admin/approvals.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">承認管理</a>
        <a href="/admin/performance.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">実績管理</a>
        <a href="/admin/bulletins.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">掲示板管理</a>
        <a href="/admin/ranking.php" class="py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">ランキング</a>
      </div>
    </div>
  </nav>

  <!-- メインコンテンツ -->
  <main class="max-w-7xl mx-auto px-4 py-8">
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
          <button onclick="loadRanking()" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            更新
          </button>
        </div>
      </div>
    </div>

    <!-- ランキング表示 -->
    <div class="grid grid-cols-2 gap-6">
      <!-- 売上金額ランキング -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">🏆 売上金額TOP10</h3>
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
  </main>

  <script>
    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadRanking();
      setupPeriodFilter();
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

    // ランキングデータ取得
    async function loadRanking() {
      try {
        const period = document.getElementById('periodFilter').value;
        let url = `/api/performance.php?period=${period}`;

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
