-- 本番環境用: event_action_multipliers テーブルを作成

CREATE TABLE IF NOT EXISTS event_action_multipliers (
    event_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    action_id VARCHAR(20) NOT NULL,
    multiplier DECIMAL(4,2) DEFAULT 1.00 NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, tenant_id, action_id),
    FOREIGN KEY (event_id, tenant_id) REFERENCES events(event_id, tenant_id) ON DELETE CASCADE,
    FOREIGN KEY (action_id, tenant_id) REFERENCES actions(action_id, tenant_id) ON DELETE CASCADE,
    INDEX idx_event_action_event (event_id, tenant_id),
    INDEX idx_event_action_action (action_id, tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'event_action_multipliers テーブルを作成しました' AS message;


