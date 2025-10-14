-- チームリーダー機能追加
USE incentive_local;

-- teamsテーブルにleader_idカラムを追加（外部キー制約なし、アプリケーションレベルで制御）
ALTER TABLE teams
ADD COLUMN leader_id VARCHAR(20) NULL COMMENT 'チームリーダーのmember_id' AFTER team_name;

-- 既存のチームにリーダーを設定（例として美咲チーム→美咲、由紀子チーム→由紀子）
UPDATE teams SET leader_id = 'MEM002' WHERE team_id = 'TEAM001'; -- 美咲チーム
UPDATE teams SET leader_id = 'MEM003' WHERE team_id = 'TEAM002'; -- 由紀子チーム
UPDATE teams SET leader_id = 'MEM001' WHERE team_id = 'TEAM003'; -- 店長

SELECT 'チームリーダー機能の追加が完了しました' AS message;
