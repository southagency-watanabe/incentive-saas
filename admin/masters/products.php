<?php
require_once __DIR__ . '/../../includes/session.php';

// 管理者権限チェック
requireAdmin();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>商品マスタ - インセンティブSaaS</title>
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
        <button onclick="toggleMasterMenu()" class="w-full flex items-center justify-between px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
          <span class="font-medium">マスタ管理</span>
          <svg id="masterArrow" class="w-4 h-4 transition-transform duration-200 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        <div id="masterSubmenu" class="bg-gray-50">
          <a href="/admin/masters/members.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>メンバー</span>
          </a>
          <a href="/admin/masters/teams.php" class="flex items-center px-6 py-2 pl-12 text-sm text-gray-700 hover:bg-gray-200">
            <span>チーム</span>
          </a>
          <a href="/admin/masters/products.php" class="flex items-center px-6 py-2 pl-12 text-sm text-blue-600 font-medium bg-blue-50 hover:bg-blue-100">
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

      <a href="/admin/performance.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 border-l-4 border-transparent hover:border-gray-300">
        <span>実績管理</span>
      </a>
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

  <!-- メインコンテンツエリア -->
  <div class="flex-1 overflow-y-auto">
    <!-- ページヘッダー -->
    <header class="bg-white shadow-sm border-b">
      <div class="px-8 py-6">
        <h2 class="text-2xl font-bold text-gray-800">商品マスタ</h2>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
      <!-- ヘッダーアクション -->
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">商品一覧</h3>
      <div class="flex gap-3">
        <button id="refreshBtn" onclick="refreshList()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center gap-2">
          <span id="refreshIcon">🔄</span>
          <span>更新</span>
        </button>
        <button onclick="openModal('create')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          新規登録
        </button>
      </div>
    </div>

    <!-- テーブル -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="product_id" data-type="string">
              商品ID <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="product_name" data-type="string">
              商品名 <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="large_category" data-type="string">
              大カテゴリ <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="medium_category" data-type="string">
              中カテゴリ <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="small_category" data-type="string">
              小カテゴリ <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="point" data-type="number">
              付与pt <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="price" data-type="number">
              売価 <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="cost" data-type="number">
              原価 <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="profit" data-type="number">
              粗利 <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="status" data-type="string">
              ステータス <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap cursor-pointer hover:bg-gray-100" data-sort="approval_required" data-type="string">
              承認要否 <span class="sort-icon">⇅</span>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">操作</th>
          </tr>
        </thead>
        <tbody id="productTableBody" class="bg-white divide-y divide-gray-200">
          <!-- データはJavaScriptで挿入 -->
        </tbody>
      </table>
    </div>
  </main>

  <!-- モーダル -->
  <div id="modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 id="modalTitle" class="text-xl font-bold">商品登録</h3>
        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
      </div>

      <form id="productForm" class="space-y-4">
        <input type="hidden" id="productId" name="product_id">

        <div class="grid grid-cols-2 gap-4">
          <!-- 商品名 -->
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">商品名 <span class="text-red-500">*</span></label>
            <input type="text" id="productName" name="product_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 大分類 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">大分類</label>
            <input type="text" id="largeCategory" name="large_category" placeholder="例：飲食" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 中分類 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">中分類</label>
            <input type="text" id="mediumCategory" name="medium_category" placeholder="例：麺類" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 小分類 -->
          <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">小分類</label>
            <input type="text" id="smallCategory" name="small_category" placeholder="例：たれそば" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 付与pt -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">付与pt <span class="text-red-500">*</span></label>
            <input type="number" id="point" name="point" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 売価 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">売価 <span class="text-red-500">*</span></label>
            <input type="number" id="price" name="price" min="0" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 原価 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">原価</label>
            <input type="number" id="cost" name="cost" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 承認要否 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">承認要否 <span class="text-red-500">*</span></label>
            <select id="approvalRequired" name="approval_required" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="必要">必要</option>
              <option value="不要">不要</option>
            </select>
          </div>

          <!-- ステータス -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ステータス <span class="text-red-500">*</span></label>
            <select id="status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="有効">有効</option>
              <option value="無効">無効</option>
            </select>
          </div>
        </div>

        <!-- 説明 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">説明</label>
          <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <!-- ボタン -->
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            キャンセル
          </button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            保存
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    let currentMode = 'create';

    // マスター管理メニューの開閉
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

    // グローバル変数
    let allProducts = [];
    let sortConfig = {
      column: null,
      direction: 'asc'
    };

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadProducts();
      setupSortableHeaders();
    });

    // 商品一覧取得
    async function loadProducts(showLoading = false) {
      try {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshIcon');
          const refreshBtn = document.getElementById('refreshBtn');
          refreshIcon.textContent = '⏳';
          refreshBtn.disabled = true;
          refreshBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const response = await fetch('/api/products.php');
        const result = await response.json();

        if (result.success) {
          allProducts = result.data;
          renderTable(allProducts);
        } else {
          alert('データの取得に失敗しました。');
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      } finally {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshIcon');
          const refreshBtn = document.getElementById('refreshBtn');
          refreshIcon.textContent = '🔄';
          refreshBtn.disabled = false;
          refreshBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
      }
    }

    // テーブル描画
    function renderTable(products) {
      const tbody = document.getElementById('productTableBody');
      tbody.innerHTML = '';

      if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      products.forEach(product => {
        // 粗利計算
        const price = parseFloat(product.price) || 0;
        const cost = parseFloat(product.cost) || 0;
        const profit = price - cost;
        const profitRate = price > 0 ? (profit / price * 100) : 0;
        
        // 粗利の表示（色分け：黒字=緑、赤字=赤）
        const profitColor = profit >= 0 ? 'text-green-600' : 'text-red-600';
        const profitDisplay = `<span class="${profitColor} font-semibold">¥${profit.toLocaleString()}</span><br><span class="text-xs text-gray-500">(${profitRate.toFixed(1)}%)</span>`;

        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(product.product_id)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(product.product_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(product.large_category || '-')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(product.medium_category || '-')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(product.small_category || '-')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(product.point)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${price.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.cost ? '¥' + cost.toLocaleString() : '-'}</td>
                    <td class="px-6 py-4 text-sm">${profitDisplay}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${product.status === '有効' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${escapeHtml(product.status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${product.approval_required === '必要' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'}">
                            ${escapeHtml(product.approval_required)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <button onclick='openModal("edit", ${JSON.stringify(product)})' class="text-blue-600 hover:text-blue-900">編集</button>
                        <button onclick='duplicateProduct("${product.product_id}")' class="text-green-600 hover:text-green-900">複製</button>
                        <button onclick='deleteProduct("${product.product_id}", "${escapeHtml(product.product_name)}")' class="text-red-600 hover:text-red-900">削除</button>
                    </td>
                `;
        tbody.appendChild(tr);
      });
    }

    // ソート可能なヘッダーの設定
    function setupSortableHeaders() {
      const sortableHeaders = document.querySelectorAll('th[data-sort]');
      
      sortableHeaders.forEach(header => {
        header.addEventListener('click', () => {
          const column = header.getAttribute('data-sort');
          sortTable(column);
        });
      });
    }

    // テーブルのソート処理
    function sortTable(column) {
      // 同じカラムをクリックした場合は方向を反転
      if (sortConfig.column === column) {
        sortConfig.direction = sortConfig.direction === 'asc' ? 'desc' : 'asc';
      } else {
        sortConfig.column = column;
        sortConfig.direction = 'asc';
      }

      // データ型を取得
      const header = document.querySelector(`th[data-sort="${column}"]`);
      const dataType = header ? header.getAttribute('data-type') : 'string';

      // データをソート
      const sortedProducts = [...allProducts].sort((a, b) => {
        let aValue, bValue;

        // 粗利は計算値
        if (column === 'profit') {
          aValue = (parseFloat(a.price) || 0) - (parseFloat(a.cost) || 0);
          bValue = (parseFloat(b.price) || 0) - (parseFloat(b.cost) || 0);
        } else {
          aValue = a[column];
          bValue = b[column];
        }

        // 数値型の場合
        if (dataType === 'number') {
          aValue = parseFloat(aValue) || 0;
          bValue = parseFloat(bValue) || 0;
          
          if (sortConfig.direction === 'asc') {
            return aValue - bValue;
          } else {
            return bValue - aValue;
          }
        }
        
        // 文字列型の場合
        aValue = String(aValue || '').toLowerCase();
        bValue = String(bValue || '').toLowerCase();

        if (aValue < bValue) {
          return sortConfig.direction === 'asc' ? -1 : 1;
        }
        if (aValue > bValue) {
          return sortConfig.direction === 'asc' ? 1 : -1;
        }
        return 0;
      });

      // ソートアイコンを更新
      updateSortIcons();

      // テーブルを再描画
      renderTable(sortedProducts);
    }

    // ソートアイコンの更新
    function updateSortIcons() {
      const sortableHeaders = document.querySelectorAll('th[data-sort]');
      
      sortableHeaders.forEach(header => {
        const column = header.getAttribute('data-sort');
        const icon = header.querySelector('.sort-icon');
        
        if (column === sortConfig.column) {
          icon.textContent = sortConfig.direction === 'asc' ? '↑' : '↓';
          icon.classList.add('text-blue-600');
        } else {
          icon.textContent = '⇅';
          icon.classList.remove('text-blue-600');
        }
      });
    }

    // モーダル開く
    function openModal(mode, data = null) {
      currentMode = mode;
      const modal = document.getElementById('modal');
      const form = document.getElementById('productForm');
      const title = document.getElementById('modalTitle');

      form.reset();

      if (mode === 'create') {
        title.textContent = '商品登録';
        document.getElementById('approvalRequired').value = '必要';
      } else {
        title.textContent = '商品編集';
        document.getElementById('productId').value = data.product_id;
        document.getElementById('productName').value = data.product_name;
        document.getElementById('largeCategory').value = data.large_category || '';
        document.getElementById('mediumCategory').value = data.medium_category || '';
        document.getElementById('smallCategory').value = data.small_category || '';
        document.getElementById('point').value = data.point;
        document.getElementById('price').value = data.price;
        document.getElementById('cost').value = data.cost || '';
        document.getElementById('approvalRequired').value = data.approval_required;
        document.getElementById('status').value = data.status;
        document.getElementById('description').value = data.description || '';
      }

      modal.classList.remove('hidden');
    }

    // モーダル閉じる
    function closeModal() {
      document.getElementById('modal').classList.add('hidden');
    }

    // フォーム送信
    document.getElementById('productForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);
      const data = {
        product_name: formData.get('product_name'),
        large_category: formData.get('large_category'),
        medium_category: formData.get('medium_category'),
        small_category: formData.get('small_category'),
        point: formData.get('point'),
        price: formData.get('price'),
        cost: formData.get('cost'),
        approval_required: formData.get('approval_required'),
        status: formData.get('status'),
        description: formData.get('description')
      };

      try {
        let url = '/api/products.php';
        let method = 'POST';

        if (currentMode === 'edit') {
          const productId = document.getElementById('productId').value;
          url = `/api/products.php?id=${productId}`;
          method = 'PUT';
        }

        const response = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          closeModal();
          loadProducts();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    });

    // 削除
    async function deleteProduct(productId, name) {
      if (!confirm(`「${name}」を削除しますか？\nこの操作は取り消せません。`)) {
        return;
      }

      try {
        const response = await fetch(`/api/products.php?id=${productId}`, {
          method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadProducts();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // 複製
    async function duplicateProduct(productId) {
      if (!confirm('この商品を複製しますか？')) {
        return;
      }

      try {
        const response = await fetch('/api/products/duplicate.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            product_id: productId
          })
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          loadProducts();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // 更新
    function refreshList() {
      loadProducts(true);
    }

    // HTMLエスケープ
    function escapeHtml(text) {
      if (text === null || text === undefined) return '';
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>
    </main>
  </div>
</body>

</html>