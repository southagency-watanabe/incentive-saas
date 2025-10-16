-- ローカル環境: イベントごとの商品カテゴリ別倍率テーブルを作成

USE incentive_local;

CREATE TABLE IF NOT EXISTS event_product_category_multipliers (
    event_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    category VARCHAR(50) NOT NULL,
    multiplier DECIMAL(4,2) DEFAULT 1.00 NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, tenant_id, category),
    FOREIGN KEY (event_id, tenant_id) REFERENCES events(event_id, tenant_id) ON DELETE CASCADE,
    INDEX idx_event_product_category_event (event_id, tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'event_product_category_multipliers テーブルを作成しました' AS message;



