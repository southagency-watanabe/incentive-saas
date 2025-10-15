<?php
// $active_page 変数が各ページで定義されていることを前提とする
// 例: $active_page = 'daily', 'weekly', 'monthly', 'dayofweek'
?>
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

    <!-- 実績管理ドロップダウン -->
    <div>
      <button onclick="togglePerformanceMenu()" class="w-full flex items-center justify-between px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
        <span class="font-medium">実績管理</span>
        <svg id="performanceArrow" class="w-4 h-4 transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>
      <div id="performanceSubmenu" class="bg-gray-50">
        <a href="/admin/performance/daily.php" class="flex items-center px-6 py-2 pl-12 text-sm <?= $active_page === 'daily' ? 'text-blue-600 bg-blue-100' : 'text-gray-700 hover:bg-gray-200' ?>">
          <span>日別</span>
        </a>
        <a href="/admin/performance/weekly.php" class="flex items-center px-6 py-2 pl-12 text-sm <?= $active_page === 'weekly' ? 'text-blue-600 bg-blue-100' : 'text-gray-700 hover:bg-gray-200' ?>">
          <span>週別</span>
        </a>
        <a href="/admin/performance/monthly.php" class="flex items-center px-6 py-2 pl-12 text-sm <?= $active_page === 'monthly' ? 'text-blue-600 bg-blue-100' : 'text-gray-700 hover:bg-gray-200' ?>">
          <span>月別</span>
        </a>
        <a href="/admin/performance/dayofweek.php" class="flex items-center px-6 py-2 pl-12 text-sm <?= $active_page === 'dayofweek' ? 'text-blue-600 bg-blue-100' : 'text-gray-700 hover:bg-gray-200' ?>">
          <span>曜日別</span>
        </a>
      </div>
    </div>

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

