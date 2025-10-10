<?php
require_once __DIR__ . '/config/database.php';
session_start();

// 既に設定済みかチェック
$pdo = getDB();
$stmt = $pdo->query("SELECT COUNT(*) as count FROM tenants WHERE tenant_id = 'DEMO01'");
$result = $stmt->fetch();
$is_setup_complete = $result['count'] > 0;

// 設定済みの場合はログインページにリダイレクト
if ($is_setup_complete && !isset($_GET['force'])) {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>初期設定 - インセンティブ管理システム</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8">
            <!-- ヘッダー -->
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">インセンティブ管理システム</h1>
                <p class="mt-2 text-sm text-gray-600">初期設定を行います</p>
            </div>

            <!-- 進捗インジケーター -->
            <div class="flex items-center justify-center space-x-4">
                <div class="flex items-center">
                    <div id="step1-indicator" class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">1</div>
                    <span class="ml-2 text-sm font-medium text-gray-900">テナント情報</span>
                </div>
                <div class="w-16 h-1 bg-gray-300" id="progress-bar-1"></div>
                <div class="flex items-center">
                    <div id="step2-indicator" class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">2</div>
                    <span class="ml-2 text-sm font-medium text-gray-600">承認フロー</span>
                </div>
            </div>

            <!-- フォーム -->
            <form id="setupForm" class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow">
                <!-- ステップ1: テナント情報 -->
                <div id="step1" class="space-y-6">
                    <h2 class="text-xl font-bold text-gray-900 border-b pb-2">1. テナント情報</h2>

                    <div>
                        <label for="tenant_id" class="block text-sm font-medium text-gray-700">
                            テナントID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="tenant_id" name="tenant_id" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="例: takahama001"
                            pattern="[a-zA-Z0-9_-]+"
                            title="英数字、ハイフン、アンダースコアのみ使用可能">
                        <p class="mt-1 text-xs text-gray-500">英数字、ハイフン、アンダースコアのみ。変更不可のため慎重に入力してください。</p>
                    </div>

                    <div>
                        <label for="tenant_name" class="block text-sm font-medium text-gray-700">
                            店舗名 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="tenant_name" name="tenant_name" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="例: タレそば専門店高濱">
                    </div>

                    <div>
                        <label for="industry" class="block text-sm font-medium text-gray-700">
                            業種 <span class="text-red-500">*</span>
                        </label>
                        <select id="industry" name="industry" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">選択してください</option>
                            <option value="飲食業" selected>飲食業</option>
                            <option value="小売業">小売業</option>
                            <option value="サービス業">サービス業</option>
                            <option value="その他">その他</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">
                                住所
                            </label>
                            <input type="text" id="address" name="address"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="例: 東京都渋谷区神南1-2-3">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">
                                電話番号
                            </label>
                            <input type="tel" id="phone" name="phone"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="例: 03-1234-5678">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            メールアドレス
                        </label>
                        <input type="email" id="email" name="email"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="例: info@takahama-taresoba.jp">
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="goToStep2()"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            次へ
                        </button>
                    </div>
                </div>

                <!-- ステップ2: 承認フロー設定 -->
                <div id="step2" class="space-y-6 hidden">
                    <h2 class="text-xl font-bold text-gray-900 border-b pb-2">2. 承認フロー設定</h2>

                    <div class="space-y-4">
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                            <p class="text-sm text-blue-700">
                                各項目について、承認が必要かどうかを設定します。承認が不要な場合は自動承認されます。
                            </p>
                        </div>

                        <!-- 売上の承認 -->
                        <div class="border rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 mb-3">売上の承認</h3>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="sales_approval" value="none" checked
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">承認不要（自動承認）</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="sales_approval" value="all"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">全ての売上で承認が必要</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="sales_approval" value="threshold"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">金額条件で承認が必要</span>
                                </label>
                                <div id="sales_threshold_input" class="ml-6 hidden">
                                    <label class="block text-sm text-gray-600 mb-1">承認が必要な金額（円以上）</label>
                                    <input type="number" name="sales_threshold" value="2000" min="0" step="100"
                                        class="w-48 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- アクションの承認 -->
                        <div class="border rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 mb-3">アクション（口コミ・SNS投稿等）の承認</h3>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="action_approval" value="none"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">承認不要（自動承認）</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="action_approval" value="all" checked
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">全てのアクションで承認が必要</span>
                                </label>
                            </div>
                        </div>

                        <!-- タスクの承認 -->
                        <div class="border rounded-lg p-4">
                            <h3 class="font-semibold text-gray-900 mb-3">タスク（日常業務）の承認</h3>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="radio" name="task_approval" value="none" checked
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">承認不要（自動承認）</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="task_approval" value="all"
                                        class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">全てのタスクで承認が必要</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between pt-4">
                        <button type="button" onclick="goToStep1()"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            戻る
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                            設定を完了
                        </button>
                    </div>
                </div>
            </form>

            <!-- エラーメッセージ -->
            <div id="errorMessage" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-md">
                <p class="text-sm text-red-600"></p>
            </div>

            <!-- 成功メッセージ -->
            <div id="successMessage" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-md">
                <p class="text-sm text-green-600">設定が完了しました。ログインページに移動します...</p>
            </div>
        </div>
    </div>

    <script>
        // ステップ管理
        function goToStep1() {
            document.getElementById('step1').classList.remove('hidden');
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step1-indicator').classList.remove('bg-gray-300', 'text-gray-600');
            document.getElementById('step1-indicator').classList.add('bg-blue-600', 'text-white');
            document.getElementById('step2-indicator').classList.remove('bg-blue-600', 'text-white');
            document.getElementById('step2-indicator').classList.add('bg-gray-300', 'text-gray-600');
            document.getElementById('progress-bar-1').classList.remove('bg-blue-600');
            document.getElementById('progress-bar-1').classList.add('bg-gray-300');
        }

        function goToStep2() {
            // ステップ1のバリデーション
            const tenantId = document.getElementById('tenant_id').value;
            const tenantName = document.getElementById('tenant_name').value;
            const industry = document.getElementById('industry').value;

            if (!tenantId || !tenantName || !industry) {
                showError('必須項目を入力してください。');
                return;
            }

            // テナントIDの形式チェック
            if (!/^[a-zA-Z0-9_-]+$/.test(tenantId)) {
                showError('テナントIDは英数字、ハイフン、アンダースコアのみ使用可能です。');
                return;
            }

            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');
            document.getElementById('step2-indicator').classList.remove('bg-gray-300', 'text-gray-600');
            document.getElementById('step2-indicator').classList.add('bg-blue-600', 'text-white');
            document.getElementById('progress-bar-1').classList.remove('bg-gray-300');
            document.getElementById('progress-bar-1').classList.add('bg-blue-600');
            hideError();
            window.scrollTo(0, 0);
        }

        // 売上承認の閾値入力表示切替
        document.querySelectorAll('input[name="sales_approval"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const thresholdInput = document.getElementById('sales_threshold_input');
                if (this.value === 'threshold') {
                    thresholdInput.classList.remove('hidden');
                } else {
                    thresholdInput.classList.add('hidden');
                }
            });
        });

        // フォーム送信
        document.getElementById('setupForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('/api/setup.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showSuccess();
                    setTimeout(() => {
                        window.location.href = '/login.php';
                    }, 2000);
                } else {
                    showError(result.message || '設定に失敗しました。');
                }
            } catch (error) {
                showError('通信エラーが発生しました。');
                console.error('Setup error:', error);
            }
        });

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.querySelector('p').textContent = message;
            errorDiv.classList.remove('hidden');
        }

        function hideError() {
            document.getElementById('errorMessage').classList.add('hidden');
        }

        function showSuccess() {
            document.getElementById('successMessage').classList.remove('hidden');
        }
    </script>
</body>
</html>
