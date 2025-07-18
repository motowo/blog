# 有料コンテンツブログアプリケーション

## 概要
有料コンテンツを提供するブログサイトのMVP開発プロジェクトです。投稿ユーザーは自身のブログコンテンツを有料で提供し、サイト運営者はその管理および収益化を行います。

## 技術スタック
- **フロントエンド**: React.js (v18.x), TypeScript (v5.x), Tailwind CSS (v3.x)
- **バックエンド**: PHP (v8.3.x), Laravel (v11.x)
- **データベース**: MySQL (v8.0.x)
- **開発環境**: Docker, Docker Compose

## 開発環境のセットアップ

### 前提条件
- Docker Desktop がインストールされていること
- Git がインストールされていること

### セットアップ手順

1. リポジトリのクローン
```bash
git clone https://github.com/motowo/blog.git
cd blog
```

2. 開発環境の起動
```bash
docker-compose up -d
```

3. 依存関係のインストール
```bash
# フロントエンドの依存関係
docker-compose exec frontend npm install

# バックエンドの依存関係
docker-compose exec backend composer install
```

4. Laravelの初期設定
```bash
# .envファイルの作成
docker-compose exec backend cp .env.example .env

# アプリケーションキーの生成
docker-compose exec backend php artisan key:generate

# データベースマイグレーション
docker-compose exec backend php artisan migrate
```

5. アプリケーションへのアクセス
- フロントエンド: http://localhost:3000
- バックエンド API: http://localhost:8000
- データベース: localhost:3306

## 開発コマンド

### Makefile を使用する場合
```bash
make up         # 環境起動
make down       # 環境停止
make restart    # 環境再起動
make logs       # ログ確認
make frontend   # フロントエンドコンテナへアクセス
make backend    # バックエンドコンテナへアクセス
make db         # データベースへアクセス
make test       # テスト実行
make lint       # リント実行
make format     # コード整形
```

### Docker Compose を直接使用する場合
```bash
# 環境起動
docker-compose up -d

# 環境停止
docker-compose down

# ログ確認
docker-compose logs -f

# コンテナへのアクセス
docker-compose exec frontend sh
docker-compose exec backend sh
```

## テスト・品質管理

### フロントエンド
```bash
# テスト実行
docker-compose exec frontend npm test

# リント実行
docker-compose exec frontend npm run lint

# コード整形
docker-compose exec frontend npm run format
```

### バックエンド
```bash
# テスト実行
docker-compose exec backend php artisan test

# リント実行
docker-compose exec backend ./vendor/bin/pint --test

# コード整形
docker-compose exec backend ./vendor/bin/pint
```

## プロジェクト構成
```
blog/
├── frontend/           # React.js フロントエンド
│   ├── public/        # 静的ファイル
│   ├── src/           # ソースコード
│   └── package.json   # 依存関係
├── backend/           # Laravel バックエンド
│   ├── app/           # アプリケーションコード
│   ├── database/      # マイグレーション、シーダー
│   └── composer.json  # 依存関係
├── database/          # データベース初期化スクリプト
├── docs/              # ドキュメント
│   ├── requirements.md # 要件定義書
│   ├── design.md      # 技術設計書
│   └── tasks.md       # 開発タスク
├── docker-compose.yml # Docker Compose設定
├── Makefile          # 開発用コマンド
└── README.md         # このファイル
```

## MVP機能一覧
- ユーザー認証（登録・ログイン）
- 記事投稿・編集・閲覧（投稿ユーザー）
- 記事の有料・無料設定
- 記事検索
- 有料記事の購入（モック決済）
- ユーザー管理（サイト運営者）
- 記事管理（サイト運営者）
- 収益管理（サイト運営者）

## 開発ルール
開発ルールについては [CLAUDE.md](./CLAUDE.md) を参照してください。