<!-- データテーブル（商品別実績のみ） -->
<div class="bg-white rounded-lg shadow overflow-x-auto">
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortProductsTable('product_name')">
          商品名
          <span id="sortIndicatorProductName" class="ml-1"></span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortProductsTable('avg_price')">
          単価
          <span id="sortIndicatorAvgPrice" class="ml-1"></span>
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
      </tr>
    </thead>
    <tbody id="productsTableBody" class="bg-white divide-y divide-gray-200">
      <!-- データはJavaScriptで挿入 -->
    </tbody>
  </table>
</div>
