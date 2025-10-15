-- eventsテーブルにapproval_requiredカラムを追加（本番環境用）
USE xs063745_incentive;

-- approval_requiredカラムを追加
ALTER TABLE events 
ADD COLUMN approval_required TINYINT(1) DEFAULT 0 COMMENT '承認要否（0:不要, 1:必要）' 
AFTER multiplier;

SELECT 'eventsテーブルにapproval_requiredカラムを追加しました' AS message;

