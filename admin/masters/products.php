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

<body class="bg-gray-100 min-h-screen">
  <!-- ヘッダー -->
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">インセンティブSaaS</h1>
        <p class="text-sm text-gray-600">商品マスタ</p>
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
        <a href="/admin/masters/members.php" class="py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">マスタ管理</a>
        <a href="/admin/sales/input.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">売上管理</a>
        <a href="/admin/approvals.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">承認管理</a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">実績管理</a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">掲示板管理</a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">ランキング</a>
      </div>
    </div>
  </nav>

  <!-- サブナビゲーション（マスタ切り替え） -->
  <div class="bg-gray-50 border-b">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex space-x-6">
        <a href="/admin/masters/members.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">メンバー</a>
        <a href="/admin/masters/teams.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">チーム</a>
        <a href="/admin/masters/products.php" class="py-3 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">商品</a>
        <a href="/admin/masters/actions.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">アクション</a>
        <a href="/admin/masters/tasks.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">タスク</a>
        <a href="/admin/masters/events.php" class="py-3 px-2 text-gray-600 hover:text-gray-900">イベント</a>
      </div>
    </div>
  </div>

  <!-- メインコンテンツ -->
  <main class="max-w-7xl mx-auto px-4 py-8">
    <!-- ヘッダーアクション -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">商品一覧</h2>
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">商品ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">商品名</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">カテゴリ</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">付与pt</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">売価</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">原価</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ステータス</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">承認要否</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">操作</th>
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

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadProducts();
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
          renderTable(result.data);
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
        tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      products.forEach(product => {
        const category = [
          product.large_category,
          product.medium_category,
          product.small_category
        ].filter(c => c).join(' > ') || '-';

        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(product.product_id)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(product.product_name)}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(category)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(product.point)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(product.price).toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.cost ? '¥' + parseFloat(product.cost).toLocaleString() : '-'}</td>
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
</body>

</html>