USE xs063745_incentive;

-- テナント設定テーブル
CREATE TABLE IF NOT EXISTS tenant_settings (
    tenant_id VARCHAR(20) NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tenant_id, setting_key),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- インデックス
CREATE INDEX idx_tenant_settings_key ON tenant_settings(setting_key);

SELECT 'tenant_settings テーブルを作成しました' AS message;
