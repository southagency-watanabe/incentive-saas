<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

// 管理者権限チェック
requireAdmin();

$pdo = getDB();
$tenant_id = $_SESSION['tenant_id'];

try {
  // 期間パラメータ取得
  $period = $_GET['period'] ?? 'current_month';
  $approval_filter = $_GET['approval_filter'] ?? 'approved'; // approved または all
  $start_date = null;
  $end_date = null;

  // 期間に応じた日付範囲を設定
  switch ($period) {
    case 'current_month':
      $start_date = date('Y-m-01');
      $end_date = date('Y-m-t');
      break;
    case 'last_month':
      $start_date = date('Y-m-01', strtotime('first day of last month'));
      $end_date = date('Y-m-t', strtotime('last day of last month'));
      break;
    case 'current_year':
      $start_date = date('Y-01-01');
      $end_date = date('Y-12-31');
      break;
    case 'custom':
      $start_date = $_GET['start_date'] ?? null;
      $end_date = $_GET['end_date'] ?? null;
      if (!$start_date || !$end_date) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '開始日と終了日を指定してください。']);
        exit;
      }
      break;
    case 'all':
      // 全期間
      break;
  }

  // WHERE句の構築
  $where_clause = "sr.tenant_id = :tenant_id";
  if ($approval_filter === 'approved') {
    $where_clause .= " AND sr.approval_status = '承認済み'";
  }
  $params = ['tenant_id' => $tenant_id];

  if ($start_date && $end_date) {
    $where_clause .= " AND sr.date BETWEEN :start_date AND :end_date";
    $params['start_date'] = $start_date;
    $params['end_date'] = $end_date;
  }

  // サマリーデータ取得（粗利益を含む）
  $stmt = $pdo->prepare("
    SELECT
      COUNT(*) as total_count,
      COALESCE(SUM(sr.quantity * sr.unit_price), 0) as total_sales,
      COALESCE(SUM(sr.base_point), 0) as base_points,
      COALESCE(SUM(sr.final_point), 0) as total_points,
      COALESCE(SUM(sr.quantity * (sr.unit_price - p.cost)), 0) as total_profit
    FROM sales_records sr
    LEFT JOIN products p ON sr.tenant_id = p.tenant_id AND sr.product_id = p.product_id
    WHERE {$where_clause}
  ");
  $stmt->execute($params);
  $summary_raw = $stmt->fetch();

  // 承認率計算
  $stmt = $pdo->prepare("
    SELECT
      COUNT(*) as all_count,
      SUM(CASE WHEN approval_status = '承認済み' THEN 1 ELSE 0 END) as approved_count
    FROM sales_records
    WHERE tenant_id = :tenant_id
    " . ($start_date && $end_date ? "AND date BETWEEN :start_date AND :end_date" : "")
  );
  $stmt->execute($params);
  $approval_data = $stmt->fetch();
  $approval_rate = $approval_data['all_count'] > 0
    ? ($approval_data['approved_count'] / $approval_data['all_count']) * 100
    : 0;

  $summary = [
    'total_count' => (int)$summary_raw['total_count'],
    'total_sales' => (float)$summary_raw['total_sales'],
    'base_points' => (int)$summary_raw['base_points'],
    'total_points' => (int)$summary_raw['total_points'],
    'total_profit' => (float)$summary_raw['total_profit'],
    'approval_rate' => (float)$approval_rate
  ];

  // メンバー別実績（粗利益を含む）
  $member_where = $approval_filter === 'approved' ? "AND sr.approval_status = '承認済み'" : "";
  $stmt = $pdo->prepare("
    SELECT
      m.member_id,
      m.name as member_name,
      t.team_name,
      COUNT(sr.id) as sales_count,
      COALESCE(SUM(sr.quantity * sr.unit_price), 0) as total_sales,
      COALESCE(SUM(sr.base_point), 0) as base_points,
      COALESCE(SUM(sr.final_point), 0) as final_points,
      COALESCE(AVG(sr.event_multiplier), 1.0) as avg_multiplier,
      COALESCE(SUM(sr.quantity * (sr.unit_price - p.cost)), 0) as total_profit
    FROM members m
    LEFT JOIN teams t ON m.tenant_id = t.tenant_id AND m.team_id = t.team_id
    LEFT JOIN sales_records sr ON m.tenant_id = sr.tenant_id AND m.member_id = sr.member_id
    LEFT JOIN products p ON sr.tenant_id = p.tenant_id AND sr.product_id = p.product_id
      {$member_where}
      " . ($start_date && $end_date ? "AND sr.date BETWEEN :start_date AND :end_date" : "") . "
    WHERE m.tenant_id = :tenant_id AND m.status = '有効'
    GROUP BY m.member_id, m.name, t.team_name
    ORDER BY final_points DESC
  ");
  $stmt->execute($params);
  $members = $stmt->fetchAll();

  // 商品別実績（粗利益を含む）
  $product_where = $approval_filter === 'approved' ? "AND sr.approval_status = '承認済み'" : "";
  $stmt = $pdo->prepare("
    SELECT
      p.product_id,
      p.product_name,
      p.cost,
      COALESCE(SUM(sr.quantity), 0) as total_quantity,
      COALESCE(SUM(sr.quantity * sr.unit_price), 0) as total_sales,
      COALESCE(SUM(sr.final_point), 0) as total_points,
      COALESCE(AVG(sr.unit_price), 0) as avg_price,
      COALESCE(SUM(sr.quantity * (sr.unit_price - p.cost)), 0) as total_profit
    FROM products p
    LEFT JOIN sales_records sr ON p.tenant_id = sr.tenant_id AND p.product_id = sr.product_id
      {$product_where}
      " . ($start_date && $end_date ? "AND sr.date BETWEEN :start_date AND :end_date" : "") . "
    WHERE p.tenant_id = :tenant_id AND p.status = '有効'
    GROUP BY p.product_id, p.product_name, p.cost
    HAVING total_quantity > 0
    ORDER BY total_sales DESC
  ");
  $stmt->execute($params);
  $products = $stmt->fetchAll();

  // ランキング（売上金額TOP10）
  $ranking_where = $approval_filter === 'approved' ? "AND sr.approval_status = '承認済み'" : "";
  $stmt = $pdo->prepare("
    SELECT
      m.member_id,
      m.name as member_name,
      COALESCE(SUM(sr.quantity * sr.unit_price), 0) as total_sales
    FROM members m
    INNER JOIN sales_records sr ON m.tenant_id = sr.tenant_id AND m.member_id = sr.member_id
    WHERE m.tenant_id = :tenant_id
      AND m.status = '有効'
      {$ranking_where}
      " . ($start_date && $end_date ? "AND sr.date BETWEEN :start_date AND :end_date" : "") . "
    GROUP BY m.member_id, m.name
    HAVING total_sales > 0
    ORDER BY total_sales DESC
    LIMIT 10
  ");
  $stmt->execute($params);
  $sales_ranking = $stmt->fetchAll();

  // ランキング（ポイントTOP10）
  $stmt = $pdo->prepare("
    SELECT
      m.member_id,
      m.name as member_name,
      COALESCE(SUM(sr.final_point), 0) as final_points
    FROM members m
    INNER JOIN sales_records sr ON m.tenant_id = sr.tenant_id AND m.member_id = sr.member_id
    WHERE m.tenant_id = :tenant_id
      AND m.status = '有効'
      {$ranking_where}
      " . ($start_date && $end_date ? "AND sr.date BETWEEN :start_date AND :end_date" : "") . "
    GROUP BY m.member_id, m.name
    HAVING final_points > 0
    ORDER BY final_points DESC
    LIMIT 10
  ");
  $stmt->execute($params);
  $points_ranking = $stmt->fetchAll();

  // グラフ用データ構造を作成
  // 1. 商品別売上TOP10（デフォルトグラフ）
  $product_sales_graph = array_slice(array_map(function($p) {
    return [
      'label' => $p['product_name'],
      'value' => (float)$p['total_sales']
    ];
  }, $products), 0, 10);

  // 2. メンバー別粗利益TOP10
  $member_profit_sorted = $members;
  usort($member_profit_sorted, function($a, $b) {
    return $b['total_profit'] - $a['total_profit'];
  });
  $member_profit_graph = array_slice(array_map(function($m) {
    return [
      'label' => $m['member_name'],
      'value' => (float)$m['total_profit']
    ];
  }, $member_profit_sorted), 0, 10);

  // 3. 商品別粗利益TOP10
  $product_profit_sorted = $products;
  usort($product_profit_sorted, function($a, $b) {
    return $b['total_profit'] - $a['total_profit'];
  });
  $product_profit_graph = array_slice(array_map(function($p) {
    return [
      'label' => $p['product_name'],
      'value' => (float)$p['total_profit']
    ];
  }, $product_profit_sorted), 0, 10);

  // 4. メンバー別売上TOP10（現在のグラフ）
  $member_sales_graph = array_slice(array_map(function($m) {
    return [
      'label' => $m['member_name'],
      'value' => (float)$m['total_sales']
    ];
  }, $members), 0, 10);

  echo json_encode([
    'success' => true,
    'summary' => $summary,
    'members' => $members,
    'products' => $products,
    'rankings' => [
      'sales' => $sales_ranking,
      'points' => $points_ranking
    ],
    'graphs' => [
      'product_sales' => $product_sales_graph,
      'member_profit' => $member_profit_graph,
      'product_profit' => $product_profit_graph,
      'member_sales' => $member_sales_graph
    ]
  ]);
} catch (PDOException $e) {
  error_log('Performance API error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'サーバーエラーが発生しました。']);
}
