<!-- データテーブル（タブ切り替え） -->
<!-- タブ -->
<div class="bg-white border-b rounded-t-lg">
  <div class="flex space-x-6 px-4">
    <button id="tabMembers" onclick="switchDataTab('members')" class="py-3 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">
      メンバー別実績
    </button>
    <button id="tabProducts" onclick="switchDataTab('products')" class="py-3 px-2 text-gray-600 hover:text-gray-900">
      商品別実績
    </button>
  </div>
</div>

<!-- メンバー別実績タブ -->
<div id="membersTab" class="bg-white rounded-b-lg shadow overflow-x-auto">
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortMembersTable('member_name')">
          メンバー
          <span id="sortIndicatorMemberName" class="ml-1"></span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortMembersTable('team_name')">
          チーム
          <span id="sortIndicatorTeamName" class="ml-1"></span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortMembersTable('sales_count')">
          売上件数
          <span id="sortIndicatorSalesCount" class="ml-1"></span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortMembersTable('total_sales')">
          売上金額
          <span id="sortIndicatorTotalSales" class="ml-1"></span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortMembersTable('final_points')">
          付与ポイント
          <span id="sortIndicatorFinalPoints" class="ml-1">▼</span>
        </th>
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
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortProductsTable('product_name')">
          商品名
          <span id="sortIndicatorProductName" class="ml-1"></span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortProductsTable('total_quantity')">
          販売数量
          <span id="sortIndicatorTotalQuantity" class="ml-1"></span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortProductsTable('total_sales')">
          売上金額
          <span id="sortIndicatorProductTotalSales" class="ml-1">▼</span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortProductsTable('total_profit')">
          粗利
          <span id="sortIndicatorTotalProfit" class="ml-1"></span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortProductsTable('total_points')">
          付与ポイント
          <span id="sortIndicatorTotalPoints" class="ml-1"></span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortProductsTable('avg_price')">
          平均単価
          <span id="sortIndicatorAvgPrice" class="ml-1"></span>
        </th>
      </tr>
    </thead>
    <tbody id="productsTableBody" class="bg-white divide-y divide-gray-200">
      <!-- データはJavaScriptで挿入 -->
    </tbody>
  </table>
</div>

