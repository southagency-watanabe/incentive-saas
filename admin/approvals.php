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

<body class="bg-gray-100 min-h-screen">
  <!-- ヘッダー -->
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">インセンティブSaaS</h1>
        <p class="text-sm text-gray-600">承認管理</p>
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
        <a href="/admin/masters/events.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">マスタ管理</a>
        <a href="/admin/sales/input.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">売上管理</a>
        <a href="/admin/approvals.php" class="py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">承認管理</a>
        <a href="/admin/performance.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">実績管理</a>
        <a href="/admin/bulletins.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">掲示板管理</a>
        <a href="/admin/ranking.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">ランキング</a>
      </div>
    </div>
  </nav>

  <!-- タブ -->
  <div class="bg-gray-50 border-b">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex space-x-6">
        <button id="tabSales" onclick="switchTab('sales')" class="py-3 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">
          売上承認
        </button>
        <button id="tabActions" onclick="switchTab('actions')" class="py-3 px-2 text-gray-600 hover:text-gray-900">
          アクション承認
        </button>
        <button id="tabTasks" onclick="switchTab('tasks')" class="py-3 px-2 text-gray-600 hover:text-gray-900">
          タスク承認
        </button>
      </div>
    </div>
  </div>

  <!-- メインコンテンツ -->
  <main class="max-w-7xl mx-auto px-4 py-8">
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">日付</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">メンバー</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">商品</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">数量</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">単価</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">金額</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">付与pt</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">承認状態</th>
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
    let currentTab = 'sales';

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadSales();
    });

    // タブ切り替え
    function switchTab(tab) {
      currentTab = tab;

      // タブボタンのスタイル更新
      document.getElementById('tabSales').classList.toggle('border-blue-500', tab === 'sales');
      document.getElementById('tabSales').classList.toggle('text-blue-600', tab === 'sales');
      document.getElementById('tabSales').classList.toggle('font-medium', tab === 'sales');
      document.getElementById('tabSales').classList.toggle('text-gray-600', tab !== 'sales');

      document.getElementById('tabActions').classList.toggle('border-blue-500', tab === 'actions');
      document.getElementById('tabActions').classList.toggle('text-blue-600', tab === 'actions');
      document.getElementById('tabActions').classList.toggle('font-medium', tab === 'actions');
      document.getElementById('tabActions').classList.toggle('text-gray-600', tab !== 'actions');

      document.getElementById('tabTasks').classList.toggle('border-blue-500', tab === 'tasks');
      document.getElementById('tabTasks').classList.toggle('text-blue-600', tab === 'tasks');
      document.getElementById('tabTasks').classList.toggle('font-medium', tab === 'tasks');
      document.getElementById('tabTasks').classList.toggle('text-gray-600', tab !== 'tasks');

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
          renderSalesTable(result.data);
        } else {
          alert('データの取得に失敗しました。');
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
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