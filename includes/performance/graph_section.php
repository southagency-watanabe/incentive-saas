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
      <button id="graphTabTeamSales" onclick="switchGraphTab('team_sales')" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
        チーム別売上
      </button>
    </div>
    <div class="flex gap-2">
      <button id="btnGraphApproved" onclick="toggleGraphApprovalFilter('approved')" class="px-4 py-2 rounded bg-blue-600 text-white font-medium">
        承認済みのみ
      </button>
      <button id="btnGraphAll" onclick="toggleGraphApprovalFilter('all')" class="px-4 py-2 rounded bg-gray-200 text-gray-700">
        全データ
      </button>
    </div>
  </div>
  <div class="h-96">
    <canvas id="salesChart"></canvas>
  </div>
</div>

