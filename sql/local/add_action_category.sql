USE incentive_local;

-- actionsテーブルにcategoryカラムを追加
ALTER TABLE actions 
ADD COLUMN category VARCHAR(50) NULL COMMENT 'アクションカテゴリ' 
AFTER action_name;

-- 既存アクションにカテゴリを設定
UPDATE actions SET category = '指名・接客' 
WHERE action_id IN ('ACT001', 'ACT002', 'ACT003', 'ACT004');

UPDATE actions SET category = '営業活動' 
WHERE action_id IN ('ACT007', 'ACT008', 'ACT009');

UPDATE actions SET category = '販促・SNS' 
WHERE action_id IN ('ACT012', 'ACT013');

SELECT 'actionsテーブルにcategoryカラムを追加し、既存データを更新しました' AS message;


