-- ============================================
-- インセンティブSaaS テストデータ挿入（本番環境用）
-- CLUB高濱のサンプルデータ
-- ============================================

USE xs063745_incentive;

-- ============================================
-- データクリーンアップ（既存のDEMO01データを削除）
-- ============================================
DELETE FROM action_records WHERE tenant_id = 'DEMO01';
DELETE FROM task_records WHERE tenant_id = 'DEMO01';
DELETE FROM sales_records WHERE tenant_id = 'DEMO01';
DELETE FROM bulletins WHERE tenant_id = 'DEMO01';
DELETE FROM event_action_category_multipliers WHERE tenant_id = 'DEMO01';
DELETE FROM event_action_multipliers WHERE tenant_id = 'DEMO01';
DELETE FROM event_product_category_multipliers WHERE tenant_id = 'DEMO01';
DELETE FROM event_product_multipliers WHERE tenant_id = 'DEMO01';
DELETE FROM events WHERE tenant_id = 'DEMO01';
DELETE FROM actions WHERE tenant_id = 'DEMO01';
DELETE FROM tasks WHERE tenant_id = 'DEMO01';
DELETE FROM products WHERE tenant_id = 'DEMO01';
DELETE FROM members WHERE tenant_id = 'DEMO01';
DELETE FROM teams WHERE tenant_id = 'DEMO01';
DELETE FROM tenants WHERE tenant_id = 'DEMO01';

-- ============================================
-- テナント情報（キャバクラ：CLUB高濱）
-- ============================================
INSERT INTO tenants (tenant_id, tenant_name, status) VALUES
('DEMO01', 'CLUB高濱', '有効');

-- ============================================
-- チーム情報（キャスト名チーム）
-- ============================================
INSERT INTO teams (team_id, tenant_id, team_name, leader_id) VALUES
('TEAM001', 'DEMO01', '美咲チーム', 'MEM002'),
('TEAM002', 'DEMO01', '由紀子チーム', 'MEM003'),
('TEAM003', 'DEMO01', '店長', 'MEM001');

-- ============================================
-- メンバー情報（源氏名のキャスト）
-- ============================================
INSERT INTO members (member_id, tenant_id, login_id, name, role, pin, team_id, status) VALUES
('MEM001', 'DEMO01', 'manager', '店長', 'admin', '1111', 'TEAM003', '有効'),
('MEM002', 'DEMO01', 'misaki', '美咲', 'user', '2222', 'TEAM001', '有効'),
('MEM003', 'DEMO01', 'yukiko', '由紀子', 'user', '3333', 'TEAM002', '有効'),
('MEM004', 'DEMO01', 'rina', 'りな', 'user', '4444', 'TEAM001', '有効'),
('MEM005', 'DEMO01', 'ayaka', 'あやか', 'user', '5555', 'TEAM002', '有効'),
('MEM006', 'DEMO01', 'nanami', 'ななみ', 'user', '6666', 'TEAM001', '有効'),
('MEM007', 'DEMO01', 'miki', '美樹', 'user', '7777', 'TEAM002', '有効'),
('MEM008', 'DEMO01', 'sakura', 'さくら', 'user', '8888', 'TEAM001', '有効'),
('MEM009', 'DEMO01', 'haruka', 'はるか', 'user', '9999', 'TEAM002', '有効'),
('MEM010', 'DEMO01', 'yui', 'ゆい', 'user', '1000', 'TEAM001', '有効');

-- ============================================
-- 商品マスタ（キャバクラメニュー）
-- ============================================
INSERT INTO products (product_id, tenant_id, product_name, large_category, medium_category, small_category, price, cost, point, status, approval_required) VALUES
-- シャンパン
('PROD001', 'DEMO01', 'モエ・エ・シャンドン', '飲食', 'シャンパン', 'シャンパン', 35000.00, 12000.00, 3500, '有効', 0),
('PROD002', 'DEMO01', 'ヴーヴ・クリコ', '飲食', 'シャンパン', 'シャンパン', 40000.00, 15000.00, 4000, '有効', 0),
('PROD003', 'DEMO01', 'ドンペリニヨン', '飲食', 'シャンパン', 'シャンパン', 80000.00, 35000.00, 8000, '有効', 0),
('PROD004', 'DEMO01', 'ドンペリニヨン・ロゼ', '飲食', 'シャンパン', 'シャンパン', 150000.00, 70000.00, 15000, '有効', 0),
('PROD005', 'DEMO01', 'アルマンド・ブリニャック', '飲食', 'シャンパン', 'シャンパン', 200000.00, 90000.00, 20000, '有効', 0),

-- ワイン
('PROD006', 'DEMO01', 'オーパスワン', '飲食', 'ワイン', 'ワイン', 120000.00, 50000.00, 12000, '有効', 0),
('PROD007', 'DEMO01', 'シャトー・マルゴー', '飲食', 'ワイン', 'ワイン', 180000.00, 80000.00, 18000, '有効', 0),

-- ボトル（焼酎・ウイスキー）
('PROD008', 'DEMO01', '森伊蔵', '飲食', 'ボトル', '焼酎', 25000.00, 8000.00, 2500, '有効', 0),
('PROD009', 'DEMO01', '魔王', '飲食', 'ボトル', '焼酎', 18000.00, 6000.00, 1800, '有効', 0),
('PROD010', 'DEMO01', 'ジャックダニエル', '飲食', 'ボトル', 'ウイスキー', 20000.00, 7000.00, 2000, '有効', 0),
('PROD011', 'DEMO01', 'シーバスリーガル18年', '飲食', 'ボトル', 'ウイスキー', 35000.00, 12000.00, 3500, '有効', 0),

-- お客様ドリンク
('PROD012', 'DEMO01', '生ビール', '飲食', 'ドリンク', 'お客様ドリンク', 1000.00, 200.00, 100, '有効', 0),
('PROD013', 'DEMO01', 'ハイボール', '飲食', 'ドリンク', 'お客様ドリンク', 1000.00, 200.00, 100, '有効', 0),
('PROD014', 'DEMO01', 'カクテル', '飲食', 'ドリンク', 'お客様ドリンク', 1200.00, 250.00, 120, '有効', 0),
('PROD015', 'DEMO01', 'ウーロン茶', '飲食', 'ドリンク', 'ソフトドリンク', 500.00, 100.00, 50, '有効', 0),

-- フード
('PROD016', 'DEMO01', 'フルーツ盛り合わせ', '飲食', 'フード', 'フード', 3000.00, 800.00, 300, '有効', 0),
('PROD017', 'DEMO01', 'おつまみ盛り合わせ', '飲食', 'フード', 'フード', 2000.00, 500.00, 200, '有効', 0),
('PROD018', 'DEMO01', '特選オードブル', '飲食', 'フード', 'フード', 5000.00, 1500.00, 500, '有効', 0);

-- ============================================
-- アクション
-- ============================================
INSERT INTO actions (action_id, tenant_id, action_name, category, target, point, status, approval_required, description) VALUES
('ACT001', 'DEMO01', '指名', '接客', '個人', 300, '有効', 0, '本指名を獲得'),
('ACT002', 'DEMO01', '場内指名', '接客', '個人', 150, '有効', 0, '場内指名を獲得'),
('ACT003', 'DEMO01', '延長30分', '接客', '個人', 300, '有効', 0, '30分延長を獲得'),
('ACT004', 'DEMO01', '延長60分', '接客', '個人', 500, '有効', 0, '60分延長を獲得'),
('ACT007', 'DEMO01', '同伴', '営業', '個人', 500, '有効', 1, '同伴出勤'),
('ACT008', 'DEMO01', 'アフター', '営業', '個人', 300, '有効', 1, 'アフター実施'),
('ACT009', 'DEMO01', '新規客獲得', '営業', '個人', 1000, '有効', 1, '新規のお客様獲得'),
('ACT012', 'DEMO01', 'SNS投稿', 'マーケティング', '個人', 100, '有効', 0, 'SNSで営業投稿'),
('ACT013', 'DEMO01', 'LINE営業', 'マーケティング', '個人', 50, '有効', 0, 'お客様へのLINE営業');

-- ============================================
-- タスク
-- ============================================
INSERT INTO tasks (task_id, tenant_id, task_name, type, repeat_type, point, status, approval_required, description) VALUES
('TASK001', 'DEMO01', '開店準備完了', '個人', '毎日', 50, '有効', 0, '開店前の準備作業'),
('TASK002', 'DEMO01', '閉店作業完了', '個人', '毎日', 50, '有効', 0, '閉店後の作業'),
('TASK003', 'DEMO01', '店内清掃完了', '個人', '毎日', 30, '有効', 0, 'フロアの清掃'),
('TASK004', 'DEMO01', '化粧室清掃完了', '個人', '毎日', 30, '有効', 0, '化粧室の清掃'),
('TASK005', 'DEMO01', 'ドリンク在庫補充', '個人', '毎週', 40, '有効', 0, 'ドリンクの在庫補充'),
('TASK006', 'DEMO01', '売上日報作成', 'チーム', '毎日', 50, '有効', 1, '日次売上報告書作成'),
('TASK007', 'DEMO01', '予約管理更新', '個人', '毎日', 40, '有効', 0, '予約システムの更新'),
('TASK008', 'DEMO01', 'お礼LINE送信', '個人', '毎日', 20, '有効', 0, '来店客へのお礼LINE');

-- ============================================
-- イベント
-- ============================================
-- EVT001: クリスマスイベント（商品カテゴリ別倍率 + アクション別倍率 + アクションカテゴリ別倍率を使用）
INSERT INTO events (event_id, tenant_id, event_name, description, repeat_type, start_date, end_date, day_of_month, target_type, target_ids, multiplier, approval_required, status) VALUES
('EVT001', 'DEMO01', 'クリスマスイベント', 'クリスマス期間は全売上2倍ポイント！シャンパン・ワインはカテゴリ別倍率、営業アクションも強化！', '単発', '2024-12-20 00:00:00', '2024-12-26 23:59:59', NULL, '全商品', NULL, 2.0, 0, '有効');

-- EVT002: ドンペリ3倍キャンペーン（個別商品倍率を使用）
INSERT INTO events (event_id, tenant_id, event_name, description, repeat_type, start_date, end_date, day_of_month, target_type, target_ids, multiplier, approval_required, status) VALUES
('EVT002', 'DEMO01', 'ドンペリ3倍キャンペーン', 'ドンペリ注文で通常の3倍ポイント！ドンペリニヨン3倍、ロゼは3.5倍！', '単発', '2025-10-01 00:00:00', '2025-10-31 23:59:59', NULL, '特定商品', 'PROD003,PROD004', 3.0, 0, '有効');

-- EVT003: バレンタインイベント（全商品一律倍率、関連テーブルなし）
INSERT INTO events (event_id, tenant_id, event_name, description, repeat_type, start_date, end_date, day_of_month, target_type, target_ids, multiplier, approval_required, status) VALUES
('EVT003', 'DEMO01', 'バレンタインイベント', 'バレンタイン期間は全売上1.5倍ポイント', '単発', '2025-02-10 00:00:00', '2025-02-15 23:59:59', NULL, '全商品', NULL, 1.5, 0, '無効');

-- EVT004: 毎月10日はシャンパンデー（個別商品倍率を使用、商品ごとに異なる倍率）
INSERT INTO events (event_id, tenant_id, event_name, description, repeat_type, start_date, end_date, day_of_month, target_type, target_ids, multiplier, approval_required, status) VALUES
('EVT004', 'DEMO01', '毎月10日はシャンパンデー', 'シャンパン2倍〜3倍ポイント！商品によって倍率が異なります。', '毎月', '2024-10-01 00:00:00', '2025-12-31 23:59:59', 10, '特定商品', 'PROD001,PROD002,PROD003,PROD004,PROD005', 2.0, 0, '有効');

-- EVT005: 魔王ジャックダニエル・生誕祭（個別商品倍率を使用、承認必要）
INSERT INTO events (event_id, tenant_id, event_name, description, repeat_type, start_date, end_date, day_of_month, target_type, target_ids, multiplier, approval_required, status) VALUES
('EVT005', 'DEMO01', '魔王ジャックダニエル・生誕祭', '魔王・ジャックダニエルの販売強化月間！対象商品のご注文で通常の2倍ポイントを獲得できます。', '単発', '2025-10-14 00:00:00', '2025-10-30 23:59:59', NULL, '特定商品', 'PROD009,PROD010', 2.0, 1, '有効');

-- EVT006: コスプレ会（個別商品倍率を使用）
INSERT INTO events (event_id, tenant_id, event_name, description, repeat_type, start_date, end_date, day_of_month, target_type, target_ids, multiplier, approval_required, status) VALUES
('EVT006', 'DEMO01', 'コスプレ会', 'コスプレイベント開催日は、オーパスワインの売上が2倍ポイントに！盛り上げていきましょう！', '毎週', '2025-10-07 00:00:00', '2025-10-21 23:59:59', NULL, '特定商品', 'PROD006', 2.0, 0, '有効');

-- EVT007: こんにちは会（個別商品倍率を使用）
INSERT INTO events (event_id, tenant_id, event_name, description, repeat_type, start_date, end_date, day_of_month, target_type, target_ids, multiplier, approval_required, status) VALUES
('EVT007', 'DEMO01', 'こんにちは会', '火・金・土曜日は対象商品が2倍ポイント！お客様に積極的にお勧めしてください。', '毎週', '2025-10-14 00:00:00', '2025-10-28 23:59:59', NULL, '特定商品', 'PROD011,PROD013,PROD015', 2.0, 0, '有効');

-- EVT008: 確認イベント（全商品一律倍率、関連テーブルなし、承認必要）
INSERT INTO events (event_id, tenant_id, event_name, description, repeat_type, start_date, end_date, day_of_month, target_type, target_ids, multiplier, approval_required, status) VALUES
('EVT008', 'DEMO01', '確認イベント', '毎月14日は全商品2倍ポイントデー！売上アップのチャンスです。', '毎月', '2025-10-14 00:00:00', '2025-10-14 23:59:59', 14, '全商品', NULL, 2.0, 1, '有効');

-- ============================================
-- イベント商品別倍率
-- ============================================
-- EVT002: ドンペリ3倍キャンペーン（個別商品倍率）
INSERT INTO event_product_multipliers (event_id, tenant_id, product_id, multiplier) VALUES
('EVT002', 'DEMO01', 'PROD003', 3.00),  -- ドンペリニヨン
('EVT002', 'DEMO01', 'PROD004', 3.50);  -- ドンペリニヨン・ロゼ（特別に3.5倍）

-- EVT004: 毎月10日はシャンパンデー（個別商品倍率）
INSERT INTO event_product_multipliers (event_id, tenant_id, product_id, multiplier) VALUES
('EVT004', 'DEMO01', 'PROD001', 2.00),  -- モエ・エ・シャンドン
('EVT004', 'DEMO01', 'PROD002', 2.00),  -- ヴーヴ・クリコ
('EVT004', 'DEMO01', 'PROD003', 2.50),  -- ドンペリニヨン（特別に2.5倍）
('EVT004', 'DEMO01', 'PROD004', 2.50),  -- ドンペリニヨン・ロゼ（特別に2.5倍）
('EVT004', 'DEMO01', 'PROD005', 3.00);  -- アルマンド・ブリニャック（特別に3.0倍）

-- EVT005: 魔王ジャックダニエル・生誕祭（個別商品倍率）
INSERT INTO event_product_multipliers (event_id, tenant_id, product_id, multiplier) VALUES
('EVT005', 'DEMO01', 'PROD009', 2.00),  -- 魔王
('EVT005', 'DEMO01', 'PROD010', 2.00);  -- ジャックダニエル

-- EVT006: コスプレ会（個別商品倍率）
INSERT INTO event_product_multipliers (event_id, tenant_id, product_id, multiplier) VALUES
('EVT006', 'DEMO01', 'PROD006', 2.00);  -- オーパスワン

-- EVT007: こんにちは会（個別商品倍率）
INSERT INTO event_product_multipliers (event_id, tenant_id, product_id, multiplier) VALUES
('EVT007', 'DEMO01', 'PROD011', 2.00),  -- シーバスリーガル18年
('EVT007', 'DEMO01', 'PROD013', 2.00),  -- ハイボール
('EVT007', 'DEMO01', 'PROD015', 2.00);  -- ウーロン茶

-- ============================================
-- イベント商品カテゴリ別倍率
-- ============================================
-- EVT001: クリスマスイベント（商品カテゴリ別倍率）
INSERT INTO event_product_category_multipliers (event_id, tenant_id, category, multiplier) VALUES
('EVT001', 'DEMO01', '飲食 > シャンパン', 2.50),  -- クリスマス時のシャンパンカテゴリ
('EVT001', 'DEMO01', '飲食 > ワイン', 2.00);      -- クリスマス時のワインカテゴリ

-- ============================================
-- イベントアクション別倍率
-- ============================================
-- EVT001: クリスマスイベント（アクション別倍率）
INSERT INTO event_action_multipliers (event_id, tenant_id, action_id, multiplier) VALUES
('EVT001', 'DEMO01', 'ACT007', 1.50),  -- 同伴強化
('EVT001', 'DEMO01', 'ACT009', 2.00);  -- 新規客獲得強化

-- ============================================
-- イベントアクションカテゴリ別倍率
-- ============================================
-- EVT001: クリスマスイベント（アクションカテゴリ別倍率）
INSERT INTO event_action_category_multipliers (event_id, tenant_id, category, multiplier) VALUES
('EVT001', 'DEMO01', '営業', 1.80),          -- クリスマス時の営業カテゴリ
('EVT001', 'DEMO01', 'マーケティング', 1.50); -- クリスマス時のマーケティングカテゴリ

-- ============================================
-- お知らせ（掲示板）
-- ============================================
INSERT INTO bulletins (bulletin_id, tenant_id, type, related_event_id, pinned, status, author, title, body, start_datetime, end_datetime) VALUES
('BUL001', 'DEMO01', 'イベント', 'EVT002', 1, '公開', 'MEM001', '10月はドンペリ3倍キャンペーン！', 'ドンペリ注文で通常の3倍ポイントがもらえます！この機会にぜひお客様にお勧めしてください！', '2025-10-01 00:00:00', '2025-10-31 23:59:59'),
('BUL002', 'DEMO01', 'イベント', 'EVT004', 1, '公開', 'MEM001', '毎月10日はシャンパンデー', '毎月10日はシャンパン2倍ポイント！頑張りましょう！', '2024-10-01 00:00:00', '2025-12-31 23:59:59'),
('BUL003', 'DEMO01', 'お知らせ', NULL, 0, '公開', 'MEM001', '10月のシフト調整について', '10月は繁忙期のため、シフト調整にご協力ください。', '2025-10-01 00:00:00', '2025-10-31 23:59:59');

SELECT 'サンプルデータ挿入完了' AS message;

-- ============================================
-- 完了メッセージ
-- ============================================

SELECT '========================================' AS message;
SELECT 'テストデータ挿入が完了しました！' AS message;
SELECT '========================================' AS message;
SELECT 'テナント: CLUB高濱 (DEMO01)' AS message;
SELECT 'メンバー: 10名' AS message;
SELECT '商品: 18種類' AS message;
SELECT 'イベント: 8件' AS message;
SELECT '========================================' AS message;
