<!-- データテーブル（タブ切り替え: メンバー別/チーム別のみ） -->
<!-- タブ -->
<div class="bg-white border-b rounded-t-lg">
  <div class="flex space-x-6 px-4">
    <button id="tabMembers" onclick="switchDataTab('members')" class="py-3 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">
      メンバー別実績
    </button>
    <button id="tabTeams" onclick="switchDataTab('teams')" class="py-3 px-2 text-gray-600 hover:text-gray-900">
      チーム別実績
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
          <span id="sortIndicatorTotalSales" class="ml-1">▼</span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortMembersTable('total_profit')">
          粗利
          <span id="sortIndicatorTotalProfit" class="ml-1"></span>
        </th>
      </tr>
    </thead>
    <tbody id="membersTableBody" class="bg-white divide-y divide-gray-200">
      <!-- データはJavaScriptで挿入 -->
    </tbody>
  </table>
</div>

<!-- チーム別実績タブ -->
<div id="teamsTab" class="hidden bg-white rounded-b-lg shadow overflow-x-auto">
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortTeamsTable('team_name')">
          チーム
          <span id="sortIndicatorTeamNameTable" class="ml-1"></span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortTeamsTable('sales_count')">
          売上件数
          <span id="sortIndicatorTeamSalesCount" class="ml-1"></span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortTeamsTable('total_sales')">
          売上金額
          <span id="sortIndicatorTeamTotalSales" class="ml-1">▼</span>
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100" onclick="sortTeamsTable('total_profit')">
          粗利
          <span id="sortIndicatorTeamTotalProfit" class="ml-1"></span>
        </th>
      </tr>
    </thead>
    <tbody id="teamsTableBody" class="bg-white divide-y divide-gray-200">
      <!-- データはJavaScriptで挿入 -->
    </tbody>
  </table>
</div>
