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
  <title>イベント - インセンティブSaaS</title>
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
      <a href="/admin/events.php" class="flex items-center px-6 py-3 text-white bg-blue-600 border-l-4 border-blue-700">
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
        <h2 class="text-2xl font-bold text-gray-800">イベント</h2>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="px-8 py-8">
      <!-- イベントセクション -->
      <div>
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800">イベント一覧</h2>
          <button id="refreshEventsBtn" onclick="refreshEvents()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 flex items-center gap-2">
            <span id="refreshEventsIcon">🔄</span>
            <span>更新</span>
          </button>
        </div>

        <!-- タブナビゲーション -->
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2" id="eventTabs">
          <!-- タブはJavaScriptで挿入 -->
        </div>

        <!-- イベント詳細表示エリア -->
        <div id="eventDetail" class="bg-white rounded-lg shadow-lg">
          <!-- 選択されたイベントの詳細はJavaScriptで挿入 -->
          <div class="p-12 text-center text-gray-500">
            イベントを選択してください
          </div>
        </div>
      </div>
    </main>
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

    let countdownIntervals = [];
    let allEvents = [];
    let currentEventId = null;

    // 初期読み込み
    document.addEventListener('DOMContentLoaded', () => {
      loadEvents();
    });

    // イベント一覧取得
    async function loadEvents(showLoading = false) {
      try {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshEventsIcon');
          const refreshBtn = document.getElementById('refreshEventsBtn');
          refreshIcon.textContent = '⏳';
          refreshBtn.disabled = true;
          refreshBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }

        const response = await fetch('/api/events.php');
        const result = await response.json();

        if (result.success) {
          renderEventsList(result.data);
        } else {
          alert('イベントデータの取得に失敗しました。');
        }
      } catch (error) {
        console.error(error);
        alert('エラーが発生しました。');
      } finally {
        if (showLoading) {
          const refreshIcon = document.getElementById('refreshEventsIcon');
          const refreshBtn = document.getElementById('refreshEventsBtn');
          refreshIcon.textContent = '🔄';
          refreshBtn.disabled = false;
          refreshBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
      }
    }

    // イベントリスト描画
    function renderEventsList(events) {
      const tabsContainer = document.getElementById('eventTabs');
      const detailContainer = document.getElementById('eventDetail');

      // 既存のカウントダウンをクリア
      countdownIntervals.forEach(interval => clearInterval(interval));
      countdownIntervals = [];

      if (events.length === 0) {
        tabsContainer.innerHTML = '<div class="text-gray-500">イベントはありません</div>';
        detailContainer.innerHTML = '<div class="p-12 text-center text-gray-500">イベントはありません</div>';
        return;
      }

      // 有効なイベントのみ表示
      const activeEvents = events.filter(e => e.status === '有効');

      if (activeEvents.length === 0) {
        tabsContainer.innerHTML = '<div class="text-gray-500">有効なイベントはありません</div>';
        detailContainer.innerHTML = '<div class="p-12 text-center text-gray-500">有効なイベントはありません</div>';
        return;
      }

      // イベントを開催順にソート（開催前 → 開催中 → 終了）
      const sortedEvents = sortEventsByStatus(activeEvents);
      allEvents = sortedEvents;

      // タブを描画
      renderEventTabs(sortedEvents);

      // 最初のイベントを表示
      if (sortedEvents.length > 0) {
        showEventDetail(sortedEvents[0].event_id);
      }
    }

    // イベントを開催状態でソート
    function sortEventsByStatus(events) {
      const now = new Date();

      return events.sort((a, b) => {
        const aStart = new Date(a.start_date);
        const aEnd = new Date(a.end_date);
        const bStart = new Date(b.start_date);
        const bEnd = new Date(b.end_date);

        // ステータスを計算
        const getStatus = (start, end) => {
          if (now < start) return 1; // 開催前
          if (now >= start && now <= end) return 2; // 開催中
          return 3; // 終了
        };

        const aStatus = getStatus(aStart, aEnd);
        const bStatus = getStatus(bStart, bEnd);

        // ステータスで比較
        if (aStatus !== bStatus) {
          return aStatus - bStatus;
        }

        // 同じステータスの場合は開始日で比較
        if (aStatus === 1) {
          // 開催前：開始日が近い順
          return aStart - bStart;
        } else if (aStatus === 2) {
          // 開催中：終了日が近い順
          return aEnd - bEnd;
        } else {
          // 終了：終了日が新しい順
          return bEnd - aEnd;
        }
      });
    }

    // タブを描画
    function renderEventTabs(events) {
      const container = document.getElementById('eventTabs');
      container.innerHTML = '';

      events.forEach(event => {
        const tab = createEventTab(event);
        container.appendChild(tab);
      });
    }

    // タブ作成
    function createEventTab(event) {
      const button = document.createElement('button');
      button.type = 'button';
      button.onclick = () => showEventDetail(event.event_id);

      const startDate = new Date(event.start_date);
      const endDate = new Date(event.end_date);
      const now = new Date();

      // ステータス判定
      let statusBadge = '';
      let statusColor = '';
      if (now < startDate) {
        statusBadge = '開催前';
        statusColor = 'bg-blue-100 text-blue-800';
      } else if (now >= startDate && now <= endDate) {
        statusBadge = '開催中';
        statusColor = 'bg-green-100 text-green-800';
      } else {
        statusBadge = '終了';
        statusColor = 'bg-gray-100 text-gray-800';
      }

      const isActive = currentEventId === event.event_id;
      button.className = `flex-shrink-0 px-6 py-3 rounded-lg border-2 transition-all ${
        isActive
          ? 'border-blue-500 bg-blue-50 shadow-md'
          : 'border-gray-200 bg-white hover:border-blue-300 hover:shadow'
      }`;

      button.innerHTML = `
        <div class="text-left">
          <div class="flex items-center gap-2 mb-1">
            <span class="font-bold text-gray-900">${escapeHtml(event.event_name)}</span>
            <span class="px-2 py-0.5 text-xs font-semibold rounded-full ${statusColor}">${statusBadge}</span>
          </div>
          <div class="text-xs text-gray-500">${formatDate(startDate)} 〜 ${formatDate(endDate)}</div>
        </div>
      `;

      return button;
    }

    // イベント詳細を表示
    function showEventDetail(eventId) {
      currentEventId = eventId;
      const event = allEvents.find(e => e.event_id === eventId);

      if (!event) return;

      // タブの表示を更新
      renderEventTabs(allEvents);

      // 詳細を表示
      const detailContainer = document.getElementById('eventDetail');
      detailContainer.innerHTML = '';

      const detail = createEventDetail(event);
      detailContainer.appendChild(detail);

      // カウントダウンを開始
      const startDate = new Date(event.start_date);
      const endDate = new Date(event.end_date);
      startCountdown(event.event_id, startDate, endDate);
    }

    // イベント詳細作成
    function createEventDetail(event) {
      const div = document.createElement('div');
      div.className = 'overflow-hidden';

      // 期間
      const startDate = new Date(event.start_date);
      const endDate = new Date(event.end_date);
      const now = new Date();

      // ステータス判定
      let statusBadge = '';
      let statusColor = '';
      if (now < startDate) {
        statusBadge = '開催前';
        statusColor = 'bg-blue-100 text-blue-800';
      } else if (now >= startDate && now <= endDate) {
        statusBadge = '開催中';
        statusColor = 'bg-green-100 text-green-800';
      } else {
        statusBadge = '終了';
        statusColor = 'bg-gray-100 text-gray-800';
      }

      // 対象商品情報
      let targetInfo = '';
      if (event.target_type === '全商品') {
        targetInfo = '全商品';
      } else if (event.target_type === '特定商品') {
        if (event.target_names && event.target_names.length > 0) {
          // 商品別倍率を含めた表示
          const productIds = event.target_ids ? event.target_ids.split(',') : [];
          const productDisplays = event.target_names.map((name, index) => {
            const productId = productIds[index];
            // 商品別倍率があればそれを使用、なければデフォルト倍率
            const multiplier = (event.product_multipliers && event.product_multipliers[productId]) 
              ? parseFloat(event.product_multipliers[productId]).toFixed(1)
              : parseFloat(event.multiplier).toFixed(1);
            return `${escapeHtml(name)}: <span class="font-bold text-purple-600">${multiplier}倍</span>`;
          });
          targetInfo = '<div class="space-y-1">' + productDisplays.join('<br>') + '</div>';
        } else {
          targetInfo = '特定商品';
        }
      } else if (event.target_type === '全アクション') {
        targetInfo = '全アクション';
      } else if (event.target_type === '特定アクション') {
        let displays = [];
        
        // カテゴリ別倍率を表示
        if (event.category_multipliers && Object.keys(event.category_multipliers).length > 0) {
          Object.entries(event.category_multipliers).forEach(([category, multiplier]) => {
            displays.push(`<span class="text-purple-600">【${escapeHtml(category)}】: <span class="font-bold">${parseFloat(multiplier).toFixed(1)}倍</span></span>`);
          });
        }
        
        // 個別アクション別倍率を表示
        if (event.target_names && event.target_names.length > 0) {
          const actionIds = event.target_ids ? event.target_ids.split(',') : [];
          const actionDisplays = event.target_names.map((name, index) => {
            const actionId = actionIds[index];
            // アクション別倍率があればそれを使用、なければデフォルト倍率
            const multiplier = (event.action_multipliers && event.action_multipliers[actionId]) 
              ? parseFloat(event.action_multipliers[actionId]).toFixed(1)
              : parseFloat(event.multiplier).toFixed(1);
            return `${escapeHtml(name)}: <span class="font-bold text-green-600">${multiplier}倍</span>`;
          });
          displays = displays.concat(actionDisplays);
        }
        
        if (displays.length > 0) {
          targetInfo = '<div class="space-y-1">' + displays.join('<br>') + '</div>';
        } else {
          targetInfo = '特定アクション';
        }
      }

      div.innerHTML = `
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-8 text-white">
          <div class="flex justify-between items-start mb-3">
            <h3 class="text-3xl font-bold">${escapeHtml(event.event_name)}</h3>
            <span class="px-4 py-2 text-sm font-semibold rounded-full ${statusColor}">${statusBadge}</span>
          </div>
          <p class="text-blue-100 text-base">${escapeHtml(event.description || 'イベント説明なし')}</p>
        </div>
        <div class="p-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- 左側：詳細情報 -->
            <div class="space-y-6">
              <!-- 倍率 -->
              <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-lg p-6 text-center">
                <div class="text-5xl font-bold text-purple-600 mb-2">${parseFloat(event.multiplier).toFixed(1)}倍</div>
                <div class="text-sm text-gray-600">ポイント倍率</div>
              </div>

              <!-- 期間 -->
              <div class="bg-gray-50 rounded-lg p-6">
                <div class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                  <span class="text-xl">📅</span>
                  <span>開催期間</span>
                </div>
                <div class="text-gray-700">
                  <div class="mb-2">
                    <span class="text-xs text-gray-500">開始</span>
                    <div class="font-semibold">${formatDatetime(event.start_date)}</div>
                  </div>
                  <div>
                    <span class="text-xs text-gray-500">終了</span>
                    <div class="font-semibold">${formatDatetime(event.end_date)}</div>
                  </div>
                </div>
              </div>

              <!-- 対象 -->
              <div class="bg-gray-50 rounded-lg p-6">
                <div class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                  <span class="text-xl">🎯</span>
                  <span>対象</span>
                </div>
                <div class="font-semibold text-gray-700">${targetInfo}</div>
              </div>

              <!-- 繰り返し設定 -->
              <div class="bg-gray-50 rounded-lg p-6">
                <div class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                  <span class="text-xl">🔄</span>
                  <span>繰り返し</span>
                </div>
                <div class="font-semibold text-gray-700">${escapeHtml(event.repeat_type)}</div>
                ${event.days_of_week ? `<div class="text-sm text-gray-600 mt-1">曜日: ${escapeHtml(event.days_of_week)}</div>` : ''}
                ${event.day_of_month ? `<div class="text-sm text-gray-600 mt-1">日付: 毎月${event.day_of_month}日</div>` : ''}
              </div>
            </div>

            <!-- 右側：カウントダウン -->
            <div class="flex items-center justify-center">
              <div class="w-full bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-8 shadow-inner" id="countdown-${event.event_id}">
                <!-- カウントダウンはJavaScriptで更新 -->
              </div>
            </div>
          </div>

          <!-- 告知本文セクション -->
          ${event.publish_notice && event.notice_title ? `
          <div class="mt-8 border-t pt-8">
            <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg p-6">
              <div class="flex items-center gap-2 mb-4">
                <span class="text-2xl">📢</span>
                <h4 class="text-xl font-bold text-gray-800">告知本文</h4>
              </div>
              <div class="mb-4">
                <div class="text-sm text-gray-600 mb-2">タイトル</div>
                <div class="text-lg font-semibold text-gray-900">${escapeHtml(event.notice_title)}</div>
              </div>
              <div>
                <div class="text-sm text-gray-600 mb-2">本文</div>
                <div class="text-gray-800 whitespace-pre-wrap leading-relaxed">${escapeHtml(event.notice_body || '')}</div>
              </div>
            </div>
          </div>
          ` : ''}
        </div>
      `;

      return div;
    }

    // 日時フォーマット（YYYY-MM-DD HH:MM）
    function formatDatetime(datetime) {
      if (!datetime) return '';
      const d = new Date(datetime);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      const hours = String(d.getHours()).padStart(2, '0');
      const minutes = String(d.getMinutes()).padStart(2, '0');
      return `${year}-${month}-${day} ${hours}:${minutes}`;
    }

    // カウントダウン開始
    function startCountdown(eventId, startDate, endDate) {
      const countdownElement = document.getElementById(`countdown-${eventId}`);

      const updateCountdown = () => {
        const now = new Date();
        let targetDate, label;

        if (now < startDate) {
          targetDate = startDate;
          label = '開始まで';
        } else if (now >= startDate && now <= endDate) {
          targetDate = endDate;
          label = '終了まで';
        } else {
          countdownElement.innerHTML = '<div class="text-center text-gray-500">イベント終了</div>';
          return;
        }

        const diff = targetDate - now;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        countdownElement.innerHTML = `
          <div class="text-center">
            <div class="text-sm font-medium text-gray-600 mb-4">${label}</div>
            <div class="grid grid-cols-4 gap-4">
              <div class="bg-white rounded-lg p-4 shadow-md">
                <div class="text-4xl font-bold text-gray-800">${days}</div>
                <div class="text-sm text-gray-500 mt-1">日</div>
              </div>
              <div class="bg-white rounded-lg p-4 shadow-md">
                <div class="text-4xl font-bold text-gray-800">${hours}</div>
                <div class="text-sm text-gray-500 mt-1">時間</div>
              </div>
              <div class="bg-white rounded-lg p-4 shadow-md">
                <div class="text-4xl font-bold text-gray-800">${minutes}</div>
                <div class="text-sm text-gray-500 mt-1">分</div>
              </div>
              <div class="bg-white rounded-lg p-4 shadow-md">
                <div class="text-4xl font-bold text-gray-800">${seconds}</div>
                <div class="text-sm text-gray-500 mt-1">秒</div>
              </div>
            </div>
          </div>
        `;
      };

      // 初回実行
      updateCountdown();

      // 1秒ごとに更新
      const interval = setInterval(updateCountdown, 1000);
      countdownIntervals.push(interval);
    }

    // 更新
    function refreshEvents() {
      loadEvents(true);
    }

    // 日付フォーマット（YYYY-MM-DD）
    function formatDate(date) {
      if (!date) return '';
      const d = new Date(date);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
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
