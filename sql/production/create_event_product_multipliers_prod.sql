-- イベント商品別倍率テーブルの作成（本番環境用）
USE xs063745_incentive;

-- イベント商品別倍率テーブル
CREATE TABLE IF NOT EXISTS event_product_multipliers (
    event_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    product_id VARCHAR(20) NOT NULL,
    multiplier DECIMAL(4,2) DEFAULT 1.00 NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, tenant_id, product_id),
    FOREIGN KEY (event_id, tenant_id) REFERENCES events(event_id, tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id, tenant_id) REFERENCES products(product_id, tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- インデックス作成
CREATE INDEX idx_event_product_event ON event_product_multipliers(event_id, tenant_id);
CREATE INDEX idx_event_product_product ON event_product_multipliers(product_id, tenant_id);

SELECT 'event_product_multipliers テーブルを作成しました' AS message;

