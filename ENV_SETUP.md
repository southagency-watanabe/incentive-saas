# 環境設定ガイド

このプロジェクトでは環境変数を使用してローカルと本番環境の設定を管理しています。

## セットアップ方法

### ローカル環境

ローカル環境用の `.env` ファイルはすでに作成されています。必要に応じて設定を変更してください。

```bash
# .env ファイルの内容を確認
cat .env
```

### 本番環境へのデプロイ

本番環境にデプロイする際は、以下の手順で設定を切り替えます:

1. **本番設定ファイルをコピー**
   ```bash
   cp .env.production .env
   ```

2. **本番環境の値を設定**
   `.env` ファイルを編集して、本番環境の実際の値に置き換えます:
   ```bash
   nano .env
   # または
   vim .env
   ```

3. **必ず変更する項目:**
   - `DB_HOST`: 本番データベースのホスト名
   - `DB_NAME`: 本番データベース名
   - `DB_USER`: 本番データベースユーザー名
   - `DB_PASS`: 本番データベースパスワード
   - `BASE_URL`: 本番環境のURL
   - `DEBUG_MODE`: 本番では必ず `0` に設定

### ローカルに戻る場合

本番環境からローカルに戻る際は、ローカル設定を復元します:

```bash
# Git管理されているファイルをリセット
git checkout .env

# または、手動でローカル設定に戻す
cat > .env << 'EOF'
ENVIRONMENT=local
DB_HOST=localhost
DB_NAME=incentive_local
DB_USER=root
DB_PASS=
SESSION_COOKIE_SECURE=0
SESSION_COOKIE_DOMAIN=
BASE_URL=http://localhost
DEBUG_MODE=1
EOF
```

## 環境変数一覧

| 変数名 | 説明 | ローカル | 本番 |
|--------|------|----------|------|
| ENVIRONMENT | 環境タイプ | local | production |
| DB_HOST | データベースホスト | localhost | 本番DBホスト |
| DB_NAME | データベース名 | incentive_local | 本番DB名 |
| DB_USER | DBユーザー名 | root | 本番DBユーザー |
| DB_PASS | DBパスワード | (空) | 本番DBパスワード |
| SESSION_COOKIE_SECURE | セキュアCookie | 0 | 1 |
| SESSION_COOKIE_DOMAIN | Cookieドメイン | (空) | (空) |
| BASE_URL | ベースURL | http://localhost | https://your-domain.com |
| DEBUG_MODE | デバッグモード | 1 | 0 |

## 注意事項

- `.env` ファイルは Git にコミットされません（`.gitignore` で除外）
- `.env.production` はテンプレートとして Git 管理されています
- 本番環境の認証情報は決して Git にコミットしないでください
- デプロイ前に必ず `DEBUG_MODE=0` に設定してください

## トラブルシューティング

### データベース接続エラー

1. `.env` ファイルが存在するか確認
2. データベース接続情報が正しいか確認
3. ローカル環境の場合、MySQLが起動しているか確認

### セッションエラー

1. `SESSION_COOKIE_SECURE` の設定を確認
   - HTTP接続の場合: `0`
   - HTTPS接続の場合: `1`

### 設定が反映されない

1. PHP のキャッシュをクリア
2. ウェブサーバーを再起動
   ```bash
   # Apache の場合
   sudo apachectl restart

   # Nginx + PHP-FPM の場合
   sudo systemctl restart php-fpm
   sudo systemctl restart nginx
   ```
