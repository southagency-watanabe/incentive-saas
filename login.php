<?php
require_once __DIR__ . '/includes/session.php';

startSession();

// すでにログイン済みの場合はリダイレクト
if (isset($_SESSION['token']) && validateSession($_SESSION['token'])) {
  $redirect = ($_SESSION['role'] === 'admin') ? '/admin/dashboard.php' : '/user/home.php';
  header('Location: ' . $redirect);
  exit;
}

// エラーメッセージ取得
$error = '';
if (isset($_GET['error'])) {
  switch ($_GET['error']) {
    case 'invalid':
      $error = 'テナントID、ログインID、またはPINが正しくありません。';
      break;
    case 'session_expired':
      $error = 'セッションの有効期限が切れました。再度ログインしてください。';
      break;
  }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログイン - インセンティブSaaS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
  <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
    <!-- ロゴ -->
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800">インセンティブSaaS</h1>
      <p class="text-gray-600 mt-2">スタッフモチベーション管理システム</p>
    </div>

    <!-- エラーメッセージ -->
    <?php if ($error): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- ログインフォーム -->
    <form id="loginForm" class="space-y-4">
      <!-- テナントID -->
      <div>
        <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-1">
          テナントID
        </label>
        <input
          type="text"
          id="tenant_id"
          name="tenant_id"
          value="DEMO01"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          required>
      </div>

      <!-- ログインID -->
      <div>
        <label for="login_id" class="block text-sm font-medium text-gray-700 mb-1">
          ログインID
        </label>
        <input
          type="text"
          id="login_id"
          name="login_id"
          value="takahama"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          required>
      </div>

      <!-- PIN -->
      <div>
        <label for="pin" class="block text-sm font-medium text-gray-700 mb-1">
          PIN（4桁）
        </label>
        <input
          type="password"
          id="pin"
          name="pin"
          maxlength="4"
          pattern="[0-9]{4}"
          value="1111"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          required>
      </div>

      <!-- ログインボタン -->
      <button
        type="submit"
        class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
        ログイン
      </button>
    </form>

    <!-- フッター -->
    <div class="mt-8 text-center text-sm text-gray-500">
      © 2025 インセンティブSaaS
    </div>
  </div>

  <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);
      const data = {
        tenant_id: formData.get('tenant_id'),
        login_id: formData.get('login_id'),
        pin: formData.get('pin')
      };

      try {
        const response = await fetch('/api/auth.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        });

        const result = await response.json();

        // デバッグ: レスポンス内容をコンソールに出力
        console.log('Auth response:', result);
        console.log('Success:', result.success);
        console.log('Redirect:', result.redirect);

        if (result.success) {
          // ログイン成功 - リダイレクト
          console.log('Redirecting to:', result.redirect);
          window.location.href = result.redirect;
        } else {
          // エラー表示
          console.log('Login failed:', result.message);
          window.location.href = '/login.php?error=invalid';
        }
      } catch (error) {
        console.error('Login error:', error);
        alert('ログイン処理中にエラーが発生しました。');
      }
    });
  </script>
</body>

</html>