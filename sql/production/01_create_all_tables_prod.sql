-- ============================================
-- インセンティブSaaS 全テーブル作成（本番環境用）
-- テーブル構造のみを作成（データは含まない）
-- ============================================

USE xs063745_incentive;

-- ============================================
-- STEP 1: 基本テーブル作成
-- ============================================

-- テナントテーブル
CREATE TABLE IF NOT EXISTS tenants (
    tenant_id VARCHAR(20) PRIMARY KEY,
    tenant_name VARCHAR(100) NOT NULL,
    status ENUM('有効', '無効') DEFAULT '有効',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- メンバーテーブル
CREATE TABLE IF NOT EXISTS members (
    member_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    login_id VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    pin VARCHAR(4) NOT NULL,
    team_id VARCHAR(20),
    status ENUM('有効', '無効') DEFAULT '有効',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (member_id, tenant_id),
    UNIQUE KEY unique_login (tenant_id, login_id),
    UNIQUE KEY unique_member_tenant (member_id, tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- セッションテーブル
CREATE TABLE IF NOT EXISTS sessions (
    token VARCHAR(64) PRIMARY KEY,
    tenant_id VARCHAR(20) NOT NULL,
    member_id VARCHAR(20) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    last_accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- チームテーブル
CREATE TABLE IF NOT EXISTS teams (
    team_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    leader_id VARCHAR(20) NULL COMMENT 'チームリーダーのmember_id',
    status ENUM('有効', '無効') DEFAULT '有効',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (team_id, tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 商品テーブル
CREATE TABLE IF NOT EXISTS products (
    product_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    large_category VARCHAR(50),
    medium_category VARCHAR(50),
    small_category VARCHAR(50),
    point INT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0,
    cost DECIMAL(10,2) DEFAULT 0,
    status ENUM('有効', '無効') DEFAULT '有効',
    approval_required BOOLEAN DEFAULT FALSE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id, tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- タスクテーブル
CREATE TABLE IF NOT EXISTS tasks (
    task_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    task_name VARCHAR(100) NOT NULL,
    type ENUM('個人', 'チーム', '日次', '週次', '月次', '一回限り') DEFAULT '個人',
    repeat_type ENUM('毎日', '毎週', '毎月', '一回限り') DEFAULT '一回限り',
    days_of_week VARCHAR(20),
    day_of_month INT,
    start_datetime DATETIME,
    end_datetime DATETIME,
    point INT DEFAULT 0,
    daily_limit INT DEFAULT 1,
    approval_required BOOLEAN DEFAULT FALSE,
    status ENUM('有効', '無効') DEFAULT '有効',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (task_id, tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- アクションテーブル
CREATE TABLE IF NOT EXISTS actions (
    action_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    action_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) DEFAULT NULL COMMENT 'アクションカテゴリ',
    repeat_type ENUM('毎日', '毎週', '毎月', '単発') DEFAULT '単発',
    start_date DATE,
    end_date DATE,
    days_of_week VARCHAR(20),
    day_of_month INT,
    target ENUM('全員', 'チーム', '個人') DEFAULT '全員',
    status ENUM('有効', '無効') DEFAULT '有効',
    description TEXT,
    point INT DEFAULT 0,
    approval_required BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (action_id, tenant_id),
    INDEX idx_actions_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- イベントテーブル
CREATE TABLE IF NOT EXISTS events (
    event_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    event_name VARCHAR(100) NOT NULL,
    description TEXT,
    repeat_type ENUM('毎日', '毎週', '毎月', '単発') DEFAULT '単発',
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    days_of_week VARCHAR(20),
    day_of_month INT,
    target_type ENUM('全商品', '特定商品', 'カテゴリ', '全アクション', '特定アクション') DEFAULT '全商品',
    target_ids TEXT,
    multiplier DECIMAL(3,2) DEFAULT 1.00,
    approval_required BOOLEAN DEFAULT FALSE COMMENT 'イベント承認要否',
    status ENUM('有効', '無効') DEFAULT '有効',
    publish_notice BOOLEAN DEFAULT FALSE,
    notice_title VARCHAR(200),
    notice_body TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 売上記録テーブル
CREATE TABLE IF NOT EXISTS sales_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(20) NOT NULL,
    member_id VARCHAR(20) NOT NULL,
    product_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) DEFAULT 0,
    base_point INT DEFAULT 0,
    event_multiplier DECIMAL(3,2) DEFAULT 1.00,
    final_point INT DEFAULT 0,
    applied_event_id VARCHAR(20),
    applied_event_name VARCHAR(100),
    note TEXT,
    approval_status ENUM('承認待ち', '承認済み', '却下') DEFAULT '承認待ち',
    approver VARCHAR(20),
    approved_at TIMESTAMP NULL,
    reject_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- タスク記録テーブル
CREATE TABLE IF NOT EXISTS task_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(20) NOT NULL,
    member_id VARCHAR(20) NOT NULL,
    task_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    point INT DEFAULT 0,
    approval_status ENUM('承認待ち', '承認済み', '却下') DEFAULT '承認待ち',
    approver VARCHAR(20),
    approved_at TIMESTAMP NULL,
    reject_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- アクション記録テーブル
CREATE TABLE IF NOT EXISTS action_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(20) NOT NULL,
    member_id VARCHAR(20) NOT NULL,
    action_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    point INT DEFAULT 0,
    approval_status ENUM('承認待ち', '承認済み', '却下') DEFAULT '承認待ち',
    approver VARCHAR(20),
    approved_at TIMESTAMP NULL,
    reject_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- お知らせテーブル
CREATE TABLE IF NOT EXISTS bulletins (
    bulletin_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    type ENUM('お知らせ', 'イベント', '重要') DEFAULT 'お知らせ',
    related_event_id VARCHAR(20),
    pinned BOOLEAN DEFAULT FALSE,
    status ENUM('下書き', '公開', '非公開') DEFAULT '下書き',
    author VARCHAR(20) NOT NULL,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    start_datetime DATETIME,
    end_datetime DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (bulletin_id, tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 監査ログテーブル
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(20) NOT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50),
    row_id VARCHAR(20),
    member_id VARCHAR(20),
    operator VARCHAR(20) NOT NULL,
    user_display_name VARCHAR(100) NOT NULL,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT '基本テーブル作成完了' AS message;

-- ============================================
-- STEP 2: テナント設定テーブル
-- ============================================

CREATE TABLE IF NOT EXISTS tenant_settings (
    tenant_id VARCHAR(20) NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tenant_id, setting_key),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_tenant_settings_key ON tenant_settings(setting_key);

SELECT 'tenant_settings テーブルを作成しました' AS message;

-- ============================================
-- STEP 3: イベント商品別倍率テーブル
-- ============================================

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

CREATE INDEX idx_event_product_event ON event_product_multipliers(event_id, tenant_id);
CREATE INDEX idx_event_product_product ON event_product_multipliers(product_id, tenant_id);

SELECT 'event_product_multipliers テーブルを作成しました' AS message;

-- ============================================
-- STEP 4: イベント商品カテゴリ別倍率テーブル
-- ============================================

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

-- ============================================
-- STEP 5: イベントアクション別倍率テーブル
-- ============================================

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

-- ============================================
-- STEP 6: イベントアクションカテゴリ別倍率テーブル
-- ============================================

CREATE TABLE IF NOT EXISTS event_action_category_multipliers (
    event_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    category VARCHAR(50) NOT NULL,
    multiplier DECIMAL(4,2) DEFAULT 1.00 NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, tenant_id, category),
    FOREIGN KEY (event_id, tenant_id) REFERENCES events(event_id, tenant_id) ON DELETE CASCADE,
    INDEX idx_event_action_category_event (event_id, tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'event_action_category_multipliers テーブルを作成しました' AS message;

-- ============================================
-- STEP 7: お知らせテンプレートテーブル
-- ============================================

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

-- ============================================
-- 完了メッセージ
-- ============================================

SELECT '========================================' AS message;
SELECT '全テーブル作成が完了しました！' AS message;
SELECT '========================================' AS message;
