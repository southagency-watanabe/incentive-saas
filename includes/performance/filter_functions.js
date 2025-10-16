/**
 * 共通フィルタ関数
 * 全ての実績管理ページで使用可能
 */

// ダッシュボードフィルタ選択肢の読み込み
function loadDashboardFilters() {
  console.log('🔧 loadDashboardFilters() 開始');
  console.log('👥 Members データ:', typeof members !== 'undefined' ? members.length : 'undefined');
  console.log('👥 Teams データ:', typeof teams !== 'undefined' ? teams.length : 'undefined');
  console.log('📦 Products データ:', typeof products !== 'undefined' ? products.length : 'undefined');

  // メンバーフィルタ
  if (typeof members !== 'undefined') {
    const memberFilters = document.getElementById('dashMemberFilters');
    if (memberFilters) {
      memberFilters.innerHTML = '';
      console.log(`👥 メンバーフィルタ生成: ${members.length}件`);
      members.forEach(member => {
        const label = document.createElement('label');
        label.className = 'flex items-center space-x-2 mb-1';
        label.innerHTML = `
          <input type="checkbox" name="dash_member_ids[]" value="${escapeHtml(member.member_id)}" class="rounded">
          <span class="text-sm">${escapeHtml(member.name)}</span>
        `;
        memberFilters.appendChild(label);
      });
    } else {
      console.warn('⚠️  dashMemberFilters要素が見つかりません');
    }
  }

  // チームフィルタ
  if (typeof teams !== 'undefined') {
    const teamFilters = document.getElementById('dashTeamFilters');
    if (teamFilters) {
      teamFilters.innerHTML = '';
      console.log(`👥 チームフィルタ生成: ${teams.length}件`);
      teams.forEach(team => {
        const label = document.createElement('label');
        label.className = 'flex items-center space-x-2 mb-1';
        label.innerHTML = `
          <input type="checkbox" name="dash_team_ids[]" value="${escapeHtml(team.team_id)}" class="rounded">
          <span class="text-sm">${escapeHtml(team.team_name)}</span>
        `;
        teamFilters.appendChild(label);
      });

      // 生成後に確認
      const checkboxes = teamFilters.querySelectorAll('input[type="checkbox"]');
      console.log(`✅ チームチェックボックス生成完了: ${checkboxes.length}個`);
    } else {
      console.warn('⚠️  dashTeamFilters要素が見つかりません');
    }
  }

  // 商品フィルタ
  if (typeof products !== 'undefined') {
    const productFilters = document.getElementById('dashProductFilters');
    if (productFilters) {
      productFilters.innerHTML = '';
      console.log(`📦 商品フィルタ生成: ${products.length}件`);
      products.forEach(product => {
        const label = document.createElement('label');
        label.className = 'flex items-center space-x-2 mb-1';
        label.innerHTML = `
          <input type="checkbox" name="dash_product_ids[]" value="${escapeHtml(product.product_id)}" class="rounded">
          <span class="text-sm">${escapeHtml(product.product_name)}</span>
        `;
        productFilters.appendChild(label);
      });
    } else {
      console.warn('⚠️  dashProductFilters要素が見つかりません');
    }
  }

  console.log('✅ loadDashboardFilters() 完了');
}

// フィルタパラメータを収集
function collectFilterParams() {
  const memberIds = Array.from(document.querySelectorAll('input[name="dash_member_ids[]"]:checked'))
    .map(cb => cb.value).join(',');
  const teamIds = Array.from(document.querySelectorAll('input[name="dash_team_ids[]"]:checked'))
    .map(cb => cb.value).join(',');
  const productIds = Array.from(document.querySelectorAll('input[name="dash_product_ids[]"]:checked'))
    .map(cb => cb.value).join(',');
  const searchText = document.getElementById('dashSearchText')?.value || '';

  console.log('🔍 フィルタパラメータ収集:', {
    memberIds: memberIds || '(なし)',
    teamIds: teamIds || '(なし)',
    productIds: productIds || '(なし)',
    searchText: searchText || '(なし)',
    'チームチェックボックス総数': document.querySelectorAll('input[name="dash_team_ids[]"]').length,
    'チェック済みチーム数': document.querySelectorAll('input[name="dash_team_ids[]"]:checked').length
  });

  return { memberIds, teamIds, productIds, searchText };
}

// ダッシュボードフィルタ詳細の開閉
function toggleDashFilterDetails() {
  const details = document.getElementById('dashFilterDetails');
  const arrow = document.getElementById('dashFilterArrow');

  if (!details || !arrow) {
    console.error('フィルタ詳細要素が見つかりません');
    return;
  }

  const isHidden = details.classList.contains('hidden');

  if (isHidden) {
    details.classList.remove('hidden');
    arrow.style.transform = 'rotate(180deg)';
  } else {
    details.classList.add('hidden');
    arrow.style.transform = 'rotate(0deg)';
  }
}

// 日付をローカルタイムゾーンでYYYY-MM-DD形式にフォーマット
function formatDateLocal(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

// ダッシュボードプリセット適用
function applyDashPreset() {
  const preset = document.getElementById('dashPeriodPreset')?.value;
  if (!preset) {
    console.error('dashPeriodPreset要素が見つかりません');
    return;
  }

  const today = new Date();
  let startDate, endDate;

  switch (preset) {
    case 'today':
      startDate = endDate = formatDateLocal(today);
      break;
    case 'this_week':
      const dayOfWeek = today.getDay();
      const monday = new Date(today);
      monday.setDate(today.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));
      startDate = formatDateLocal(monday);
      endDate = formatDateLocal(today);
      break;
    case 'this_month':
      startDate = formatDateLocal(new Date(today.getFullYear(), today.getMonth(), 1));
      endDate = formatDateLocal(new Date(today.getFullYear(), today.getMonth() + 1, 0));
      break;
    case 'last_month':
      startDate = formatDateLocal(new Date(today.getFullYear(), today.getMonth() - 1, 1));
      endDate = formatDateLocal(new Date(today.getFullYear(), today.getMonth(), 0));
      break;
    case 'this_quarter':
      const quarter = Math.floor(today.getMonth() / 3);
      startDate = formatDateLocal(new Date(today.getFullYear(), quarter * 3, 1));
      endDate = formatDateLocal(new Date(today.getFullYear(), (quarter + 1) * 3, 0));
      break;
    case 'this_year':
      startDate = formatDateLocal(new Date(today.getFullYear(), 0, 1));
      endDate = formatDateLocal(new Date(today.getFullYear(), 11, 31));
      break;
    default:
      console.warn('不明なプリセット:', preset);
      return;
  }

  const startDateInput = document.getElementById('dashStartDate');
  const endDateInput = document.getElementById('dashEndDate');

  if (startDateInput && endDateInput) {
    startDateInput.value = startDate;
    endDateInput.value = endDate;
    console.log('📅 日付プリセット適用:', { preset, startDate, endDate });
  }
}

// HTMLエスケープ
function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
