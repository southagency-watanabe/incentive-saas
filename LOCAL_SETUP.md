# ローカル環境セットアップガイド

## 前提条件

- PHP 8.x 以上がインストール済み
- MySQL 8.x 以上がインストール済み
- Git がインストール済み

---

## 本番環境からローカル環境へ切り替える手順

### 1. 環境設定ファイル（.env）の切り替え

#### 本番 → ローカル

```bash
# 現在の.envをバックアップ（念のため）
cp .env .env.backup

# ローカル環境用の設定を.envに反映
cat > .env << 'EOF'
# ローカル環境設定
# ローカル開発用の設定ファイルです

# 環境タイプ
ENVIRONMENT=local

# データベース設定
DB_HOST=localhost
DB_NAME=incentive_local
DB_USER=root
DB_PASS=

# セッション設定
SESSION_COOKIE_SECURE=0
SESSION_COOKIE_DOMAIN=

# ベースURL
BASE_URL=http://localhost

# デバッグモード（ローカルでは1に設定可能）
DEBUG_MODE=1
EOF
```

#### ローカル → 本番

```bash
# 本番環境用の設定を.envに反映
cp .env.production .env
```

---

### 2. データベースのセットアップ

#### ⚠️ 重要: 文字コード設定

MySQLでは**必ず utf8mb4** を使用してください。文字化けを防ぐため、以下の点に注意：

```bash
# データベースの作成（文字コードを明示的に指定）
mysql -u root -e "DROP DATABASE IF EXISTS incentive_local;"
mysql -u root -e "CREATE DATABASE incentive_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# テーブル作成時も文字コードを指定
mysql -u root --default-character-set=utf8mb4 incentive_local < sql/local/create_tables.sql

# テストデータ挿入時も文字コードを指定
mysql -u root --default-character-set=utf8mb4 incentive_local < sql/local/insert_club_realistic_data.sql
```

#### ステップ詳細

```bash
# 1. データベース作成
mysql -u root -e "DROP DATABASE IF EXISTS incentive_local; CREATE DATABASE incentive_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. テーブル作成
mysql -u root --default-character-set=utf8mb4 incentive_local < sql/local/create_tables.sql

# 3. テストデータ挿入（リアルなデータ版）
mysql -u root --default-character-set=utf8mb4 incentive_local < sql/local/insert_club_realistic_data.sql

# 4. takahamaユーザーの追加（必要な場合）
mysql -u root --default-character-set=utf8mb4 incentive_local -e "
INSERT INTO members (member_id, tenant_id, login_id, name, role, pin, team_id, status)
VALUES ('MEM011', 'DEMO01', 'takahama', '高濱 太郎', 'admin', '1111', 'TEAM003', '有効')
ON DUPLICATE KEY UPDATE name=name;
"
```

---

### 3. PHPサーバーの起動

```bash
# プロジェクトルートディレクトリで実行
php -S localhost:8000
```

---

### 4. ログイン情報

#### URL
```
http://localhost:8000/login.php
```

#### 管理者アカウント（デフォルト）

**アカウント1: takahama**
- テナントID: `DEMO01`
- ログインID: `takahama`
- PIN: `1111`

**アカウント2: manager（店長）**
- テナントID: `DEMO01`
- ログインID: `manager`
- PIN: `1111`

---

## 注意点

### ⚠️ 必須注意事項

#### 1. **文字コードの統一**
- データベース、テーブル、接続すべてで **utf8mb4** を使用
- MySQL接続時は必ず `--default-character-set=utf8mb4` を指定
- SQLファイルは **UTF-8（BOM無し）** で保存

#### 2. **.envファイルの管理**
- `.env` ファイルは環境に応じて切り替える
- 本番環境の設定は `.env.production` に保存
- **誤って本番設定でローカルDBに接続しないこと**
- **誤ってローカル設定で本番DBに接続しないこと**

#### 3. **Gitでの管理**
- `.env` はGitで管理しない（.gitignore済み）
- `.env.production` はGitで管理する（本番設定のテンプレート）
- ローカルの変更を本番にプッシュする前に必ず `.env` を確認

#### 4. **データベースの文字化け対策**
- 既存のデータベースに文字化けがある場合は、**一度削除して再作成**
- 二重エンコーディングが発生している場合は修復不可能
- 再作成時は必ず文字コード指定を含める

#### 5. **PHPサーバーの起動確認**
```bash
# ポート8000が既に使用されていないか確認
lsof -i :8000

# 既に起動中の場合はプロセスを終了
kill <PID>
```

---

## トラブルシューティング

### 問題1: ログインできない

**原因候補:**
- データベースに文字化けデータがある
- `.env` の設定が正しくない
- データベースが起動していない

**解決策:**
```bash
# 文字コードを確認
mysql -u root incentive_local -e "SELECT login_id, status, HEX(status) FROM members WHERE login_id='takahama';"

# 正しいHEX値: E69C89E58AA9 (有効)
# 文字化けしている場合: データベースを再作成
```

### 問題2: 文字化けが発生する

**原因:**
- MySQLの接続文字コードが正しくない
- データベースの文字コード設定が間違っている

**解決策:**
```bash
# 文字コード設定を確認
mysql -u root incentive_local -e "SHOW VARIABLES LIKE 'character_set%';"

# すべて utf8mb4 であることを確認
# 違っていたら my.cnf を修正して MySQL を再起動
```

### 問題3: データベース接続エラー

**原因:**
- MySQLが起動していない
- `.env` の設定が間違っている

**解決策:**
```bash
# MySQLの起動確認
mysql -u root -e "SELECT 1;"

# .envファイルを確認
cat .env

# データベース接続テスト
php -r "
require_once 'config/database.php';
\$pdo = getDB();
echo 'Database connection successful!';
"
```

---

## 環境切り替えチェックリスト

### ローカル環境に切り替える前に

- [ ] 現在の.envをバックアップ
- [ ] MySQLが起動していることを確認
- [ ] ローカル用データベース（incentive_local）が存在することを確認

### 本番環境に戻す前に

- [ ] ローカルでの変更をすべてコミット
- [ ] .env.production を .env にコピー
- [ ] 本番環境でテストデプロイ
- [ ] 本番データベースへの接続を確認

---

## よく使うコマンド集

```bash
# データベースのテーブル一覧を表示
mysql -u root incentive_local -e "SHOW TABLES;"

# 特定のテーブルのデータ件数を確認
mysql -u root incentive_local -e "SELECT COUNT(*) FROM members;"

# 管理者ユーザー一覧を表示
mysql -u root incentive_local -e "SELECT login_id, name, role FROM members WHERE role='admin';"

# PHPサーバーのログを確認
tail -f /tmp/php_server.log

# MySQLのエラーログを確認（Macの場合）
tail -f /opt/homebrew/var/mysql/$(hostname).err
```

---

## 開発フロー（推奨）

1. **ローカル環境で開発**
   - `.env` をローカル設定にする
   - `php -S localhost:8000` でサーバー起動
   - ブラウザで動作確認

2. **変更をコミット**
   - Git で変更を管理
   - **`.env` は含めない**（.gitignoreに含まれている）

3. **本番環境にデプロイ**
   - `.env.production` を `.env` にコピー
   - サーバーにプッシュ
   - 本番環境で動作確認

---

## 参考情報

### プロジェクト構成

```
incentive.southagency.email/
├── .env                    # 環境設定（Gitで管理しない）
├── .env.production         # 本番環境設定テンプレート
├── config/
│   └── database.php       # データベース接続設定
├── sql/
│   ├── local/             # ローカル用SQLファイル
│   │   ├── create_tables.sql
│   │   ├── insert_club_realistic_data.sql
│   │   └── insert_club_data.sql
│   └── production/        # 本番用SQLファイル
├── api/                   # APIエンドポイント
├── admin/                 # 管理者画面
├── user/                  # ユーザー画面
└── includes/              # 共通処理
```

### データベーススキーマ

- **tenants**: テナント情報
- **members**: メンバー情報
- **teams**: チーム情報
- **products**: 商品マスタ
- **actions**: アクションマスタ
- **tasks**: タスクマスタ
- **events**: イベント情報
- **sales_records**: 売上実績
- **action_records**: アクション実績
- **task_records**: タスク実績
- **bulletins**: 掲示板
- **sessions**: セッション管理
- **audit_logs**: 監査ログ

---

## サポート

問題が解決しない場合は、以下の情報を確認してください：

1. PHPバージョン: `php -v`
2. MySQLバージョン: `mysql --version`
3. 現在の.env設定: `cat .env`
4. データベースの文字コード: `mysql -u root incentive_local -e "SHOW VARIABLES LIKE 'character_set%';"`
