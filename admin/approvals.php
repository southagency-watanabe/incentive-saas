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
  <title>承認管理 - インセンティブSaaS</title>
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
        <button onclick="toggleApprovalMenu()" class="w-full flex items-center justify-between px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
          <span class="font-medium">承認管理</span>
          <svg id="approvalArrow" class="w-4 h-4 transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div id="approvalSubmenu" class="bg-gray-50">
          <a href="/admin/approvals.php?tab=sales" class="flex items-center px-6 py-2 pl-12 text-sm text-blue-600 font-medium bg-blue-50 hover:bg-blue-100">
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
        <h2 class="text-2xl font-bold text-gray-800" id="pageTitle">売上承認</h2>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
    <!-- 売上承認タブ -->
    <div id="salesTab">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">売上承認待ち一覧</h2>
        <button onclick="loadSales()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center gap-2">
          <span>🔄</span>
          <span>更新</span>
        </button>
      </div>

      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th onclick="sortTable('date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
                日付 <span id="sort-date"></span>
              </th>
              <th onclick="sortTable('member_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
                メンバー <span id="sort-member_name"></span>
              </th>
              <th onclick="sortTable('product_name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
                商品 <span id="sort-product_name"></span>
              </th>
              <th onclick="sortTable('quantity')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
                数量 <span id="sort-quantity"></span>
              </th>
              <th onclick="sortTable('unit_price')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
                単価 <span id="sort-unit_price"></span>
              </th>
              <th onclick="sortTable('amount')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
                金額 <span id="sort-amount"></span>
              </th>
              <th onclick="sortTable('final_point')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
                付与pt <span id="sort-final_point"></span>
              </th>
              <th onclick="sortTable('approval_status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">
                承認状態 <span id="sort-approval_status"></span>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
            </tr>
          </thead>
          <tbody id="salesTableBody" class="bg-white divide-y divide-gray-200">
            <!-- データはJavaScriptで挿入 -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- アクション承認タブ -->
    <div id="actionsTab" class="hidden">
      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-500">アクション承認機能は実装予定です。</p>
      </div>
    </div>

    <!-- タスク承認タブ -->
    <div id="tasksTab" class="hidden">
      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-500">タスク承認機能は実装予定です。</p>
      </div>
    </div>
    </main>
  </div>

  <!-- 却下モーダル -->
  <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold">却下理由入力</h3>
        <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
      </div>

      <form id="rejectForm">
        <input type="hidden" id="rejectSaleId">

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">却下理由 <span class="text-red-500">*</span></label>
          <textarea id="rejectReason" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex justify-end gap-3">
          <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            キャンセル
          </button>
          <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
            却下する
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // マスタ管理ドロップダウンの開閉
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

    let currentTab = 'sales';
    let currentSalesData = [];
    let currentSortKey = 'date';
    let currentSortOrder = 'desc';

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      // URLパラメータからタブを取得
      const urlParams = new URLSearchParams(window.location.search);
      const tabParam = urlParams.get('tab');
      if (tabParam) {
        currentTab = tabParam;
      }

      // タブに応じたコンテンツを表示
      switchTab(currentTab);
    });

    // タブ切り替え
    function switchTab(tab) {
      currentTab = tab;

      // ページタイトル更新
      const pageTitle = document.getElementById('pageTitle');
      if (tab === 'sales') {
        pageTitle.textContent = '売上承認';
      } else if (tab === 'actions') {
        pageTitle.textContent = 'アクション承認';
      } else if (tab === 'tasks') {
        pageTitle.textContent = 'タスク承認';
      }

      // コンテンツ表示切り替え
      document.getElementById('salesTab').classList.toggle('hidden', tab !== 'sales');
      document.getElementById('actionsTab').classList.toggle('hidden', tab !== 'actions');
      document.getElementById('tasksTab').classList.toggle('hidden', tab !== 'tasks');

      // データ読み込み
      if (tab === 'sales') {
        loadSales();
      }
    }

    // 売上承認待ち一覧取得
    async function loadSales() {
      try {
        const response = await fetch('/api/sales.php?filter=pending');
        const result = await response.json();

        if (result.success) {
          currentSalesData = result.data;
          applySortAndRender();
        } else {
          alert('データの取得に失敗しました。');
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // ソート機能
    function sortTable(key) {
      // 同じカラムをクリックした場合は昇順/降順を切り替え
      if (currentSortKey === key) {
        currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        currentSortKey = key;
        currentSortOrder = 'asc';
      }

      applySortAndRender();
    }

    // ソート適用とレンダリング
    function applySortAndRender() {
      // データのソート
      const sortedData = [...currentSalesData].sort((a, b) => {
        let valA, valB;

        switch (currentSortKey) {
          case 'date':
            valA = a.date;
            valB = b.date;
            break;
          case 'member_name':
            valA = a.member_name;
            valB = b.member_name;
            break;
          case 'product_name':
            valA = a.product_name;
            valB = b.product_name;
            break;
          case 'quantity':
            valA = parseFloat(a.quantity);
            valB = parseFloat(b.quantity);
            break;
          case 'unit_price':
            valA = parseFloat(a.unit_price);
            valB = parseFloat(b.unit_price);
            break;
          case 'amount':
            valA = parseFloat(a.quantity) * parseFloat(a.unit_price);
            valB = parseFloat(b.quantity) * parseFloat(b.unit_price);
            break;
          case 'final_point':
            valA = parseInt(a.final_point);
            valB = parseInt(b.final_point);
            break;
          case 'approval_status':
            valA = a.approval_status;
            valB = b.approval_status;
            break;
          default:
            return 0;
        }

        // 比較
        let result = 0;
        if (typeof valA === 'string') {
          result = valA.localeCompare(valB);
        } else {
          result = valA - valB;
        }

        return currentSortOrder === 'asc' ? result : -result;
      });

      // ソートインジケーター更新
      document.querySelectorAll('[id^="sort-"]').forEach(el => el.textContent = '');
      const indicator = document.getElementById(`sort-${currentSortKey}`);
      if (indicator) {
        indicator.textContent = currentSortOrder === 'asc' ? '▲' : '▼';
      }

      // テーブルレンダリング
      renderSalesTable(sortedData);
    }

    // テーブル描画
    function renderSalesTable(sales) {
      const tbody = document.getElementById('salesTableBody');
      tbody.innerHTML = '';

      if (sales.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-4 text-center text-gray-500">承認待ちの売上はありません</td></tr>';
        return;
      }

      sales.forEach(sale => {
        const amount = sale.quantity * sale.unit_price;

        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(sale.date)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(sale.member_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(sale.product_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${sale.quantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(sale.unit_price).toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${amount.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${sale.final_point}pt</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                            ${escapeHtml(sale.approval_status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <button onclick='approveSale(${sale.id})' class="text-green-600 hover:text-green-900">承認</button>
                        <button onclick='openRejectModal(${sale.id})' class="text-red-600 hover:text-red-900">却下</button>
                    </td>
                `;
        tbody.appendChild(tr);
      });
    }

    // 承認
    async function approveSale(saleId) {
      if (!confirm('この売上を承認しますか？')) {
        return;
      }

      try {
        const response = await fetch(`/api/sales/approve.php?id=${saleId}`, {
          method: 'PUT'
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadSales();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // 却下モーダルを開く
    function openRejectModal(saleId) {
      document.getElementById('rejectSaleId').value = saleId;
      document.getElementById('rejectReason').value = '';
      document.getElementById('rejectModal').classList.remove('hidden');
    }

    // 却下モーダルを閉じる
    function closeRejectModal() {
      document.getElementById('rejectModal').classList.add('hidden');
    }

    // 却下フォーム送信
    document.getElementById('rejectForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const saleId = document.getElementById('rejectSaleId').value;
      const rejectReason = document.getElementById('rejectReason').value;

      try {
        const response = await fetch(`/api/sales/reject.php?id=${saleId}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            reject_reason: rejectReason
          })
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          closeRejectModal();
          loadSales();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    });

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