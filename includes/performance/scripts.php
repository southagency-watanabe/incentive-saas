<script>
  // グローバル変数
  let currentApprovalFilter = 'approved';
  let currentStartDate = '';
  let currentEndDate = '';

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

  // 実績管理ドロップダウンの開閉
  function togglePerformanceMenu() {
    const submenu = document.getElementById('performanceSubmenu');
    const arrow = document.getElementById('performanceArrow');

    if (submenu.classList.contains('hidden')) {
      submenu.classList.remove('hidden');
      arrow.style.transform = 'rotate(180deg)';
    } else {
      submenu.classList.add('hidden');
      arrow.style.transform = 'rotate(0deg)';
    }
  }

  // 時系列サブメニューの開閉
  function toggleTimeSeriesMenu() {
    const submenu = document.getElementById('timeSeriesSubmenu');
    const arrow = document.getElementById('timeSeriesArrow');

    if (submenu.classList.contains('hidden')) {
      submenu.classList.remove('hidden');
      arrow.style.transform = 'rotate(180deg)';
    } else {
      submenu.classList.add('hidden');
      arrow.style.transform = 'rotate(0deg)';
    }
  }

  // 期間プリセット適用
  function applyPreset() {
    const preset = document.getElementById('periodPreset').value;
    const today = new Date();
    let startDate, endDate;

    switch (preset) {
      case 'today':
        startDate = endDate = today;
        break;
      case 'this_week':
        const dayOfWeek = today.getDay();
        const monday = new Date(today);
        monday.setDate(today.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));
        startDate = monday;
        endDate = new Date(monday);
        endDate.setDate(monday.getDate() + 6);
        break;
      case 'this_month':
        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
        endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        break;
      case 'last_month':
        startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        endDate = new Date(today.getFullYear(), today.getMonth(), 0);
        break;
      case 'this_quarter':
        const quarter = Math.floor(today.getMonth() / 3);
        startDate = new Date(today.getFullYear(), quarter * 3, 1);
        endDate = new Date(today.getFullYear(), quarter * 3 + 3, 0);
        break;
      case 'this_year':
        startDate = new Date(today.getFullYear(), 0, 1);
        endDate = new Date(today.getFullYear(), 11, 31);
        break;
    }

    document.getElementById('startDate').value = formatDate(startDate);
    document.getElementById('endDate').value = formatDate(endDate);
  }

  // 日付フォーマット (YYYY-MM-DD)
  function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  // 承認フィルター切り替え
  function toggleApprovalFilter(filter) {
    currentApprovalFilter = filter;
    
    const btnApproved = document.getElementById('btnApproved');
    const btnAll = document.getElementById('btnAll');
    
    if (filter === 'approved') {
      btnApproved.className = 'px-4 py-2 rounded bg-blue-600 text-white font-medium';
      btnAll.className = 'px-4 py-2 rounded bg-gray-200 text-gray-700';
    } else {
      btnApproved.className = 'px-4 py-2 rounded bg-gray-200 text-gray-700';
      btnAll.className = 'px-4 py-2 rounded bg-blue-600 text-white font-medium';
    }
  }

  // フィルター適用
  async function applyFilters() {
    currentStartDate = document.getElementById('startDate').value;
    currentEndDate = document.getElementById('endDate').value;
    
    if (!currentStartDate || !currentEndDate) {
      alert('開始日と終了日を選択してください。');
      return;
    }

    await loadData();
  }

  // サマリーカード更新
  function updateSummary(summary) {
    document.getElementById('totalSales').textContent = summary.total_sales.toLocaleString();
    document.getElementById('totalProfit').textContent = summary.total_profit.toLocaleString();
    document.getElementById('totalPoints').textContent = summary.total_points.toLocaleString();
    document.getElementById('totalCount').textContent = summary.total_count.toLocaleString();
    document.getElementById('approvalRate').textContent = summary.approval_rate.toFixed(1);
  }

  // HTML エスケープ
  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
  }

  // 詳細フィルタの開閉
  let filterDetailsOpen = false;

  function toggleFilterDetails() {
    const details = document.getElementById('filterDetails');
    const arrow = document.getElementById('filterArrow');

    filterDetailsOpen = !filterDetailsOpen;

    if (filterDetailsOpen) {
      details.classList.remove('hidden');
      arrow.style.transform = 'rotate(180deg)';
    } else {
      details.classList.add('hidden');
      arrow.style.transform = 'rotate(0deg)';
    }
  }

  // フィルタ選択肢の読み込み（各ページで定義されたmembers, teams, productsを使用）
  function loadFilterOptions() {
    // membersがグローバル変数として定義されている場合
    if (typeof members !== 'undefined') {
      const memberFilters = document.getElementById('memberFilters');
      memberFilters.innerHTML = '';
      members.forEach(member => {
        const label = document.createElement('label');
        label.className = 'flex items-center space-x-2 mb-1';
        label.innerHTML = `
          <input type="checkbox" name="member_ids[]" value="${escapeHtml(member.member_id)}" class="rounded">
          <span class="text-sm">${escapeHtml(member.name)}</span>
        `;
        memberFilters.appendChild(label);
      });
    }

    // teamsがグローバル変数として定義されている場合
    if (typeof teams !== 'undefined') {
      const teamFilters = document.getElementById('teamFilters');
      teamFilters.innerHTML = '';
      teams.forEach(team => {
        const label = document.createElement('label');
        label.className = 'flex items-center space-x-2 mb-1';
        label.innerHTML = `
          <input type="checkbox" name="team_ids[]" value="${escapeHtml(team.team_id)}" class="rounded">
          <span class="text-sm">${escapeHtml(team.team_name)}</span>
        `;
        teamFilters.appendChild(label);
      });
    }

    // productsがグローバル変数として定義されている場合
    if (typeof products !== 'undefined') {
      const productFilters = document.getElementById('productFilters');
      productFilters.innerHTML = '';
      products.forEach(product => {
        const label = document.createElement('label');
        label.className = 'flex items-center space-x-2 mb-1';
        label.innerHTML = `
          <input type="checkbox" name="product_ids[]" value="${escapeHtml(product.product_id)}" class="rounded">
          <span class="text-sm">${escapeHtml(product.product_name)}</span>
        `;
        productFilters.appendChild(label);
      });
    }
  }

  // ========================================
  // ダッシュボード機能
  // ========================================

  let dashTrendChart = null;

  // ダッシュボードスコアカード更新
  function updateDashScoreCards(scoreCards) {
    if (document.getElementById('dashSalesAmount')) {
      document.getElementById('dashSalesAmount').textContent = '¥' + scoreCards.sales.current.toLocaleString();
      document.getElementById('dashSalesDiff').innerHTML = formatDashDiff(scoreCards.sales.diff, scoreCards.sales.diff_percent);
    }

    if (document.getElementById('dashSalesCount')) {
      document.getElementById('dashSalesCount').textContent = scoreCards.count.current.toLocaleString() + '件';
      document.getElementById('dashCountDiff').innerHTML = formatDashDiff(scoreCards.count.diff, scoreCards.count.diff_percent);
    }

    if (document.getElementById('dashProfitAmount')) {
      document.getElementById('dashProfitAmount').textContent = '¥' + scoreCards.profit.current.toLocaleString();
      document.getElementById('dashProfitDiff').innerHTML = formatDashDiff(scoreCards.profit.diff, scoreCards.profit.diff_percent);
    }
  }

  // 差分フォーマット
  function formatDashDiff(diff, percent) {
    const sign = diff >= 0 ? '+' : '';
    const color = diff >= 0 ? 'text-green-600' : 'text-red-600';
    const percentStr = percent.toFixed(1) + '%';
    const diffStr = '¥' + Math.abs(diff).toLocaleString();
    return `<span class="font-medium ${color}">${sign}${percentStr} (${sign}${diffStr})</span> <span class="text-gray-500">対前期間</span>`;
  }

  // 期間に基づいて自動的にグラフ表示単位を計算
  function calculateDashGranularity(startDate, endDate) {
    if (!startDate || !endDate) {
      return 'monthly'; // デフォルト
    }

    const start = new Date(startDate);
    const end = new Date(endDate);
    const daysDiff = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;

    if (isNaN(daysDiff)) {
      return 'monthly'; // デフォルト
    }

    if (daysDiff <= 31) {
      return 'daily';  // 31日以内は日次表示
    } else if (daysDiff <= 92) {
      return 'weekly'; // 約3ヶ月以内は週次表示
    } else if (daysDiff <= 365) {
      return 'monthly'; // 1年以内は月次表示
    } else if (daysDiff <= 730) {
      return 'quarterly'; // 2年以内は四半期表示
    } else {
      return 'yearly'; // それ以上は年次表示
    }
  }

  // 売上推移グラフ更新
  function updateDashTrendChart(trendData) {
    if (!document.getElementById('dashTrendChart')) return;

    const ctx = document.getElementById('dashTrendChart').getContext('2d');

    if (dashTrendChart) {
      dashTrendChart.destroy();
    }

    dashTrendChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: trendData.map(d => d.period),
        datasets: [          {
            label: '売上金額',
            data: trendData.map(d => d.sales),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0
          },
          {
            label: '粗利益',
            data: trendData.map(d => d.profit),
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'top',
          }
        },
        scales: {
          x: {
            ticks: {
              callback: function(value, index, ticks) {
                const label = this.getLabelForValue(value);
                // 一番左（最初）と一番右（最後）だけ西暦を表示
                if (index === 0 || index === ticks.length - 1) {
                  return label; // 西暦付き（YYYY-MM-DD）
                }
                // それ以外は月日のみ（MM-DD）
                if (label && label.includes('-')) {
                  const parts = label.split('-');
                  if (parts.length >= 3) {
                    return parts[1] + '-' + parts[2]; // MM-DD
                  } else if (parts.length === 2) {
                    return parts[1]; // 月次の場合はMM
                  }
                }
                return label;
              }
            }
          },
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '¥' + value.toLocaleString();
              }
            }
          }
        }
      }
    });
  }

  // 月別売上推移グラフ更新（1～12月固定）
  function updateMonthlyTrendChart(trendData, year) {
    if (!document.getElementById('dashTrendChart')) return;

    const ctx = document.getElementById('dashTrendChart').getContext('2d');

    if (dashTrendChart) {
      dashTrendChart.destroy();
    }

    // 1～12月のラベルを生成
    const monthLabels = Array.from({length: 12}, (_, i) => `${i + 1}月`);
    
    // データをマッピング（データがない月は0）
    const salesData = new Array(12).fill(0);
    const profitData = new Array(12).fill(0);
    
    trendData.forEach(d => {
      if (d.period && d.period.includes('-')) {
        const parts = d.period.split('-');
        if (parts.length >= 2) {
          const month = parseInt(parts[1]) - 1; // 0-indexed
          if (month >= 0 && month < 12) {
            salesData[month] = parseFloat(d.sales) || 0;
            profitData[month] = parseFloat(d.profit) || 0;
          }
        }
      }
    });

    dashTrendChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: monthLabels,
        datasets: [
          {
            label: '売上金額',
            data: salesData,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0,
            fill: true
          },
          {
            label: '粗利益',
            data: profitData,
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0,
            fill: true
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: `${year}年 月別売上推移`
          }
        },
        scales: {
          x: {
            title: {
              display: true,
              text: '月'
            }
          },
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '¥' + value.toLocaleString();
              }
            }
          }
        }
      }
    });
  }

  // 週別売上推移グラフ更新
  function updateWeeklyTrendChart(trendData) {
    if (!document.getElementById('dashTrendChart')) return;

    const ctx = document.getElementById('dashTrendChart').getContext('2d');

    if (dashTrendChart) {
      dashTrendChart.destroy();
    }

    // 週のラベルを生成（日付範囲または週番号）
    const labels = trendData.map(d => d.label || d.period);
    const salesData = trendData.map(d => parseFloat(d.sales) || 0);
    const profitData = trendData.map(d => parseFloat(d.profit) || 0);

    dashTrendChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: '売上金額',
            data: salesData,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0,
            fill: true
          },
          {
            label: '粗利益',
            data: profitData,
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0,
            fill: true
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'top',
          },
          title: {
            display: true,
            text: '週別売上推移'
          }
        },
        scales: {
          x: {
            title: {
              display: true,
              text: '週'
            },
            ticks: {
              maxRotation: 45,
              minRotation: 45
            }
          },
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '¥' + value.toLocaleString();
              }
            }
          }
        }
      }
    });
  }

  // ========================================
  // グラフセクション機能
  // ========================================

  let salesChart = null;
  let currentGraphTab = 'product_sales';
  let currentGraphApprovalFilter = 'approved';
  let cachedGraphData = null;

  // 棒グラフ初期化
  function initSalesChart() {
    const chartElement = document.getElementById('salesChart');
    if (!chartElement) {
      console.error('salesChart要素が見つかりません');
      return;
    }

    console.log('棒グラフを初期化します');
    const ctx = chartElement.getContext('2d');
    salesChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [{
          label: '売上金額',
          data: [],
          backgroundColor: 'rgba(59, 130, 246, 0.8)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return '¥' + value.toLocaleString();
              }
            }
          }
        },
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return '売上: ¥' + context.parsed.y.toLocaleString();
              }
            }
          }
        }
      }
    });
  }

  // グラフタブ切り替え
  function switchGraphTab(tab) {
    currentGraphTab = tab;

    // タブボタンのスタイル更新
    ['ProductSales', 'MemberSales', 'MemberProfit', 'ProductProfit', 'TeamSales', 'TeamProfit'].forEach(t => {
      const btn = document.getElementById(`graphTab${t}`);
      if (!btn) return;
      const tabKey = t.charAt(0).toLowerCase() + t.slice(1).replace(/([A-Z])/g, '_$1').toLowerCase();
      const isActive = tabKey === tab;
      btn.classList.toggle('bg-blue-600', isActive);
      btn.classList.toggle('text-white', isActive);
      btn.classList.toggle('font-medium', isActive);
      btn.classList.toggle('bg-gray-200', !isActive);
      btn.classList.toggle('text-gray-700', !isActive);
    });

    // グラフ更新
    updateChartByTab(tab);
  }

  // グラフタブに応じてグラフを更新
  function updateChartByTab(tab) {
    console.log('updateChartByTab呼び出し:', { tab, salesChart: !!salesChart, cachedGraphData: !!cachedGraphData });
    
    if (!salesChart) {
      console.error('salesChartが初期化されていません');
      return;
    }
    
    if (!cachedGraphData) {
      console.error('cachedGraphDataがありません');
      return;
    }

    const data = cachedGraphData[tab] || [];
    console.log(`${tab}のグラフデータ:`, data);
    const labels = data.map(d => d.label);
    const values = data.map(d => d.value);

    // グラフタイトルとラベルを変更
    let chartLabel = '';
    let valuePrefix = '';

    switch(tab) {
      case 'product_sales':
        chartLabel = '商品別売上';
        valuePrefix = '¥';
        break;
      case 'member_sales':
        chartLabel = 'メンバー別売上';
        valuePrefix = '¥';
        break;
      case 'member_profit':
        chartLabel = 'メンバー別粗利益';
        valuePrefix = '¥';
        break;
      case 'product_profit':
        chartLabel = '商品別粗利益';
        valuePrefix = '¥';
        break;
      case 'team_sales':
        chartLabel = 'チーム別売上';
        valuePrefix = '¥';
        break;
      case 'team_profit':
        chartLabel = 'チーム別粗利益';
        valuePrefix = '¥';
        break;
    }

    salesChart.data.labels = labels;
    salesChart.data.datasets[0].label = chartLabel;
    salesChart.data.datasets[0].data = values;

    // Y軸のフォーマット更新
    salesChart.options.scales.y.ticks.callback = function(value) {
      return valuePrefix + value.toLocaleString();
    };

    // ツールチップのフォーマット更新
    salesChart.options.plugins.tooltip.callbacks.label = function(context) {
      return chartLabel + ': ' + valuePrefix + context.parsed.y.toLocaleString();
    };

    salesChart.update();
  }

  // グラフ用承認フィルタ切り替え
  function toggleGraphApprovalFilter(filter) {
    currentGraphApprovalFilter = filter;

    const btnApproved = document.getElementById('btnGraphApproved');
    const btnAll = document.getElementById('btnGraphAll');

    if (!btnApproved || !btnAll) return;

    if (filter === 'approved') {
      btnApproved.className = 'px-4 py-2 rounded bg-blue-600 text-white font-medium';
      btnAll.className = 'px-4 py-2 rounded bg-gray-200 text-gray-700';
    } else {
      btnApproved.className = 'px-4 py-2 rounded bg-gray-200 text-gray-700';
      btnAll.className = 'px-4 py-2 rounded bg-blue-600 text-white font-medium';
    }

    // データ再読み込み
    if (currentStartDate && currentEndDate) {
      loadData();
    }
  }

  // ========================================
  // データテーブル機能
  // ========================================

  let currentDataTab = 'members';
  let membersData = [];
  let teamsData = [];
  let productsData = [];
  let currentMembersSortColumn = 'total_sales';
  let currentMembersSortOrder = 'desc';
  let currentTeamsSortColumn = 'total_sales';
  let currentTeamsSortOrder = 'desc';
  let currentProductsSortColumn = 'total_sales';
  let currentProductsSortOrder = 'desc';

  // データテーブルタブ切り替え
  function switchDataTab(tab) {
    currentDataTab = tab;

    // タブボタンのスタイル更新
    ['Members', 'Teams', 'Products'].forEach(t => {
      const btn = document.getElementById(`tab${t}`);
      if (!btn) return;
      const isActive = t.toLowerCase() === tab;
      btn.classList.toggle('border-blue-500', isActive);
      btn.classList.toggle('text-blue-600', isActive);
      btn.classList.toggle('font-medium', isActive);
      btn.classList.toggle('text-gray-600', !isActive);
    });

    // コンテンツ表示切り替え
    const membersTab = document.getElementById('membersTab');
    const teamsTab = document.getElementById('teamsTab');
    const productsTab = document.getElementById('productsTab');
    if (membersTab) membersTab.classList.toggle('hidden', tab !== 'members');
    if (teamsTab) teamsTab.classList.toggle('hidden', tab !== 'teams');
    if (productsTab) productsTab.classList.toggle('hidden', tab !== 'products');
  }

  // メンバー別実績テーブルソート
  function sortMembersTable(column) {
    // 同じカラムをクリックした場合は昇順/降順を切り替え
    if (currentMembersSortColumn === column) {
      currentMembersSortOrder = currentMembersSortOrder === 'desc' ? 'asc' : 'desc';
    } else {
      currentMembersSortColumn = column;
      currentMembersSortOrder = 'desc'; // 新しいカラムは降順から開始
    }

    // データをソート
    membersData.sort((a, b) => {
      let aVal, bVal;
      
      switch(column) {
        case 'member_name':
        case 'team_name':
          aVal = (a[column] || '').toString();
          bVal = (b[column] || '').toString();
          break;
        case 'sales_count':
        case 'total_sales':
        case 'total_profit':
          aVal = parseFloat(a[column]) || 0;
          bVal = parseFloat(b[column]) || 0;
          break;
        default:
          return 0;
      }

      if (currentMembersSortOrder === 'desc') {
        return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
      } else {
        return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
      }
    });

    // テーブル再描画
    renderMembersTableFromCache();
    updateMembersSortIndicators();
  }

  // 商品別実績テーブルソート
  function sortProductsTable(column) {
    // 同じカラムをクリックした場合は昇順/降順を切り替え
    if (currentProductsSortColumn === column) {
      currentProductsSortOrder = currentProductsSortOrder === 'desc' ? 'asc' : 'desc';
    } else {
      currentProductsSortColumn = column;
      currentProductsSortOrder = 'desc'; // 新しいカラムは降順から開始
    }

    // データをソート
    productsData.sort((a, b) => {
      let aVal, bVal;
      
      switch(column) {
        case 'product_name':
          aVal = (a[column] || '').toString();
          bVal = (b[column] || '').toString();
          break;
        case 'total_quantity':
        case 'total_sales':
        case 'total_profit':
        case 'avg_price':
          aVal = parseFloat(a[column]) || 0;
          bVal = parseFloat(b[column]) || 0;
          break;
        default:
          return 0;
      }

      if (currentProductsSortOrder === 'desc') {
        return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
      } else {
        return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
      }
    });

    // テーブル再描画
    renderProductsTableFromCache();
    updateProductsSortIndicators();
  }

  // ソートインジケーター更新（メンバー）
  function updateMembersSortIndicators() {
    const indicators = {
      'member_name': 'sortIndicatorMemberName',
      'team_name': 'sortIndicatorTeamName',
      'sales_count': 'sortIndicatorSalesCount',
      'total_sales': 'sortIndicatorTotalSales',
      'total_profit': 'sortIndicatorTotalProfit'
    };

    Object.entries(indicators).forEach(([column, indicatorId]) => {
      const indicator = document.getElementById(indicatorId);
      if (!indicator) return;
      
      if (column === currentMembersSortColumn) {
        indicator.textContent = currentMembersSortOrder === 'desc' ? '▼' : '▲';
      } else {
        indicator.textContent = '';
      }
    });
  }

  // ソートインジケーター更新（商品）
  function updateProductsSortIndicators() {
    const indicators = {
      'product_name': 'sortIndicatorProductName',
      'total_quantity': 'sortIndicatorTotalQuantity',
      'total_sales': 'sortIndicatorProductTotalSales',
      'total_profit': 'sortIndicatorTotalProfit',
      'avg_price': 'sortIndicatorAvgPrice'
    };

    Object.entries(indicators).forEach(([column, indicatorId]) => {
      const indicator = document.getElementById(indicatorId);
      if (!indicator) return;
      
      if (column === currentProductsSortColumn) {
        indicator.textContent = currentProductsSortOrder === 'desc' ? '▼' : '▲';
      } else {
        indicator.textContent = '';
      }
    });
  }

  // メンバー別実績テーブル描画（キャッシュから）
  function renderMembersTableFromCache() {
    const tbody = document.getElementById('membersTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (membersData.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
      return;
    }

    membersData.forEach(member => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(member.member_name)}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(member.team_name || '-')}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${member.sales_count}件</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(member.total_sales).toLocaleString()}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">¥${parseFloat(member.total_profit || 0).toLocaleString()}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  // 商品別実績テーブル描画（キャッシュから）
  function renderProductsTableFromCache() {
    const tbody = document.getElementById('productsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (productsData.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
      return;
    }

    productsData.forEach(product => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(product.product_name)}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(product.avg_price).toLocaleString()}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${product.total_quantity}個</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(product.total_sales).toLocaleString()}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">¥${parseFloat(product.total_profit || 0).toLocaleString()}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  // チーム別実績テーブルソート
  function sortTeamsTable(column) {
    // 同じカラムをクリックした場合は昇順/降順を切り替え
    if (currentTeamsSortColumn === column) {
      currentTeamsSortOrder = currentTeamsSortOrder === 'desc' ? 'asc' : 'desc';
    } else {
      currentTeamsSortColumn = column;
      currentTeamsSortOrder = 'desc'; // 新しいカラムは降順から開始
    }

    // データをソート
    teamsData.sort((a, b) => {
      let aVal, bVal;
      
      switch(column) {
        case 'team_name':
          aVal = (a[column] || '').toString();
          bVal = (b[column] || '').toString();
          break;
        case 'sales_count':
        case 'total_sales':
        case 'total_profit':
          aVal = parseFloat(a[column]) || 0;
          bVal = parseFloat(b[column]) || 0;
          break;
        default:
          return 0;
      }

      if (currentTeamsSortOrder === 'desc') {
        return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
      } else {
        return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
      }
    });

    // テーブル再描画
    renderTeamsTableFromCache();
    updateTeamsSortIndicators();
  }

  // ソートインジケーター更新（チーム）
  function updateTeamsSortIndicators() {
    const indicators = {
      'team_name': 'sortIndicatorTeamNameTable',
      'sales_count': 'sortIndicatorTeamSalesCount',
      'total_sales': 'sortIndicatorTeamTotalSales',
      'total_profit': 'sortIndicatorTeamTotalProfit'
    };

    Object.entries(indicators).forEach(([column, indicatorId]) => {
      const indicator = document.getElementById(indicatorId);
      if (!indicator) return;
      
      if (column === currentTeamsSortColumn) {
        indicator.textContent = currentTeamsSortOrder === 'desc' ? '▼' : '▲';
      } else {
        indicator.textContent = '';
      }
    });
  }

  // チーム別実績テーブル描画（キャッシュから）
  function renderTeamsTableFromCache() {
    const tbody = document.getElementById('teamsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (teamsData.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">データがありません</td></tr>';
      return;
    }

    teamsData.forEach(team => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${escapeHtml(team.team_name)}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${team.sales_count}件</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">¥${parseFloat(team.total_sales).toLocaleString()}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">¥${parseFloat(team.total_profit || 0).toLocaleString()}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  // メンバー別実績テーブル描画（外部から呼ばれる）
  function renderMembersTable(members) {
    membersData = members || [];
    // デフォルトソートを適用
    currentMembersSortColumn = 'total_sales';
    currentMembersSortOrder = 'desc';
    sortMembersTable(currentMembersSortColumn);
  }

  // 商品別実績テーブル描画（外部から呼ばれる）
  function renderProductsTable(products) {
    productsData = products || [];
    // デフォルトソートを適用
    currentProductsSortColumn = 'total_sales';
    currentProductsSortOrder = 'desc';
    sortProductsTable(currentProductsSortColumn);
  }

  // チーム別実績テーブル描画（外部から呼ばれる）
  function renderTeamsTable(teams) {
    teamsData = teams || [];
    // デフォルトソートを適用
    currentTeamsSortColumn = 'total_sales';
    currentTeamsSortOrder = 'desc';
    sortTeamsTable(currentTeamsSortColumn);
  }

  // ページ読み込み時の初期化
  document.addEventListener('DOMContentLoaded', () => {
    // フィルタ選択肢の読み込み
    loadFilterOptions();
    // デフォルト期間を適用
    applyPreset();
    // グラフ初期化
    initSalesChart();
    // データ読み込み
    loadData();
  });
</script>

