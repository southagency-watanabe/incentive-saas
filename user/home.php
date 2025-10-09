<?php
require_once __DIR__ . '/../includes/session.php';

// ログインチェック
requireLogin();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ホーム - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
  <!-- ヘッダー -->
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">インセンティブSaaS</h1>
        <p class="text-sm text-gray-600">スタッフホーム</p>
      </div>
      <div class="flex items-center gap-4">
        <span class="text-gray-700">
          <?= htmlspecialchars($_SESSION['name']) ?> さん
        </span>
        <a
          href="/api/logout.php"
          class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors">
          ログアウト
        </a>
      </div>
    </div>
  </header>

  <!-- ナビゲーション -->
  <nav class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex space-x-8">
        <a href="/user/home.php" class="py-4 px-2 border-b-2 border-blue-500 text-blue-600 font-medium">
          ホーム
        </a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">
          報告
        </a>
        <a href="/user/sales-approval.php" class="py-4 px-2 text-gray-600 hover:text-gray-900">
          売上承認
        </a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">
          ランキング
        </a>
        <a href="#" class="py-4 px-2 text-gray-600 hover:text-gray-900">
          掲示板
        </a>
      </div>
    </div>
  </nav>

  <!-- メインコンテンツ -->
  <main class="max-w-7xl mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold text-gray-800 mb-4">ログイン成功！</h2>

      <div class="space-y-2 text-gray-700">
        <p><strong>テナントID:</strong> <?= htmlspecialchars($_SESSION['tenant_id']) ?></p>
        <p><strong>メンバーID:</strong> <?= htmlspecialchars($_SESSION['member_id']) ?></p>
        <p><strong>名前:</strong> <?= htmlspecialchars($_SESSION['name']) ?></p>
        <p><strong>権限:</strong> <?= htmlspecialchars($_SESSION['role']) ?></p>
      </div>

      <div class="mt-6 p-4 bg-green-100 border border-green-400 rounded">
        <p class="text-green-800">
          ✅ <strong>フェーズ2完了：認証基盤が正常に動作しています！</strong>
        </p>
        <p class="text-green-700 mt-2">
          ユーザー権限でもログインできることを確認できました。
        </p>
      </div>
    </div>
  </main>

  <!-- フッター -->
  <footer class="max-w-7xl mx-auto px-4 py-4 text-center text-gray-500 text-sm">
    © 2025 インセンティブSaaS
  </footer>
</body>

</html>