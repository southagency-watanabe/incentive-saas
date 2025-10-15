<?php
// $default_period 変数が各ページで定義されていることを前提とする
// 例: $default_period = 'today', 'this_week', 'this_month'
?>
<!-- フィルタエリア -->
<div class="bg-white rounded-lg shadow mb-6">
  <!-- 基本フィルタ（常に表示） -->
  <div class="p-6 pb-3">
    <div class="flex gap-4 items-end">
      <!-- 期間フィルタ -->
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">期間</label>
        <div class="flex gap-2 items-center flex-wrap">
          <input type="date" id="startDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          <span>〜</span>
          <input type="date" id="endDate" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          <select id="periodPreset" onchange="applyPreset()" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <option value="today" <?= ($default_period ?? 'this_month') === 'today' ? 'selected' : '' ?>>今日</option>
            <option value="this_week" <?= ($default_period ?? 'this_month') === 'this_week' ? 'selected' : '' ?>>今週</option>
            <option value="this_month" <?= ($default_period ?? 'this_month') === 'this_month' ? 'selected' : '' ?>>今月</option>
            <option value="last_month" <?= ($default_period ?? 'this_month') === 'last_month' ? 'selected' : '' ?>>先月</option>
            <option value="this_quarter" <?= ($default_period ?? 'this_month') === 'this_quarter' ? 'selected' : '' ?>>今四半期</option>
            <option value="this_year" <?= ($default_period ?? 'this_month') === 'this_year' ? 'selected' : '' ?>>今年</option>
          </select>
        </div>
      </div>

      <!-- 承認フィルタ -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">承認状態</label>
        <div class="flex gap-2">
          <button id="btnApproved" onclick="toggleApprovalFilter('approved')" class="px-4 py-2 rounded bg-blue-600 text-white font-medium">
            承認済みのみ
          </button>
          <button id="btnAll" onclick="toggleApprovalFilter('all')" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
            全データ
          </button>
        </div>
      </div>

      <!-- 適用ボタン -->
      <div>
        <button onclick="applyFilters()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 h-[42px]">
          適用
        </button>
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
          <div id="memberFilters" class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
            <!-- JavaScriptで動的に挿入 -->
          </div>
        </div>

        <!-- チームフィルタ -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">チーム</label>
          <div id="teamFilters" class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
            <!-- JavaScriptで動的に挿入 -->
          </div>
        </div>

        <!-- 商品フィルタ -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">商品</label>
          <div id="productFilters" class="border border-gray-300 rounded-md p-3 max-h-40 overflow-y-auto bg-white">
            <!-- JavaScriptで動的に挿入 -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

