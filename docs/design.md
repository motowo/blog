# 技術設計書

## 1. システム構成
- **フロントエンド**: Webブラウザで動作するユーザーインターフェース。
    - 技術スタック: React.js
    - UIフレームワーク: Tailwind CSS
- **バックエンド**: API提供、ビジネスロジック、データベース連携。
    - 技術スタック: PHP (Laravel Framework)
    - 認証: Laravel Sanctum (SPA認証) または JWT (検討)
- **データベース**: ユーザー情報、記事情報、決済情報などを格納。
    - 種類: MySQL
- **ファイルストレージ**: 画像、動画などのメディアファイルを保存。
    - サービス: AWS S3
- **決済システム**: 有料記事の購入処理。
    - サービス: モック実装 (クレジットカード決済を想定し、特定の番号入力でOK/NGを判断)
- **デプロイ環境**:
    - サーバー: AWS EC2
    - コンテナ化: Docker / Docker Compose (開発環境および本番環境)

## 2. データ設計

### 2.1. エンティティと属性

#### User
| 属性名           | データ型     | 制約           | 説明                               |
| :--------------- | :----------- | :------------- | :--------------------------------- |
| `id`             | INT          | PK, Auto Inc.  | ユーザーID                         |
| `email`          | VARCHAR(255) | Unique         | メールアドレス                     |
| `password`       | VARCHAR(255) | Not Null       | パスワード (ハッシュ化)            |
| `username`       | VARCHAR(255) | Not Null       | ユーザー名                         |
| `role`           | ENUM         | Not Null       | ロール (投稿ユーザー, サイト運営者) |
| `profile_image_url` | VARCHAR(255) | Nullable       | プロフィール画像URL                |
| `bio`            | TEXT         | Nullable       | 自己紹介                           |
| `created_at`     | TIMESTAMP    | Not Null       | 登録日時                           |
| `updated_at`     | TIMESTAMP    | Not Null       | 更新日時                           |

#### Article
| 属性名           | データ型     | 制約           | 説明                               |
| :--------------- | :----------- | :------------- | :--------------------------------- |
| `id`             | INT          | PK, Auto Inc.  | 記事ID                             |
| `user_id`        | INT          | FK (User)      | 投稿ユーザーID                     |
| `category_id`    | INT          | FK (Category)  | カテゴリID                         |
| `title`          | VARCHAR(255) | Not Null       | 記事タイトル                       |
| `content`        | TEXT         | Not Null       | 記事本文 (Markdown / HTML)         |
| `thumbnail_url`  | VARCHAR(255) | Nullable       | サムネイル画像URL                  |
| `status`         | ENUM         | Not Null       | 公開ステータス (公開, 非公開)      |
| `is_paid`        | BOOLEAN      | Not Null       | 有料/無料 (True: 有料, False: 無料) |
| `price`          | DECIMAL(10,2)| Nullable       | 価格 (有料の場合)                  |
| `preview_content`| TEXT         | Nullable       | プレビュー用コンテンツ             |
| `created_at`     | TIMESTAMP    | Not Null       | 投稿日時                           |
| `updated_at`     | TIMESTAMP    | Not Null       | 更新日時                           |

#### Category
| 属性名       | データ型     | 制約           | 説明             |
| :----------- | :----------- | :------------- | :--------------- |
| `id`         | INT          | PK, Auto Inc.  | カテゴリID       |
| `name`       | VARCHAR(255) | Unique, Not Null | カテゴリ名       |
| `created_at` | TIMESTAMP    | Not Null       | 作成日時         |
| `updated_at` | TIMESTAMP    | Not Null       | 更新日時         |

#### Payment
| 属性名           | データ型     | 制約           | 説明                               |
| :--------------- | :----------- | :------------- | :--------------------------------- |
| `id`             | INT          | PK, Auto Inc.  | 決済ID                             |
| `user_id`        | INT          | FK (User)      | 購入者ユーザーID                   |
| `article_id`     | INT          | FK (Article)   | 購入記事ID                         |
| `amount`         | DECIMAL(10,2)| Not Null       | 決済金額                           |
| `status`         | ENUM         | Not Null       | 決済ステータス (成功, 失敗, 保留)  |
| `transaction_id` | VARCHAR(255) | Unique, Not Null | 決済トランザクションID             |
| `paid_at`        | TIMESTAMP    | Not Null       | 決済日時                           |
| `created_at`     | TIMESTAMP    | Not Null       | 作成日時                           |
| `updated_at`     | TIMESTAMP    | Not Null       | 更新日時                           |

#### Comment
| 属性名       | データ型     | 制約           | 説明             |
| :----------- | :----------- | :------------- | :--------------- |
| `id`         | INT          | PK, Auto Inc.  | コメントID       |
| `user_id`    | INT          | FK (User)      | コメント投稿者ID |
| `article_id` | INT          | FK (Article)   | 記事ID           |
| `content`    | TEXT         | Not Null       | コメント内容     |
| `created_at` | TIMESTAMP    | Not Null       | 投稿日時         |
| `updated_at` | TIMESTAMP    | Not Null       | 更新日時         |

#### Payout
| 属性名           | データ型     | 制約           | 説明                               |
| :--------------- | :----------- | :------------- | :--------------------------------- |
| `id`             | INT          | PK, Auto Inc.  | 収益分配ID                         |
| `user_id`        | INT          | FK (User)      | 投稿ユーザーID                     |
| `period`         | VARCHAR(255) | Not Null       | 期間 (例: 2023-07)                 |
| `amount`         | DECIMAL(10,2)| Not Null       | 支払金額                           |
| `status`         | ENUM         | Not Null       | 支払ステータス (未処理, 処理済み, 失敗) |
| `paid_at`        | TIMESTAMP    | Nullable       | 支払日時                           |
| `bank_account_info` | JSON         | Nullable       | 振込先口座情報 (暗号化して保存)    |
| `created_at`     | TIMESTAMP    | Not Null       | 作成日時                           |
| `updated_at`     | TIMESTAMP    | Not Null       | 更新日時                           |

### 2.2. ER図 (Mermaid形式)
```mermaid
erDiagram
    User ||--o{ Article : "has"
    User ||--o{ Payment : "makes"
    User ||--o{ Comment : "posts"
    User ||--o{ Payout : "receives"
    Article ||--o{ Comment : "has"
    Article ||--o{ Payment : "is_bought_as"
    Category ||--o{ Article : "categorizes"

    User {
        int id PK
        varchar email UK
        varchar password
        varchar username
        enum role
        varchar profile_image_url
        text bio
        timestamp created_at
        timestamp updated_at
    }

    Article {
        int id PK
        int user_id FK
        int category_id FK
        varchar title
        text content
        varchar thumbnail_url
        enum status
        boolean is_paid
        decimal price
        text preview_content
        timestamp created_at
        timestamp updated_at
    }

    Category {
        int id PK
        varchar name UK
        timestamp created_at
        timestamp updated_at
    }

    Payment {
        int id PK
        int user_id FK "buyer"
        int article_id FK
        decimal amount
        enum status
        varchar transaction_id UK
        timestamp paid_at
        timestamp created_at
        timestamp updated_at
    }

    Comment {
        int id PK
        int user_id FK "commenter"
        int article_id FK
        text content
        timestamp created_at
        timestamp updated_at
    }

    Payout {
        int id PK
        int user_id FK "recipient"
        varchar period
        decimal amount
        enum status
        timestamp paid_at
        json bank_account_info
        timestamp created_at
        timestamp updated_at
    }
```

## 3. インターフェース設計

### 3.1. RESTful API一覧
| 機能カテゴリ   | 機能名             | HTTPメソッド | エンドポイント                 | 説明                                   |
| :------------- | :----------------- | :----------- | :----------------------------- | :------------------------------------- |
| ユーザー認証   | ユーザー登録       | POST         | `/api/auth/register`           | 新規ユーザーを登録する                 |
|                | ログイン           | POST         | `/api/auth/login`              | ユーザー認証を行い、トークンを発行する |
| ユーザー管理   | ユーザー一覧取得   | GET          | `/api/users`                   | 全ユーザーの一覧を取得する             |
|                | ユーザー詳細取得   | GET          | `/api/users/{id}`              | 特定ユーザーの詳細を取得する           |
|                | ユーザー情報更新   | PUT          | `/api/users/{id}`              | 特定ユーザーの情報を更新する           |
|                | ユーザーアカウント停止 | DELETE       | `/api/users/{id}`              | 特定ユーザーのアカウントを停止する     |
| 記事管理       | 記事作成           | POST         | `/api/articles`                | 新規記事を作成する                     |
|                | 記事一覧取得       | GET          | `/api/articles`                | 全記事の一覧を取得する                 |
|                | 記事詳細取得       | GET          | `/api/articles/{id}`           | 特定記事の詳細を取得する               |
|                | 記事更新           | PUT          | `/api/articles/{id}`           | 特定記事を更新する                     |
|                | 記事削除           | DELETE       | `/api/articles/{id}`           | 特定記事を削除する                     |
| カテゴリ管理   | カテゴリ一覧取得   | GET          | `/api/categories`              | 全カテゴリの一覧を取得する             |
|                | カテゴリ作成       | POST         | `/api/categories`              | 新規カテゴリを作成する                 |
|                | カテゴリ更新       | PUT          | `/api/categories/{id}`         | 特定カテゴリを更新する                 |
|                | カテゴリ削除       | DELETE       | `/api/categories/{id}`         | 特定カテゴリを削除する                 |
| 決済           | 記事購入           | POST         | `/api/payments`                | 有料記事の購入処理を行う               |
|                | 決済履歴取得       | GET          | `/api/payments`                | 自身の決済履歴を取得する               |
| コメント       | コメント投稿       | POST         | `/api/articles/{id}/comments`  | 特定記事にコメントを投稿する           |
|                | コメント一覧取得   | GET          | `/api/articles/{id}/comments`  | 特定記事のコメント一覧を取得する       |
| 収益           | 収益状況取得       | GET          | `/api/payouts/summary`         | サイト全体の収益状況を取得する         |
|                | 収益分配処理       | POST         | `/api/payouts`                 | 投稿ユーザーへの収益分配処理を行う     |
|                | 収益受け取り設定   | PUT          | `/api/users/{id}/payout-settings` | 投稿ユーザーの収益受け取り設定を更新する |
| サイト設定     | サイト設定取得     | GET          | `/api/settings`                | サイト設定情報を取得する               |
|                | サイト設定更新     | PUT          | `/api/settings`                | サイト設定情報を更新する               |
| お知らせ       | お知らせ一覧取得   | GET          | `/api/announcements`           | お知らせの一覧を取得する               |
|                | お知らせ作成       | POST         | `/api/announcements`           | 新規お知らせを作成する                 |
|                | お知らせ更新       | PUT          | `/api/announcements/{id}`      | 特定お知らせを更新する                 |
|                | お知らせ削除       | DELETE       | `/api/announcements/{id}`      | 特定お知らせを削除する                 |
| お問い合わせ   | お問い合わせ送信   | POST         | `/api/contact`                 | お問い合わせを送信する                 |

### 3.2. UI/UX設計
- ユーザーフレンドリーなインターフェース。
- PCのみ対応。
- 直感的な操作性。

## 4. セキュリティ設計
- **認証**: Laravel Sanctum または JWTによるセッション管理。
- **認可**: ロールベースアクセス制御 (RBAC)。
- **パスワード**: ハッシュ化して保存。
- **入力値検証**: 全てのユーザー入力に対して厳格な検証。
- **HTTPS**: 全ての通信を暗号化。
- **決済情報**: 決済代行サービスを利用し、サイト内で機密情報を保持しない。
- **脆弱性対策**: OWASP Top 10を考慮した設計・実装。

## 5. 機能一覧
| 優先度 | 登場人物     | 機能カテゴリ       | 機能名             | 説明                                   |
| :----- | :----------- | :----------------- | :----------------- | :------------------------------------- |
| MVP    | 共通         | 記事閲覧           | 記事検索           | 記事の検索機能                         |
| MVP    | 共通         | 記事閲覧           | 無料記事閲覧       | 無料記事の閲覧                         |
| MVP    | 共通         | 記事閲覧           | 有料記事購入・閲覧 | 有料記事の購入・閲覧（決済連携）       |
| MVP    | サイト運営者 | 記事管理           | 記事管理           | 全記事の一覧表示、内容確認、公開・非公開設定、削除 |
| MVP    | サイト運営者 | 収益管理           | 収益管理           | サイト全体の収益状況の確認、決済履歴の確認 |
| MVP    | サイト運営者 | ユーザー管理       | ユーザー管理       | 投稿ユーザーの一覧表示、アカウント停止 |
| MVP    | 投稿ユーザー | 記事管理           | 記事投稿・編集     | テキスト、画像などを利用した記事作成、公開・非公開設定、有料・無料設定 |
| MVP    | 投稿ユーザー | 記事管理           | 自身の記事閲覧     | 投稿した記事の一覧表示と詳細閲覧       |
| MVP    | 投稿ユーザー | ユーザー認証       | ユーザー登録・ログイン | メールアドレスとパスワードによる登録・ログイン |
| 高     | 共通         | お問い合わせ       | お問い合わせ機能   | サイト運営者へのお問い合わせフォーム   |
| 高     | 共通         | コメント           | コメント機能       | 記事へのコメント投稿、閲覧             |
| 高     | サイト運営者 | サイト設定         | サイト設定         | サイト名、ロゴ、利用規約、プライバシーポリシーなどの設定 |
| 高     | サイト運営者 | 収益管理           | 投稿ユーザーへの収益分配管理 | 投稿ユーザーへの収益分配処理を行う     |
| 高     | 投稿ユーザー | ダッシュボード     | ダッシュボード     | 自身の収益状況の確認                   |
| 高     | 投稿ユーザー | プロフィール管理   | プロフィール管理   | プロフィール情報（ユーザー名、自己紹介など）の編集 |
| 高     | 投稿ユーザー | 収益管理           | 収益受け取り設定   | 収益の振込先口座情報の設定             |
| 中     | サイト運営者 | お知らせ           | お知らせ・アナウンス機能 | サイト全体へのお知らせ配信             |
| 中     | サイト運営者 | コンテンツカテゴリ管理 | コンテンツカテゴリ管理 | 記事のカテゴリの追加・編集・削除       |
| 中     | 投稿ユーザー | 記事管理           | 記事プレビュー機能 | 記事の公開前に内容を確認する機能       |
| 低     | 共通         | 拡張機能           | 記事の動画コンテンツ対応 | 記事内に動画コンテンツを埋め込む機能   |
| 低     | 共通         | 拡張機能           | 高度な分析レポート機能 | サイトのアクセス状況や収益の詳細な分析レポート |
| 低     | 共通         | 拡張機能           | サブスクリプションモデル | 定期購読モデルの導入                   |
| 低     | 共通         | 拡張機能           | ライブ配信機能     | リアルタイムでのコンテンツ配信         |

## 6. 画面一覧
| 優先度 | 登場人物     | 画面名             | URL                            | 説明                                   |
| :----- | :----------- | :----------------- | :----------------------------- | :------------------------------------- |
| MVP    | 共通         | 記事購入画面       | `/articles/{id}/purchase`      | 有料記事の購入手続きを行う画面         |
| MVP    | 共通         | 記事詳細画面 (無料/有料) | `/articles/{id}`               | 記事の詳細内容を表示する画面           |
| MVP    | 共通         | 検索結果画面       | `/search`                      | 記事検索の結果を表示する画面           |
| MVP    | 共通         | トップページ (記事一覧) | `/`                            | サイトのトップページで記事一覧を表示する画面 |
| MVP    | サイト運営者 | 記事管理詳細画面   | `/admin/articles/{id}`         | 特定記事の詳細を表示し、管理する画面   |
| MVP    | サイト運営者 | 記事管理一覧画面   | `/admin/articles`              | 全記事の一覧を表示し、管理する画面     |
| MVP    | サイト運営者 | サイト運営者ログイン画面 | `/admin/login`                 | サイト運営者がログインする画面         |
| MVP    | サイト運営者 | 収益管理ダッシュボード画面 | `/admin/dashboard`             | サイト全体の収益状況を表示する画面     |
| MVP    | サイト運営者 | ユーザー管理一覧画面 | `/admin/users`                 | 登録ユーザーの一覧を表示し、管理する画面 |
| MVP    | 投稿ユーザー | ログイン画面       | `/login`                       | 登録済みユーザーがログインする画面     |
| MVP    | 投稿ユーザー | 記事投稿・編集画面 | `/user/articles/new` / `/user/articles/{id}/edit` | 記事の作成や編集を行う画面             |
| MVP    | 投稿ユーザー | 自身の記事一覧画面 | `/user/articles`               | 投稿した記事の一覧を表示する画面       |
| MVP    | 投稿ユーザー | 自身の記事詳細画面 | `/user/articles/{id}`          | 投稿した記事の詳細を表示する画面       |
| MVP    | 投稿ユーザー | ユーザー登録画面   | `/register`                    | 新規ユーザー登録を行う画面             |
| 高     | 共通         | お問い合わせフォーム画面 | `/contact`                     | サイト運営者へのお問い合わせを送信する画面 |
| 高     | サイト運営者 | サイト設定画面     | `/admin/settings`              | サイトの基本設定を行う画面             |
| 高     | サイト運営者 | 収益分配管理画面   | `/admin/payouts`               | 投稿ユーザーへの収益分配を管理する画面 |
| 高     | 投稿ユーザー | プロフィール編集画面 | `/user/profile/edit`           | プロフィール情報を編集する画面         |
| 高     | 投稿ユーザー | 投稿ユーザーダッシュボード画面 | `/user/dashboard`              | 自身の記事一覧、収益状況などを表示する画面 |
| 高     | 投稿ユーザー | 収益受け取り設定画面 | `/user/payout-settings`        | 収益の振込先口座情報を設定する画面     |
| 中     | サイト運営者 | お知らせ・アナウンス管理画面 | `/admin/announcements`         | お知らせの作成・管理を行う画面         |
| 中     | サイト運営者 | カテゴリ管理画面   | `/admin/categories`            | 記事カテゴリの追加・編集・削除を行う画面 |
| 中     | 投稿ユーザー | 記事プレビュー画面 | `/user/articles/{id}/preview`  | 記事の公開前に内容を確認する画面       |

## 7. 画面遷移図

```mermaid
graph TD
    %% 共通ユーザーフロー
    A["トップページ /"] --> B["記事詳細画面 /articles/{id}"]
    A --> C["検索結果画面 /search"]
    A --> D["ログイン画面 /login"]
    A --> E["ユーザー登録画面 /register"]
    B -- 有料記事の場合 --> F["記事購入画面 /articles/{id}/purchase"]
    G["お問い合わせフォーム画面 /contact"]

    %% 投稿ユーザーフロー
    D --> H["投稿ユーザーダッシュボード画面 /user/dashboard"]
    E --> H
    H --> I["自身の記事一覧画面 /user/articles"]
    H --> J["プロフィール編集画面 /user/profile/edit"]
    H --> K["収益受け取り設定画面 /user/payout-settings"]
    I --> L["記事投稿・編集画面 /user/articles/new / /user/articles/{id}/edit"]
    I --> M["自身の記事詳細画面 /user/articles/{id}"]
    L --> N["記事プレビュー画面 /user/articles/{id}/preview"]

    %% サイト運営者フロー
    O["サイト運営者ログイン画面 /admin/login"] --> P["収益管理ダッシュボード画面 /admin/dashboard"]
    P --> Q["ユーザー管理一覧画面 /admin/users"]
    P --> R["記事管理一覧画面 /admin/articles"]
    P --> S["収益分配管理画面 /admin/payouts"]
    P --> T["サイト設定画面 /admin/settings"]
    P --> U["カテゴリ管理画面 /admin/categories"]
    P --> V["お知らせ・アナウンス管理画面 /admin/announcements"]
    R --> W["記事管理詳細画面 /admin/articles/{id}"]

    %% 共通アクセス
    subgraph 共通アクセス
        A
        B
        C
        D
        E
        F
        G
    end

    subgraph 投稿ユーザー
        H
        I
        J
        K
        L
        M
        N
    end

    subgraph サイト運営者
        O
        P
        Q
        R
        S
        T
        U
        V
        W
    end
```
## 8. その他
- **開発言語**: フロントエンド: JavaScript/TypeScript, バックエンド: PHP
- **バージョン管理**: Git
- **CI/CD**: GitHub Actions / GitLab CI (検討)
- **テスト**: ユニットテスト、結合テスト、E2Eテスト。
- **開発環境**: Docker / Docker Compose を使用し、各サービスをコンテナ化。
- **本番環境**: AWS EC2 上で Docker コンテナをデプロイ.

### 9. 開発環境のバージョン
- **Node.js**: 20.x (LTS)
- **npm**: 10.x (Node.js 20.xに同梱)
- **React**: 18.x
- **TypeScript**: 5.x
- **Tailwind CSS**: 3.x
- **PHP**: 8.3.x
- **Laravel**: 11.x
- **MySQL**: 8.0.x
- **Docker / Docker Compose**: 最新の安定版

