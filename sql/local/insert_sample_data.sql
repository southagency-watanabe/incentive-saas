-- サンプルデータ挿入スクリプト
USE incentive_local;

-- テナントデータ
INSERT INTO tenants (tenant_id, tenant_name, status) VALUES
('DEMO01', 'デモテナント', '有効');

-- チームデータ
INSERT INTO teams (team_id, tenant_id, team_name, status) VALUES
('TEAM001', 'DEMO01', '営業部', '有効'),
('TEAM002', 'DEMO01', 'マーケティング部', '有効');

-- メンバーデータ
INSERT INTO members (member_id, tenant_id, login_id, name, role, pin, team_id, status) VALUES
('MEMBER001', 'DEMO01', 'admin', '管理者', 'admin', '1234', 'TEAM001', '有効'),
('MEMBER002', 'DEMO01', 'user1', '田中太郎', 'user', '5678', 'TEAM001', '有効'),
('MEMBER003', 'DEMO01', 'user2', '佐藤花子', 'user', '9012', 'TEAM002', '有効');

-- 商品データ
INSERT INTO products (product_id, tenant_id, product_name, large_category, medium_category, small_category, point, price, cost, status, approval_required) VALUES
('PROD001', 'DEMO01', '商品A', 'カテゴリ1', 'サブカテゴリ1', '詳細カテゴリ1', 100, 1000.00, 500.00, '有効', FALSE),
('PROD002', 'DEMO01', '商品B', 'カテゴリ1', 'サブカテゴリ2', '詳細カテゴリ2', 200, 2000.00, 1000.00, '有効', TRUE),
('PROD003', 'DEMO01', '商品C', 'カテゴリ2', 'サブカテゴリ3', '詳細カテゴリ3', 150, 1500.00, 750.00, '有効', FALSE);

-- タスクデータ
INSERT INTO tasks (task_id, tenant_id, task_name, type, repeat_type, point, daily_limit, approval_required, status) VALUES
('TASK001', 'DEMO01', '朝礼参加', '日次', '毎日', 10, 1, FALSE, '有効'),
('TASK002', 'DEMO01', '週次レポート作成', '週次', '毎週', 50, 1, TRUE, '有効'),
('TASK003', 'DEMO01', '月次会議参加', '月次', '毎月', 100, 1, FALSE, '有効');

-- アクションデータ
INSERT INTO actions (action_id, tenant_id, action_name, target, point, approval_required, status) VALUES
('ACTION001', 'DEMO01', '新規顧客開拓', '全員', 200, TRUE, '有効'),
('ACTION002', 'DEMO01', '既存顧客フォロー', '全員', 100, FALSE, '有効'),
('ACTION003', 'DEMO01', 'チーム貢献', 'チーム', 50, TRUE, '有効');

-- イベントデータ
INSERT INTO events (event_id, tenant_id, event_name, start_date, end_date, multiplier, status) VALUES
('EVENT001', 'DEMO01', '夏季キャンペーン', '2025-07-01', '2025-08-31', 1.50, '有効'),
('EVENT002', 'DEMO01', '年末キャンペーン', '2025-12-01', '2025-12-31', 2.00, '有効');

-- 売上記録データ（サンプル）
INSERT INTO sales_records (tenant_id, member_id, product_id, date, quantity, unit_price, base_point, event_multiplier, final_point, approval_status) VALUES
('DEMO01', 'MEMBER002', 'PROD001', '2025-10-01', 2, 1000.00, 200, 1.00, 200, '承認済み'),
('DEMO01', 'MEMBER002', 'PROD002', '2025-10-02', 1, 2000.00, 200, 1.50, 300, 'ユーザー確認待ち'),
('DEMO01', 'MEMBER003', 'PROD003', '2025-10-03', 3, 1500.00, 450, 1.00, 450, '承認待ち');

-- タスク記録データ（サンプル）
INSERT INTO task_records (tenant_id, member_id, task_id, date, point, approval_status) VALUES
('DEMO01', 'MEMBER002', 'TASK001', '2025-10-01', 10, '承認済み'),
('DEMO01', 'MEMBER002', 'TASK001', '2025-10-02', 10, '承認済み'),
('DEMO01', 'MEMBER003', 'TASK002', '2025-10-01', 50, 'ユーザー確認待ち');

-- アクション記録データ（サンプル）
INSERT INTO action_records (tenant_id, member_id, action_id, date, point, approval_status) VALUES
('DEMO01', 'MEMBER002', 'ACTION001', '2025-10-01', 200, '承認済み'),
('DEMO01', 'MEMBER003', 'ACTION002', '2025-10-02', 100, 'ユーザー確認待ち');

-- お知らせデータ
INSERT INTO bulletins (tenant_id, event_id, title, content) VALUES
('DEMO01', 'EVENT001', '夏季キャンペーン開始のお知らせ', '7月1日から夏季キャンペーンが開始されます。期間中はポイントが1.5倍になります。'),
('DEMO01', 'EVENT002', '年末キャンペーンのお知らせ', '12月1日から年末キャンペーンが開始されます。期間中はポイントが2倍になります。');
