<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../config/database.php';

// 管理者権限チェック
requireAdmin();

// メンバー・商品一覧取得（プルダウン用）
$pdo = getDB();

$stmt = $pdo->prepare("SELECT member_id, name FROM members WHERE tenant_id = :tenant_id AND status = '有効' ORDER BY member_id ASC");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$members = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT product_id, product_name, price, point FROM products WHERE tenant_id = :tenant_id AND status = '有効' ORDER BY product_id ASC");
$stmt->execute(['tenant_id' => $_SESSION['tenant_id']]);
$products = $stmt->fetchAll();

// 商品情報をJSON化（JavaScript用）
$productsJson = json_encode($products);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>売上入力 - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
  <!-- ヘッダー -->
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">インセンティブSaaS</h1>
        <p class="text-sm text-gray-600">売上入力</p>
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
        <a href="/admin/masters/members.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">マスタ管理</a>
        <a href="/admin/sales/input.php" class="py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">売上管理</a>
        <a href="/admin/approvals.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">承認管理</a>
        <a href="/admin/performance.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">実績管理</a>
        <a href="/admin/bulletins.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">掲示板管理</a>
        <a href="/admin/ranking.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">ランキング</a>
      </div>
    </div>
  </nav>

  <!-- メインコンテンツ -->
  <main class="max-w-7xl mx-auto px-4 py-8">
    <!-- 売上入力フォーム -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h2 class="text-xl font-bold text-gray-800 mb-4">売上入力</h2>

      <form id="salesForm" class="space-y-4">
        <div class="grid grid-cols-3 gap-4">
          <!-- 売上計上日 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">売上計上日 <span class="text-red-500">*</span></label>
            <input type="date" id="date" name="date" required max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- メンバー -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">メンバー <span class="text-red-500">*</span></label>
            <select id="memberId" name="member_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="">選択してください</option>
              <?php foreach ($members as $member): ?>
                <option value="<?= htmlspecialchars($member['member_id']) ?>">
                  <?= htmlspecialchars($member['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 商品 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">商品 <span class="text-red-500">*</span></label>
            <select id="productId" name="product_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
              <option value="">選択してください</option>
              <?php foreach ($products as $product): ?>
                <option value="<?= htmlspecialchars($product['product_id']) ?>" data-price="<?= htmlspecialchars($product['price']) ?>" data-point="<?= htmlspecialchars($product['point']) ?>">
                  <?= htmlspecialchars($product['product_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 数量 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">数量 <span class="text-red-500">*</span></label>
            <input type="number" id="quantity" name="quantity" min="1" required value="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
          </div>

          <!-- 単価 -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">単価 <span class="text-red-500">*</span></label>
            <input type="number" id="unitPrice" name="unit_price" min="0" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <p id="priceWarning" class="text-sm text-orange-600 mt-1 hidden">⚠ 標準価格と異なります</p>
          </div>

          <!-- 合計金額（表示のみ） -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">合計金額</label>
            <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-900 font-medium">
              ¥<span id="totalAmount">0</span>
            </div>
          </div>
        </div>

        <!-- 備考 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">備考</label>
          <textarea id="note" name="note" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <!-- 登録ボタン -->
        <div class="flex justify-end">
          <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
            登録
          </button>
        </div>
      </form>
    </div>

    <!-- フィルタボタン -->
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-bold text-gray-800">売上一覧</h2>
      <div class="flex gap-3">
        <button id="filterAll" onclick="setFilter('all')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
          全件表示
        </button>
        <button id="filterPending" onclick="setFilter('pending')" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
          承認待ちのみ
        </button>
        <button onclick="refreshList()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center gap-2">
          <span>🔄</span>
          <span>更新</span>
        </button>
      </div>
    </div>

    <!-- 合計金額表示 -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
      <p class="text-lg font-bold text-blue-900">
        合計金額: ¥<span id="listTotal">0</span>
      </p>
    </div>

    <!-- テーブル -->
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">倍率</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">イベント</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">承認状態</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">備考</th>
          </tr>
        </thead>
        <tbody id="salesTableBody" class="bg-white divide-y divide-gray-200">
          <!-- データはJavaScriptで挿入 -->
        </tbody>
      </table>
    </div>
  </main>

  <script>
    const productsData = <?= $productsJson ?>;
    let currentFilter = 'all';

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadSales();
      setupProductChange();
      setupPriceChange();
      setupQuantityChange();
    });

    // 商品選択時の処理
    function setupProductChange() {
      const productSelect = document.getElementById('productId');
      const unitPriceInput = document.getElementById('unitPrice');

      productSelect.addEventListener('change', (e) => {
        const selectedOption = e.target.options[e.target.selectedIndex];
        if (selectedOption.value) {
          const price = selectedOption.getAttribute('data-price');
          unitPriceInput.value = price;
          updateTotal();
          checkPriceChange();
        }
      });
    }

    // 単価変更時の警告
    function setupPriceChange() {
      const unitPriceInput = document.getElementById('unitPrice');
      unitPriceInput.addEventListener('input', () => {
        updateTotal();
        checkPriceChange();
      });
    }

    // 数量変更時の合計更新
    function setupQuantityChange() {
      const quantityInput = document.getElementById('quantity');
      quantityInput.addEventListener('input', updateTotal);
    }

    // 単価変更チェック
    function checkPriceChange() {
      const productSelect = document.getElementById('productId');
      const unitPriceInput = document.getElementById('unitPrice');
      const priceWarning = document.getElementById('priceWarning');

      const selectedOption = productSelect.options[productSelect.selectedIndex];
      if (selectedOption.value) {
        const standardPrice = parseFloat(selectedOption.getAttribute('data-price'));
        const currentPrice = parseFloat(unitPriceInput.value);

        if (Math.abs(standardPrice - currentPrice) > 0.01) {
          priceWarning.classList.remove('hidden');
        } else {
          priceWarning.classList.add('hidden');
        }
      }
    }

    // 合計金額更新
    function updateTotal() {
      const quantity = parseFloat(document.getElementById('quantity').value) || 0;
      const unitPrice = parseFloat(document.getElementById('unitPrice').value) || 0;
      const total = quantity * unitPrice;
      document.getElementById('totalAmount').textContent = total.toLocaleString();
    }

    // フィルタ設定
    function setFilter(filter) {
      currentFilter = filter;

      // ボタンのスタイル更新
      document.getElementById('filterAll').classList.toggle('bg-blue-600', filter === 'all');
      document.getElementById('filterAll').classList.toggle('bg-gray-500', filter !== 'all');
      document.getElementById('filterPending').classList.toggle('bg-blue-600', filter === 'pending');
      document.getElementById('filterPending').classList.toggle('bg-orange-500', filter !== 'pending');

      loadSales();
    }

    // 売上一覧取得
    async function loadSales() {
      try {
        const response = await fetch(`/api/sales.php?filter=${currentFilter}`);
        const result = await response.json();

        if (result.success) {
          renderTable(result.data);
          document.getElementById('listTotal').textContent = result.total.toLocaleString();
        } else {
          alert('データの取得に失敗しました。');
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    }

    // テーブル描画
    function renderTable(sales) {
      const tbody = document.getElementById('salesTableBody');
      tbody.innerHTML = '';

      if (sales.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
        return;
      }

      sales.forEach(sale => {
        const amount = sale.quantity * sale.unit_price;
        const eventDisplay = sale.applied_event_name ? escapeHtml(sale.applied_event_name) : '-';
        const eventClass = sale.applied_event_name ? 'text-blue-600 font-medium' : 'text-gray-500';

        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(sale.date)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(sale.member_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${escapeHtml(sale.product_name)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${sale.quantity}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(sale.unit_price).toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${amount.toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${sale.final_point}pt</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${parseFloat(sale.event_multiplier).toFixed(1)}倍</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm ${eventClass}">${eventDisplay}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(sale.approval_status)}">
                            ${escapeHtml(sale.approval_status)}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(sale.note || '-')}</td>
                `;
        tbody.appendChild(tr);
      });
    }

    // 承認状態の色
    function getStatusColor(status) {
      switch (status) {
        case 'ユーザー確認待ち':
          return 'bg-orange-100 text-orange-800';
        case '承認待ち':
          return 'bg-yellow-100 text-yellow-800';
        case '承認済み':
          return 'bg-green-100 text-green-800';
        case '却下':
          return 'bg-red-100 text-red-800';
        default:
          return 'bg-gray-100 text-gray-800';
      }
    }

    // フォーム送信
    document.getElementById('salesForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);
      const data = {
        date: formData.get('date'),
        member_id: formData.get('member_id'),
        product_id: formData.get('product_id'),
        quantity: formData.get('quantity'),
        unit_price: formData.get('unit_price'),
        note: formData.get('note')
      };

      try {
        const response = await fetch('/api/sales.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
          alert(result.message);
          e.target.reset();
          document.getElementById('date').value = '<?= date('Y-m-d') ?>';
          document.getElementById('totalAmount').textContent = '0';
          document.getElementById('priceWarning').classList.add('hidden');
          loadSales();
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      }
    });

    // 更新
    function refreshList() {
      loadSales();
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