1. プロジェクト概要
1.1 プロジェクト名
インセンティブSaaS（飲食店向けスタッフモチベーション管理システム）
1.2 目的
飲食店スタッフのモチベーション向上を目的としたポイントベースのインセンティブ管理システム。売上、アクション、タスクに対してポイントを付与し、イベント倍率やランキングでゲーミフィケーション要素を提供する。
1.3 対象業種
主対象：飲食店（たれそば、しゃぶしゃぶ、カレー等）
汎用設計：他業種への横展開可能
マルチテナント：複数店舗・企業を1システムで管理
1.4 主要ユーザー
管理者（admin）：マネージャー（主にPC利用）
スタッフ（user）：一般スタッフ（主にスマホ利用）
2. 技術スタック
2.1 バックエンド
言語：PHP 8.x（Pure PHP、フレームワーク不使用）
データベース：MySQL 8.x
DB管理ツール：PHPMyAdmin
2.2 フロントエンド
HTML5
CSS：Tailwind CSS 3.x（CDN版）
JavaScript：Vanilla JS（jQuery不使用）
2.3 ストレージ
証憑画像：サーバーローカル（/uploads/{tenant_id}/evidence/）
代替案：AWS S3、Cloudflare R2等（将来対応）
2.4 サーバー環境
Webサーバー：Apache 2.4+ / Nginx 1.x+
PHP実行環境：mod_php / PHP-FPM
SSL/TLS：必須（Let's Encrypt推奨）
5 件の返信
髙濵涼太郎  [18:55]
3. ユーザーストーリー
3.1 スタッフ（user権限）の1日
09:00 スタッフ出勤
      → ホーム画面で「未達成タスク」をステータスバーで確認（進捗率表示）
      → イベント一覧（開催中タブ）で「肉の日キャンペーン」を確認
      → タスク報告（清掃完了）：写真を添付して報告

12:00 ランチ営業中
      → アクション報告（Google口コミ獲得）：証憑スクリーンショットを添付

15:00 休憩時間
      → 売上承認画面で管理者が入力した自分の売上を確認
      → 内容を確認して「承認」ボタンをタップ

18:00 ディナー営業開始前
      → イベント一覧（開催前タブ）で「週末売上2倍キャンペーン」を確認
      → 掲示板で詳細を確認

22:00 営業終了
      → ランキング画面で今日の自分の順位を確認
      → チームランキングも確認してモチベーションアップ
3.2 管理者（admin権限）の1日
15:00 ランチ売上入力
      → 売上管理画面で商品選択、担当スタッフ選択、数量入力
      → 単価は自動入力されるが変更も可能（備考に理由記載）
      → 合計金額を確認して保存

16:00 承認管理
      → タスク/アクション/売上の承認待ち一覧を確認
      → 必要に応じて承認・却下（理由必須）
      → 強制承認・強制却下も可能

18:00 イベント設定
      → 「肉の日」イベントを登録（毎月29日、肉類商品2倍）
      → 「告知を掲示板に投稿」にチェックして自動投稿

20:00 実績管理
      → 商品別売上、個人別ポイント、チーム別ポイントを集計
      → 今月の実績をグラフで確認

22:00 ディナー売上入力
      → ランチと同様に売上計上日を指定して入力

23:00 マスタメンテナンス
      → 新商品を商品マスタに登録（大分類：飲食、中分類：麺類、小分類：たれそば）
      → 新スタッフをメンバーマスタに登録
4. 機能要件
4.1 マルチテナント設計
4.1.1 テナント（店舗・企業）管理
テナントID：各店舗・企業に一意のID（TN001形式）
テナント分離：
全データにテナントIDを付与
ログイン時にテナントIDを識別
クエリには必ずテナントID条件を付与
サブドメイン方式：
例：tenant1.incentive-saas.com
または：incentive-saas.com/?tenant=tenant1
4.1.2 テナントマスタ
項目：
テナントID（TN001形式）
テナント名（店舗名・企業名）
サブドメイン
ステータス（有効/無効）
契約開始日
契約終了日
作成日時
更新日時
4.2 認証・セッション管理
4.2.1 ログイン
ログイン方式：テナントID + ログインID + 4桁PIN
PIN保存形式：
初期フェーズ：平文（開発初期）
本番フェーズ：password_hash()でハッシュ化（BCRYPT）
セッション管理：
PHPセッション（session_start()）
有効期限：24時間
スライディング延長：アクセスのたびにlast_accessed_atを更新
セッションデータ：データベースに保存（sessionsテーブル）
セッションにテナントID保存
4.2.2 権限管理
admin（管理者）：全機能アクセス可能
user（スタッフ）：ユーザー向け機能のみアクセス可能
アクセス制御：
各ページで$_SESSION['role']をチェック
各ページで$_SESSION['tenant_id']をチェック
権限不足の場合は403エラーまたはリダイレクト
4.3 管理者（admin）機能
4.3.1 マスタ管理
メンバーマスタ
URL：/admin/masters/members.php
項目：
メンバーID（自動採番：M001形式）
氏名（必須）
チームID（プルダウン選択、無効チームは除外）
在籍ステータス（有効/無効、必須）
ログインID（必須、テナント内一意制約、リアルタイム重複チェックAPI）
PIN（必須、4桁、文字列型で先頭ゼロ保護）
権限（user/admin、必須）
説明
テナントID（自動付与、非表示）
作成日時（自動）
更新日時（自動）
操作：
新規登録（モーダル表示）
編集（モーダル表示）
削除（物理削除、確認ダイアログ）
複製（IDとログインIDのみ新規生成）
更新ボタン（Ajax再読み込み）
API：
GET /api/members.php - 一覧取得（自テナントのみ）
POST /api/members.php - 新規登録
PUT /api/members.php?id={id} - 更新
DELETE /api/members.php?id={id} - 削除
POST /api/members/duplicate.php - 複製
GET /api/members/check-login-id.php?login_id={id}&exclude={id} - 重複チェック
制約：
ログインIDはテナント内一意（入力時リアルタイム検証 + 保存時サーバー検証）
無効チームは選択不可（候補生成時に除外）
必須項目未入力は保存不可（フロント＋バック二重チェック）
二重送信防止（トークン方式）
チームマスタ
URL：/admin/masters/teams.php
項目：
チームID（自動採番：T001形式）
チーム名（必須）
ステータス（有効/無効、必須）
説明
テナントID（自動付与、非表示）
作成日時（自動）
更新日時（自動）
操作：新規/編集/削除/複製/更新
API：
GET /api/teams.php
POST /api/teams.php
PUT /api/teams.php?id={id}
DELETE /api/teams.php?id={id}
POST /api/teams/duplicate.php
商品マスタ
URL：/admin/masters/products.php
項目：
商品ID（自動採番：PRD001形式）
商品名（必須）
大分類（例：飲食）
中分類（例：麺類）
小分類（例：たれそば）
付与pt（必須、数値）
売価（必須、数値）
原価（数値）
ステータス（有効/無効、必須）
承認要否（必要/不要、必須、デフォルト：必要）
説明
テナントID（自動付与、非表示）
作成日時（自動）
更新日時（自動）
操作：新規/編集/削除/複製/更新
API：
GET /api/products.php
POST /api/products.php
PUT /api/products.php?id={id}
DELETE /api/products.php?id={id}
POST /api/products/duplicate.php
アクションマスタ
URL：/admin/masters/actions.php
項目：
アクションID（自動採番：ACT001形式）
アクション名（必須、例：Google口コミ獲得）
対象（個人/チーム、必須、「両方」は存在しない）
ステータス（有効/無効、必須）
説明
付与pt（必須、数値）
承認要否（必要/不要、必須、デフォルト：必要）
テナントID（自動付与、非表示）
作成日時（自動）
更新日時（自動）
操作：新規/編集/削除/複製/更新
API：
GET /api/actions.php
POST /api/actions.php
PUT /api/actions.php?id={id}
DELETE /api/actions.php?id={id}
POST /api/actions/duplicate.php
タスクマスタ
URL：/admin/masters/tasks.php
項目：
タスクID（自動採番：TSK001形式）
タスク名（必須）
種別（個人/チーム、必須、「両方」は存在しない）
繰り返し（毎日/毎週/毎月、単一選択、必須）
曜日CSV（繰り返し=毎週の場合のみ、チェックボックス横2列並び）
毎月日（繰り返し=毎月の場合のみ、例：15 または 末）
付与pt（必須、数値）
1日上限（数値、0は無制限）
承認要否（必要/不要、必須、デフォルト：必要）
ステータス（有効/無効、必須）
説明
テナントID（自動付与、非表示）
作成日時（自動）
更新日時（自動）
操作：新規/編集/削除/複製/更新
API：
GET /api/tasks.php
POST /api/tasks.php
PUT /api/tasks.php?id={id}
DELETE /api/tasks.php?id={id}
POST /api/tasks/duplicate.php
イベントマスタ（通常倍率イベント）
URL：/admin/masters/events.php
項目：
イベントID（自動採番：EVT001形式）
イベント名（必須、例：肉の日キャンペーン）
繰り返し（単発/毎週/毎月、プルダウン、必須）
開始日（日付ピッカー、必須）
終了日（日付ピッカー、必須）
曜日CSV（繰り返し=毎週の場合のみ、チェックボックス横2列並び）
毎月日（繰り返し=毎月の場合のみ、例：29、複数同時登録不可）
対象タイプ（全商品/特定商品/全アクション/特定アクション、プルダウン、必須）
対象IDsCSV（対象タイプ=特定の場合、チェックボックスで選択、IDで保存、例：肉類商品のみ）
倍率（必須、数値、例：2.0）
ステータス（有効/無効、必須）
説明（例：毎月29日は肉類商品ポイント2倍）
告知公開（告知を掲示板に投稿、チェックボックス）
告知タイトル（告知公開ONの場合）
告知本文（告知公開ONの場合）
テナントID（自動付与、非表示）
作成日時（自動）
更新日時（自動）
操作：新規/編集/削除/複製/更新
API：
GET /api/events.php
POST /api/events.php
PUT /api/events.php?id={id}
DELETE /api/events.php?id={id}
POST /api/events/duplicate.php
4.3.2 売上管理
売上入力
URL：/admin/sales/input.php
入力項目：
売上計上日（日付ピッカー、必須、未来日付は入力不可）
メンバー名（プルダウン選択、必須、有効メンバーのみ）
商品名（プルダウン選択、必須、有効商品のみ）
数量（数値入力、必須）
単価（自動入力、変更可、変更時は警告表示＆備考に理由自動追記）
備考（テキストエリア）
自動計算：
基準付与pt = 商品マスタの付与pt × 数量
商品イベント倍率 = イベントマスタから該当イベントの最大倍率
最終付与pt = 基準付与pt × 商品イベント倍率
承認状態：
管理者入力時は常に「ユーザー確認待ち」
表示：
入力フォームの下に売上一覧を表示
「承認待ちのみ」フィルタボタンで一発絞り込み
合計金額を表示
API：
POST /api/sales.php - 売上登録
GET /api/sales.php?filter=pending - 承認待ち一覧
GET /api/sales.php - 全件一覧
4.3.3 承認管理
URL：/admin/approvals.php
画面構成：
タブで切り替え（売上/アクション/タスク）
1画面に統合
売上承認タブ
一覧表示：
承認待ち（ユーザー確認待ち）の売上一覧
表示項目：日付、メンバー名、商品名、数量、単価、金額、承認状態
操作：
強制承認：管理者が直接承認
強制却下：管理者が直接却下（却下理由必須）
API：
GET /api/sales.php?status=pending - 承認待ち一覧
PUT /api/sales/approve.php?id={id} - 強制承認
PUT /api/sales/reject.php?id={id} - 強制却下
アクション承認タブ
一覧表示：
承認待ちのアクション実績一覧
表示項目：日付、メンバー名、アクション名、回数、付与pt、証憑、承認状態
操作：
承認
却下（却下理由必須）
証憑画像の確認（モーダルで拡大表示）
API：
GET /api/action-records.php?status=pending
PUT /api/action-records/approve.php?id={id}
PUT /api/action-records/reject.php?id={id}
タスク承認タブ
一覧表示：
承認待ちのタスク実績一覧
表示項目：日付、メンバー名、タスク名、回数、付与pt、証憑、承認状態
操作：
承認
却下（却下理由必須）
証憑画像の確認（モーダルで拡大表示）
API：
GET /api/task-records.php?status=pending
PUT /api/task-records/approve.php?id={id}
PUT /api/task-records/reject.php?id={id}
4.3.4 実績管理
URL：/admin/reports.php
画面構成：
タブで切り替え（商品別/個人別/チーム別/イベント別）
集計期間を指定可能（デフォルト：今月）
日付ピッカーで開始日・終了日を選択
商品別集計タブ
表示項目：
商品名
売上金額合計
件数合計
ポイント合計
API：
GET /api/reports/by-product.php?from={date}&to={date}
個人別集計タブ
表示項目：
メンバー名
売上金額合計
件数合計
ポイント合計
API：
GET /api/reports/by-member.php?from={date}&to={date}
チーム別集計タブ
表示項目：
チーム名
売上金額合計
件数合計
ポイント合計
API：
GET /api/reports/by-team.php?from={date}&to={date}
イベント別集計タブ
表示項目：
イベント名
売上金額合計
件数合計
ポイント合計
API：
GET /api/reports/by-event.php?from={date}&to={date}
4.3.5 掲示板管理
掲示板投稿
URL：/admin/bulletins.php
投稿種類：
イベント告知：イベントマスタと連携（自動投稿）
フリー投稿：管理者が自由に投稿
入力項目：
種類（イベント告知/フリー投稿、プルダウン）
タイトル（必須）
本文（必須、テキストエリア）
開始日時（イベント告知の場合のみ、日時ピッカー）
終了日時（イベント告知の場合のみ、日時ピッカー）
関連イベントID（イベント告知の場合、プルダウン選択）
ピン留め（チェックボックス）
ステータス（公開/下書き、必須）
表示：
ピン留めは最上部にハイライト表示（背景色変更）
投稿一覧（公開/下書き切り替えタブ）
イベント告知との連携：
イベントマスタで「告知を掲示板に投稿」にチェック
自動で掲示板に投稿される（種類=イベント告知）
掲示板にイベントごとのランキング表を埋め込み表示
API：
GET /api/bulletins.php?status={public|draft} - 一覧取得
POST /api/bulletins.php - 新規投稿
PUT /api/bulletins.php?id={id} - 更新
DELETE /api/bulletins.php?id={id} - 削除
4.3.6 ランキング
URL：/admin/ranking.php
画面構成：
右上タブで切り替え（個人/チーム）
フィルタUI：日/週/月 × 承認済のみ/全件
表示項目：
順位
メンバー名 or チーム名
ポイント合計
売上金額合計
機能：
期間範囲表示（YYYY/MM/DD 〜 YYYY/MM/DD）
ポイント降順
週起点は設定の「週の開始曜日」に従う
API：
GET /api/ranking.php?type={individual|team}&period={day|week|month}&filter={approved|all}&date={YYYY-MM-DD}
4.4 ユーザー（user）機能
4.4.1 ホーム
URL：/user/home.php
表示内容：
お知らせ（掲示板の最新公開投稿3件）
イベント一覧（タブ切り替え：開催中/開催前）
開催中：現在進行中のイベント一覧
開催前：今後開催予定のイベント一覧
表示項目：イベント名、期間、倍率、説明
未達成タスク（ステータスバー表示）
各タスクの進捗率を視覚的に表示（例：3/5回完了 → 60%）
1日上限に達していないタスクのみ表示
各タスクカードに「報告」ボタン
操作：
「報告」ボタンをタップ → 報告モーダル表示
API：
GET /api/home.php - ホーム情報取得（お知らせ+イベント+未達成タスク）
GET /api/events.php?status=ongoing - 開催中イベント
GET /api/events.php?status=upcoming - 開催前イベント
4.4.2 報告
URL：/user/report.php
画面構成：
アクション/タスクをカード表示
アクションは常に表示
タスクは未達成を優先表示（ステータスバー付き）
アクション報告
表示：有効なアクション一覧をカード表示（例：Google口コミ獲得）
操作：
カードをクリック → 報告モーダル表示
回数は固定1（入力不要）
備考（任意）
証憑添付（任意、ファイル選択 → 即アップロード → プレビュー表示）
保存ボタンをクリック → 実績保存（証憑のファイルパスとURLも保存）
API：
GET /api/actions.php?status=active - 有効なアクション一覧
POST /api/action-records.php - アクション報告
POST /api/upload.php - 証憑アップロード
タスク報告
表示：有効なタスク一覧をカード表示（ステータスバー付き）
操作：
カードをクリック → 報告モーダル表示
回数は固定1（入力不要）
備考（任意）
証憑添付（任意、ファイル選択 → 即アップロード → プレビュー表示）
保存ボタンをクリック → 実績保存（証憑のファイルパスとURLも保存）
API：
GET /api/tasks.php?status=active - 有効なタスク一覧
POST /api/task-records.php - タスク報告
POST /api/upload.php - 証憑アップロード
4.4.3 売上承認
URL：/user/sales-approval.php
画面構成：
デフォルト：未承認（ユーザー確認待ち）一覧
その下に承認済み一覧（折りたたみ可）
未承認一覧：
表示項目：日付、商品名、数量、単価、ポイント、備考
売上金額は表示しない
操作：
承認ボタン
却下ボタン（却下理由必須、モーダル表示）
承認済み一覧：
表示項目：日付、商品名、数量、単価、ポイント、承認日時
売上金額は表示しない
API：
GET /api/sales.php?member_id={id}&status=pending - 未承認一覧
GET /api/sales.php?member_id={id}&status=approved - 承認済み一覧
PUT /api/sales/user-approve.php?id={id} - ユーザー承認
PUT /api/sales/user-reject.php?id={id} - ユーザー却下




3 件の返信


髙濵涼太郎
  今日 12:11
4.4.4 ランキング
URL：/user/ranking.php
画面構成：
右上タブで切り替え（個人/チーム）
フィルタUI：日/週/月 × 承認済のみ/全件
表示項目：
順位
メンバー名 or チーム名
ポイント合計
売上金額は表示しない
機能：
自分の行をハイライト（背景色変更）
期間範囲表示（YYYY/MM/DD 〜 YYYY/MM/DD）
ポイント降順
API：
GET /api/ranking.php?type={individual|team}&period={day|week|month}&filter={approved|all}&date={YYYY-MM-DD}
4.4.5 掲示板
URL：/user/bulletins.php
表示内容：
公開済みの掲示板投稿のみ表示
ピン留めは最上部にハイライト表示
イベント告知の場合：
イベントごとのランキング表を埋め込み表示
個人/チーム切り替えタブあり
期間フィルタは不要（イベント期間内の集計のみ）
API：
GET /api/bulletins.php?status=public - 公開投稿一覧
GET /api/ranking.php?event_id={id}&type={individual|team} - イベント別ランキング
（編集済み）
[18:56]
5. データベース設計（MySQL with PHPMyAdmin）
5.1 データベース設計方針
文字コード：UTF-8（utf8mb4）
照合順序：utf8mb4_unicode_ci
ストレージエンジン：InnoDB
トランザクション：必要に応じて使用
外部キー制約：参照整合性が重要な箇所に設定
マルチテナント：全テーブルにtenant_idカラムを追加
インデックス：tenant_idに必ずインデックスを設定
5.2 テーブル定義
5.2.1 テナントマスタ（tenants）
sql
CREATE TABLE tenants (
    tenant_id VARCHAR(10) PRIMARY KEY,
    tenant_name VARCHAR(100) NOT NULL,
    subdomain VARCHAR(50) UNIQUE,
    status VARCHAR(20) NOT NULL DEFAULT '有効',
    contract_start_date DATE,
    contract_end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_subdomain (subdomain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
初期データ（デモテナント）：
sql
INSERT INTO tenants (tenant_id, tenant_name, subdomain, status, contract_start_date) VALUES
('DEMO01', 'デモ店舗', 'demo', '有効', CURDATE());
5.2.2 設定テーブル（settings）
sql
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(10) NOT NULL,
    setting_key VARCHAR(50) NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tenant_key (tenant_id, setting_key),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
初期データ（デモテナント）：
sql
INSERT INTO settings (tenant_id, setting_key, setting_value) VALUES
('DEMO01', 'store_name', 'デモ店舗'),
('DEMO01', 'timezone', 'Asia/Tokyo'),
('DEMO01', 'week_start_day', 'Mon'),
('DEMO01', 'default_approval_sales', '必要'),
('DEMO01', 'default_approval_action', '必要'),
('DEMO01', 'default_approval_task', '必要'),
('DEMO01', 'image_max_mb', '10'),
('DEMO01', 'app_version', '1.0');
5.2.3 メンバーマスタ（members）
sql
CREATE TABLE members (
    member_id VARCHAR(10) NOT NULL,
    tenant_id VARCHAR(10) NOT NULL,
    name VARCHAR(100) NOT NULL,
    team_id VARCHAR(10),
    status VARCHAR(20) NOT NULL DEFAULT '有効',
    login_id VARCHAR(50) NOT NULL,
    pin VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tenant_id, member_id),
    UNIQUE KEY unique_tenant_login (tenant_id, login_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_team_id (tenant_id, team_id),
    INDEX idx_login_id (tenant_id, login_id),
    INDEX idx_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
初期データ（管理者アカウント）：
sql
INSERT INTO members (member_id, tenant_id, name, team_id, status, login_id, pin, role, description) VALUES
('M001', 'DEMO01', 'システム管理者', NULL, '有効', 'admin', '1234', 'admin', '初期管理者アカウント');
5.2.4 チームマスタ（teams）
sql
CREATE TABLE teams (
    team_id VARCHAR(10) NOT NULL,
    tenant_id VARCHAR(10) NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT '有効',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tenant_id, team_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
5.2.5 商品マスタ（products）
sql
CREATE TABLE products (
    product_id VARCHAR(10) NOT NULL,
    tenant_id VARCHAR(10) NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    large_category VARCHAR(50) COMMENT '例：飲食',
    medium_category VARCHAR(50) COMMENT '例：麺類',
    small_category VARCHAR(50) COMMENT '例：たれそば',
    point INT NOT NULL DEFAULT 0,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    cost DECIMAL(10,2),
    status VARCHAR(20) NOT NULL DEFAULT '有効',
    description TEXT,
    approval_required VARCHAR(20) NOT NULL DEFAULT '必要',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tenant_id, product_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
5.2.6 アクションマスタ（actions）
sql
CREATE TABLE actions (
    action_id VARCHAR(10) NOT NULL,
    tenant_id VARCHAR(10) NOT NULL,
    action_name VARCHAR(100) NOT NULL COMMENT '例：Google口コミ獲得',
    target VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT '有効',
    description TEXT,
    point INT NOT NULL DEFAULT 0,
    approval_required VARCHAR(20) NOT NULL DEFAULT '必要',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tenant_id, action_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (tenant_id, status),
    INDEX idx_target (tenant_id, target),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
12:12
5.2.7 タスクマスタ（tasks）
sql
CREATE TABLE tasks (
    task_id VARCHAR(10) NOT NULL,
    tenant_id VARCHAR(10) NOT NULL,
    task_name VARCHAR(100) NOT NULL,
    type VARCHAR(20) NOT NULL,
    repeat_type VARCHAR(20) NOT NULL,
    days_of_week VARCHAR(50),
    day_of_month VARCHAR(10),
    point INT NOT NULL DEFAULT 0,
    daily_limit INT DEFAULT 0,
    approval_required VARCHAR(20) NOT NULL DEFAULT '必要',
    status VARCHAR(20) NOT NULL DEFAULT '有効',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tenant_id, task_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (tenant_id, status),
    INDEX idx_type (tenant_id, type),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
5.2.8 イベントマスタ（events）
sql
CREATE TABLE events (
    event_id VARCHAR(10) NOT NULL,
    tenant_id VARCHAR(10) NOT NULL,
    event_name VARCHAR(100) NOT NULL COMMENT '例：肉の日キャンペーン',
    repeat_type VARCHAR(20) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days_of_week VARCHAR(50),
    day_of_month VARCHAR(10) COMMENT '例：29',
    target_type VARCHAR(50) NOT NULL,
    target_ids TEXT COMMENT '例：肉類商品ID',
    multiplier DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    status VARCHAR(20) NOT NULL DEFAULT '有効',
    description TEXT COMMENT '例：毎月29日は肉類商品ポイント2倍',
    publish_notice BOOLEAN DEFAULT FALSE,
    notice_title VARCHAR(200),
    notice_body TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tenant_id, event_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (tenant_id, status),
    INDEX idx_dates (tenant_id, start_date, end_date),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
5.2.9 掲示板（bulletins）
sql
CREATE TABLE bulletins (
    bulletin_id VARCHAR(10) NOT NULL,
    tenant_id VARCHAR(10) NOT NULL,
    type VARCHAR(20) NOT NULL,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    start_datetime DATETIME,
    end_datetime DATETIME,
    related_event_id VARCHAR(10),
    pinned BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) NOT NULL DEFAULT '下書き',
    author VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (tenant_id, bulletin_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (tenant_id, status),
    INDEX idx_pinned (tenant_id, pinned),
    INDEX idx_related_event (tenant_id, related_event_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
5.2.10 売上実績（sales_records）
sql
CREATE TABLE sales_records (
    id INT AUTO_INCREMENT,
    tenant_id VARCHAR(10) NOT NULL,
    date DATE NOT NULL,
    member_id VARCHAR(10) NOT NULL,
    product_id VARCHAR(10) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    base_point INT NOT NULL DEFAULT 0,
    event_multiplier DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    final_point INT NOT NULL DEFAULT 0,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_status VARCHAR(50) NOT NULL DEFAULT 'ユーザー確認待ち',
    approver VARCHAR(10),
    approved_at DATETIME,
    reject_reason TEXT,
    PRIMARY KEY (id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_member (tenant_id, member_id),
    INDEX idx_product (tenant_id, product_id),
    INDEX idx_date (tenant_id, date),
    INDEX idx_approval_status (tenant_id, approval_status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
[18:56]
5.2.11 アクション実績（action_records）
sql
CREATE TABLE action_records (
    id INT AUTO_INCREMENT,
    tenant_id VARCHAR(10) NOT NULL,
    date DATE NOT NULL,
    member_id VARCHAR(10) NOT NULL,
    action_id VARCHAR(10) NOT NULL,
    count INT NOT NULL DEFAULT 1,
    base_point INT NOT NULL DEFAULT 0,
    event_multiplier DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    final_point INT NOT NULL DEFAULT 0,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_status VARCHAR(50) NOT NULL DEFAULT '未承認',
    approver VARCHAR(10),
    approved_at DATETIME,
    reject_reason TEXT,
    evidence_file_path VARCHAR(255),
    evidence_url TEXT,
    PRIMARY KEY (id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_member (tenant_id, member_id),
    INDEX idx_action (tenant_id, action_id),
    INDEX idx_date (tenant_id, date),
    INDEX idx_approval_status (tenant_id, approval_status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
5.2.12 タスク実績（task_records）
sql
CREATE TABLE task_records (
    id INT AUTO_INCREMENT,
    tenant_id VARCHAR(10) NOT NULL,
    date DATE NOT NULL,
    member_id VARCHAR(10) NOT NULL,
    task_id VARCHAR(10) NOT NULL,
    count INT NOT NULL DEFAULT 1,
    base_point INT NOT NULL DEFAULT 0,
    final_point INT NOT NULL DEFAULT 0,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_status VARCHAR(50) NOT NULL DEFAULT '未承認',
    approver VARCHAR(10),
    approved_at DATETIME,
    reject_reason TEXT,
    evidence_file_path VARCHAR(255),
    evidence_url TEXT,
    PRIMARY KEY (id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_member (tenant_id, member_id),
    INDEX idx_task (tenant_id, task_id),
    INDEX idx_date (tenant_id, date),
    INDEX idx_approval_status (tenant_id, approval_status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
5.2.13 セッション（sessions）
sql
CREATE TABLE sessions (
    token VARCHAR(255) PRIMARY KEY,
    tenant_id VARCHAR(10) NOT NULL,
    member_id VARCHAR(10) NOT NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    last_accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_member (tenant_id, member_id),
    INDEX idx_expires (expires_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
5.2.14 監査ログ（audit_logs）
sql
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(10) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    action VARCHAR(100) NOT NULL,
    record_type VARCHAR(50),
    table_name VARCHAR(50),
    row_id INT,
    member_id VARCHAR(10),
    operator VARCHAR(10),
    user_display_name VARCHAR(100),
    details TEXT,
    INDEX idx_tenant (tenant_id),
    INDEX idx_timestamp (tenant_id, timestamp),
    INDEX idx_member (tenant_id, member_id),
    INDEX idx_operator (tenant_id, operator),
    FOREIGN KEY (tenant_id) REFERENCES tenants(tenant_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
6. 画面設計
6.1 共通レイアウト
6.1.1 ヘッダー
ロゴ：インセンティブSaaS
テナント名：店舗名表示
ユーザー名：ログイン中のユーザー名を表示
ログアウトボタン
6.1.2 ナビゲーション（上部タブ）
デザイン：アプリ風タブUI（ブラウザタブ風ではない）
アクティブタブ：下線または背景色でハイライト
管理者（admin）タブ：
マスタ管理
売上管理
承認管理
実績管理
掲示板管理
ランキング
ユーザー（user）タブ：
ホーム
報告
売上承認
ランキング
掲示板
6.1.3 フッター
コピーライト：© 2025 インセンティブSaaS
6.2 画面詳細
6.2.1 ログイン画面（/login.php）
レイアウト：中央配置、カード型
入力項目：
テナントID（テキストボックス、またはサブドメインから自動判定）
ログインID（テキストボックス）
PIN（パスワードボックス、4桁）
ボタン：ログイン
エラー表示：認証失敗時に赤文字でメッセージ表示
6.2.2 ホーム画面（/user/home.php）
レイアウト：
上部：お知らせカード（最新3件）
中央上：イベント一覧（タブ切り替え：開催中/開催前）
中央下：未達成タスク（ステータスバー付きカード、報告ボタン）
イベント一覧（開催中タブ）：
表示項目：イベント名、期間、倍率、説明
カード形式で表示
イベント一覧（開催前タブ）：
表示項目：イベント名、開始日、倍率、説明
カード形式で表示
未達成タスク：
各タスクカードに進捗ステータスバー表示
例：「清掃タスク 3/5回完了」→ 60%のバー表示
「報告」ボタン付き
6.2.3 報告画面（/user/report.php）
レイアウト：
アクション/タスクをカード表示（グリッド形式）
タスクカードにはステータスバー表示
カードクリック → 報告モーダル表示
報告モーダル：
アクション/タスク名
回数（固定1）
備考（任意）
証憑添付（ファイル選択 → プレビュー）
保存ボタン
:デスクトップコンピューター: 7. 開発環境・デプロイ
7.1 開発環境
ローカル開発環境
XAMPP / MAMP / LaragonPHP 8.x
MySQL 8.x
Apache 2.4+
PHPMyAdmin（データベース管理）
推奨IDE
Visual Studio Code拡張機能：PHP Intelephense, Prettier, Tailwind CSS IntelliSense
7.2 ディレクトリ構成
/incentive-saas/
│
├── /public/                    # 公開ディレクトリ（ドキュメントルート）
│   ├── index.php               # エントリーポイント（ログインチェック → リダイレクト）
│   ├── login.php               # ログイン画面
│   ├── logout.php              # ログアウト処理
│   │
│   ├── /admin/                 # 管理者画面
│   │   ├── /masters/           # マスタ管理
│   │   │   ├── members.php
│   │   │   ├── teams.php
│   │   │   ├── products.php
│   │   │   ├── actions.php
│   │   │   ├── tasks.php
│   │   │   └── events.php
│   │   ├── /sales/             # 売上管理
│   │   │   └── input.php
│   │   ├── approvals.php       # 承認管理
│   │   ├── reports.php         # 実績管理
│   │   ├── bulletins.php       # 掲示板管理
│   │   └── ranking.php         # ランキング
│   │
│   ├── /user/                  # ユーザー画面
│   │   ├── home.php            # ホーム
│   │   ├── report.php          # 報告
│   │   ├── sales-approval.php  # 売上承認
│   │   ├── ranking.php         # ランキング
│   │   └── bulletins.php       # 掲示板
│   │
│   ├── /api/                   # API（Ajaxエンドポイント）
│   │   ├── members.php
│   │   ├── teams.php
│   │   ├── products.php
│   │   ├── actions.php
│   │   ├── tasks.php
│   │   ├── events.php
│   │   ├── sales.php
│   │   ├── action-records.php
│   │   ├── task-records.php
│   │   ├── bulletins.php
│   │   ├── ranking.php
│   │   ├── home.php            # ホーム情報取得
│   │   ├── upload.php          # 証憑アップロード
│   │   └── /reports/
│   │       ├── by-product.php
│   │       ├── by-member.php
│   │       ├── by-team.php
│   │       └── by-event.php
│   │
│   ├── /assets/                # 静的ファイル
│   │   ├── /css/
│   │   │   └── style.css       # カスタムCSS
│   │   ├── /js/
│   │   │   └── app.js          # メインJavaScript
│   │   └── /images/            # 画像ファイル
│   │
│   └── /uploads/               # アップロードファイル（テナント別）
│       ├── /DEMO01/
│       │   └── /evidence/      # 証憑画像
│       └── /TENANT02/
│           └── /evidence/
│
├── /includes/                  # 共通ファイル
│   ├── config.php              # データベース接続設定
│   ├── db.php                  # データベース接続クラス
│   ├── auth.php                # 認証関連関数（テナント認証含む）
│   ├── functions.php           # 共通関数
│   ├── tenant_check.php        # テナントチェック機能
│   ├── header.php              # 共通ヘッダー
│   ├── footer.php              # 共通フッター
│   └── nav.php                 # ナビゲーション
│
├── /sql/                       # SQLファイル
│   ├── schema.sql              # テーブル定義
│   └── initial_data.sql        # 初期データ
│
├── .htaccess                   # Apache設定（リダイレクト等）
├── .env                        # 環境変数（DB接続情報）
└── README.md                   # プロジェクト説明
7.3 環境変数（.env）
env
DB_HOST=localhost
DB_NAME=incentive_saas
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

SESSION_LIFETIME=86400
UPLOAD_MAX_SIZE=10485760

TIMEZONE=Asia/Tokyo
7.4 デプロイ
本番環境
レンタルサーバー：さくらインターネット、エックスサーバー等
VPS：ConoHa VPS、Vultr等
クラウド：AWS EC2、Google Cloud Compute Engine等
デプロイ手順
サーバーにPHP 8.x、MySQL 8.xをインストール
PHPMyAdminでデータベースを作成（incentive_saas）
/sql/schema.sqlを実行してテーブル作成
/sql/initial_data.sqlを実行して初期データ投入
プロジェクトファイルをアップロード
.envファイルの接続情報を本番環境に合わせて編集
/uploads/{tenant_id}/evidence/ディレクトリに書き込み権限を付与（chmod 755）
SSL証明書を設定（Let's Encrypt推奨）
https://your-domain.com/にアクセスして動作確認
12:12
8. セキュリティ要件
8.1 認証・認可
パスワードハッシュ化：password_hash() / password_verify()
テナント分離：全クエリにtenant_id条件を付与
セッション管理：
session_regenerate_id(true)でセッションID再生成
セッションタイムアウト（24時間）
テナントIDをセッションに保存
CSRF対策：フォーム送信時にトークン検証
XSS対策：htmlspecialchars()でエスケープ
SQLインジェクション対策：PDOのプリペアドステートメント使用
8.2 ファイルアップロード
拡張子チェック：画像ファイルのみ許可（jpg, jpeg, png, gif）
MIMEタイプチェック：mime_content_type()で検証
ファイルサイズ制限：設定の「画像上限MB」に従う
ファイル名のサニタイズ：ランダム文字列にリネーム
テナント別ディレクトリ：/uploads/{tenant_id}/evidence/
8.3 通信
HTTPS必須：本番環境では必ずSSL/TLS通信
HTTPSリダイレクト：.htaccessで強制リダイレクト
9. 開発フェーズ
フェーズ0：環境構築（1日）
ローカル開発環境構築（XAMPP + PHPMyAdmin）
データベース作成・テーブル定義
初期データ投入
基本ディレクトリ構成作成
フェーズ1：認証・基盤（2日）
テナント管理機能
ログイン/ログアウト機能（テナント認証含む）
セッション管理
共通ヘッダー/フッター/ナビゲーション
権限チェック機能
フェーズ2：マスタ管理（3日）
メンバーマスタCRUD
チームマスタCRUD
商品マスタCRUD（大中小分類）
アクションマスタCRUD
タスクマスタCRUD
イベントマスタCRUD
フェーズ3：証憑アップロード（1日）
画像アップロード機能（テナント別ディレクトリ）
プレビュー表示
ファイル管理
フェーズ4：実績入力（2日）
売上入力機能
アクション報告機能
タスク報告機能
ホーム画面（イベント一覧、未達成タスク with ステータスバー）
フェーズ5：承認機能（2日）
売上承認（ユーザー・管理者）
アクション承認
タスク承認
フェーズ6：ランキング・実績管理（2日）
ランキング表示（個人/チーム）
実績管理（商品別/個人別/チーム別/イベント別）
イベント別ランキング
フェーズ7：掲示板（1日）
掲示板投稿機能
イベント告知との連携
ピン留め機能
フェーズ8：テスト・調整（2日）
単体テスト
統合テスト
マルチテナント動作確認
UI/UX調整
バグ修正
フェーズ9：本番デプロイ（1日）
本番環境セットアップ
データ移行
SSL設定
動作確認
合計開発期間：約17日
10. 補足事項
10.1 技術選定理由
Pure PHP：軽量、学習コスト低、レンタルサーバーで簡単にデプロイ可能
MySQL：実績豊富、PHPとの親和性が高い
PHPMyAdmin：視覚的なデータベース管理、初心者にも扱いやすい
Tailwind CSS：迅速なUI構築、レスポンシブ対応が容易
10.2 マルチテナント設計の利点
運用コスト削減：1つのシステムで複数店舗を管理
一括アップデート：全テナントに同時に機能追加可能
データ分離：テナントごとにデータが完全分離され、セキュリティ確保
10.3 拡張性
API化：モバイルアプリ連携を想定したRESTful API設計
通知機能：メール通知、プッシュ通知の追加
レポート機能強化：グラフ・チャート表示（Chart.js等）
10.4 パフォーマンス
インデックス最適化：頻繁に検索される列（tenant_id等）にインデックス設定
クエリ最適化：N+1問題の回避、JOIN最適化
キャッシュ：必要に応じてRedis導入
11. 受け入れ基準
フェーズ0
データベースが作成され、全テーブルが存在する
テナントマスタにデモテナント（DEMO01）が登録されている
初期管理者アカウント（DEMO01 / admin / 1234）でログイン可能
フェーズ1
テナントIDを含むログイン/ログアウトが正常に動作
管理者とユーザーで異なるタブが表示される
セッションにテナントIDが保存される
フェーズ2
全マスタで新規登録/編集/削除/複製が可能
ログインID重複チェックがテナント内で動作
無効ステータスのデータが選択候補に表示されない
承認要否のデフォルトが「必要」になっている
フェーズ3
画像アップロードが成功し、テナント別ディレクトリに保存される
プレビュー表示が正常に機能する
アップロードサイズ制限が機能する
フェーズ4
売上入力が保存され、一覧に表示される
アクション/タスク報告が保存され、証憑が紐づく
ホーム画面にイベント一覧（開催中/開催前タブ）が表示される
ホーム画面に未達成タスクがステータスバー付きで表示される
フェーズ5
ユーザーが自分の売上を承認/却下できる
管理者がアクション/タスクを承認/却下できる
承認状態が正しく更新される
フェーズ6
ランキングが正しく集計・表示される
実績管理で各種集計が表示される
イベント別ランキングが機能する
フェーズ7
掲示板投稿が作成・表示される
イベント告知が自動投稿される
ピン留めが最上部に表示される
フェーズ8
全機能が正常に動作する
マルチテナント動作が正常（テナント間でデータ漏洩なし）
レスポンシブ対応（スマホ・PC両対応）
エラーハンドリングが適切
フェーズ9
本番環境で全機能が動作する
SSL/TLS通信が有効
パフォーマンスが許容範囲
--要件終了--
--ログインID--
管理者
https://incentive.southagency.email/admin/dashboard.php
DEMO01
admin
1234
ユーザー
https://incentive.southagency.email/login.php
DEMO01
staff001
5678
--ログインID終了--
--倍率定義--
売上時のイベント倍率適用ルール
売上日はイベントの実施期間内であり、かつ次のいずれかの実施条件に一致する場合のみ、対象商品のポイント倍率を適用する。
"繰り返し"が
"単発"の場合：開始日〜終了日の期間内すべてが対象。
"毎週"の場合：指定された曜日に一致する売上日のみ対象。
"毎月"の場合：指定された毎月日（または末日）に一致する売上日のみ対象。
これらの条件を満たさない場合、倍率は適用しない（＝1.0倍扱い）。
 複数イベントが該当する場合は、最も高い倍率を採用する。