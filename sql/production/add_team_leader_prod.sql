-- チームリーダー機能追加（本番環境用）
USE incentive_production;

-- teamsテーブルにleader_idカラムを追加（外部キー制約なし、アプリケーションレベルで制御）
ALTER TABLE teams
ADD COLUMN leader_id VARCHAR(20) NULL COMMENT 'チームリーダーのmember_id' AFTER team_name;

-- 注意: 既存のチームデータにリーダーを設定する場合は、
-- 本番環境のデータに合わせて個別にUPDATE文を実行してください

SELECT 'チームリーダー機能の追加が完了しました' AS message;
