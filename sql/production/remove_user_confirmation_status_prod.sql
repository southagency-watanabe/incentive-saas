-- 承認状態の統合: 「ユーザー確認待ち」を「承認待ち」に統合（本番環境用）
USE xs063745_incentive;

-- 既存データを「承認待ち」に更新
UPDATE sales_records SET approval_status = '承認待ち' WHERE approval_status = 'ユーザー確認待ち';
UPDATE task_records SET approval_status = '承認待ち' WHERE approval_status = 'ユーザー確認待ち';
UPDATE action_records SET approval_status = '承認待ち' WHERE approval_status = 'ユーザー確認待ち';

-- ENUM型から「ユーザー確認待ち」を削除
ALTER TABLE sales_records
MODIFY COLUMN approval_status ENUM('承認待ち', '承認済み', '却下') DEFAULT '承認待ち';

ALTER TABLE task_records
MODIFY COLUMN approval_status ENUM('承認待ち', '承認済み', '却下') DEFAULT '承認待ち';

ALTER TABLE action_records
MODIFY COLUMN approval_status ENUM('承認待ち', '承認済み', '却下') DEFAULT '承認待ち';

SELECT '承認状態の統合が完了しました' AS message;
