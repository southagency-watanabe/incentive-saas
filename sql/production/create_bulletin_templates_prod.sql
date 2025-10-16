-- 本番環境: お知らせテンプレートテーブルを作成

USE incentive_production;

CREATE TABLE IF NOT EXISTS bulletin_templates (
    template_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    template_name VARCHAR(100) NOT NULL COMMENT 'テンプレート名',
    title VARCHAR(200) NOT NULL COMMENT 'お知らせタイトル',
    body TEXT NOT NULL COMMENT 'お知らせ本文',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (template_id, tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'bulletin_templates テーブルを作成しました' AS message;



