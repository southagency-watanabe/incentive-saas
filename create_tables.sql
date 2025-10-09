-- インセンティブSaaS データベーステーブル作成スクリプト
USE incentive_local;

-- テナントテーブル
CREATE TABLE IF NOT EXISTS tenants (
    tenant_id VARCHAR(20) PRIMARY KEY,
    tenant_name VARCHAR(100) NOT NULL,
    status ENUM('有効', '無効') DEFAULT '有効',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (member_id, tenant_id),
    UNIQUE KEY unique_login (tenant_id, login_id),
    UNIQUE KEY unique_member_tenant (member_id, tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id)
);

-- セッションテーブル
CREATE TABLE IF NOT EXISTS sessions (
    token VARCHAR(64) PRIMARY KEY,
    tenant_id VARCHAR(20) NOT NULL,
    member_id VARCHAR(20) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    last_accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- チームテーブル
CREATE TABLE IF NOT EXISTS teams (
    team_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    status ENUM('有効', '無効') DEFAULT '有効',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (team_id, tenant_id)
);

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
);

-- タスクテーブル
CREATE TABLE IF NOT EXISTS tasks (
    task_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    task_name VARCHAR(100) NOT NULL,
    type ENUM('日次', '週次', '月次', '一回限り') DEFAULT '一回限り',
    repeat_type ENUM('毎日', '毎週', '毎月', '一回限り') DEFAULT '一回限り',
    days_of_week VARCHAR(20),
    day_of_month INT,
    point INT DEFAULT 0,
    daily_limit INT DEFAULT 1,
    approval_required BOOLEAN DEFAULT FALSE,
    status ENUM('有効', '無効') DEFAULT '有効',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (task_id, tenant_id)
);

-- アクションテーブル
CREATE TABLE IF NOT EXISTS actions (
    action_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    action_name VARCHAR(100) NOT NULL,
    target ENUM('全員', 'チーム', '個人') DEFAULT '全員',
    status ENUM('有効', '無効') DEFAULT '有効',
    description TEXT,
    point INT DEFAULT 0,
    approval_required BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (action_id, tenant_id)
);

-- イベントテーブル
CREATE TABLE IF NOT EXISTS events (
    event_id VARCHAR(20) NOT NULL,
    tenant_id VARCHAR(20) NOT NULL,
    event_name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    multiplier DECIMAL(3,2) DEFAULT 1.00,
    status ENUM('有効', '無効') DEFAULT '有効',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, tenant_id)
);

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
    note TEXT,
    approval_status ENUM('ユーザー確認待ち', '承認待ち', '承認済み', '却下') DEFAULT 'ユーザー確認待ち',
    approver VARCHAR(20),
    approved_at TIMESTAMP NULL,
    reject_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- タスク記録テーブル
CREATE TABLE IF NOT EXISTS task_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(20) NOT NULL,
    member_id VARCHAR(20) NOT NULL,
    task_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    point INT DEFAULT 0,
    approval_status ENUM('ユーザー確認待ち', '承認待ち', '承認済み', '却下') DEFAULT 'ユーザー確認待ち',
    approver VARCHAR(20),
    approved_at TIMESTAMP NULL,
    reject_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- アクション記録テーブル
CREATE TABLE IF NOT EXISTS action_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(20) NOT NULL,
    member_id VARCHAR(20) NOT NULL,
    action_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    point INT DEFAULT 0,
    approval_status ENUM('ユーザー確認待ち', '承認待ち', '承認済み', '却下') DEFAULT 'ユーザー確認待ち',
    approver VARCHAR(20),
    approved_at TIMESTAMP NULL,
    reject_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- お知らせテーブル
CREATE TABLE IF NOT EXISTS bulletins (
    bulletin_id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(20) NOT NULL,
    event_id VARCHAR(20) NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
);
