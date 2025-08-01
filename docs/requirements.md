# 要件定義書

## 1. 概要
本プロジェクトは、有料コンテンツを提供するブログサイトを構築することを目的とする。投稿ユーザーは自身のブログコンテンツを有料で提供し、サイト運営者はその管理および収益化を行う。

## 2. 機能要件

### 2.1. 投稿ユーザー向け機能
- **ユーザー登録・ログイン機能**: メールアドレスとパスワードによる登録・ログイン。
- **プロフィール管理機能**: プロフィール情報（ユーザー名、自己紹介など）の編集。
- **記事投稿・編集機能**:
    - テキストのみを利用した記事作成。
    - 記事の公開・非公開設定。
    - 記事の有料・無料設定。
    - 記事のプレビュー機能。
- **ダッシュボード機能**: 自身の記事一覧、収益状況の確認。
- **収益受け取り設定機能**: 収益の振込先口座情報の設定。

### 2.2. サイト運営者向け機能
- **ユーザー管理機能**: 投稿ユーザーの一覧表示、情報編集、アカウント停止。
- **記事管理機能**: 全記事の一覧表示、内容確認、公開・非公開設定、削除。
- **収益管理機能**:
    - サイト全体の収益状況の確認。
    - 投稿ユーザーへの収益分配管理。
    - 決済履歴の確認。
- **サイト設定機能**: サイト名、ロゴ、利用規約、プライバシーポリシーなどの設定。
- **コンテンツカテゴリ管理機能**: 記事のカテゴリの追加・編集・削除。
- **お知らせ・アナウンス機能**: サイト全体へのお知らせ配信。

### 2.3. 共通機能
- **記事閲覧機能**:
    - 無料記事の閲覧。
    - 有料記事の購入・閲覧（決済連携）。
    - 記事検索、カテゴリ別表示。
- **コメント機能**: 記事へのコメント投稿、閲覧。
- **お問い合わせ機能**: サイト運営者へのお問い合わせフォーム。

## 3. 非機能要件
- **パフォーマンス**: 記事表示、検索などの主要機能は3秒以内に応答。
- **セキュリティ**:
    - ユーザー認証情報の安全な管理。
    - 決済情報の安全な処理。
    - SQLインジェクション、XSSなどの脆弱性対策。
- **可用性**: 稼働率99.9%以上。
- **拡張性**: 将来的な機能追加（例: サブスクリプションモデル、ライブ配信）に対応可能なアーキテクチャ。
- **保守性**: コードの可読性、テスト容易性の確保。
- **スケーラビリティ**: アクセス増加に対応できるシステム構成。
- **対応デバイス**: PCのみ対応。

## 4. 技術スタック
- **フロントエンド**: React.js (v18.x), TypeScript (v5.x), Tailwind CSS (v3.x)
- **バックエンド**: PHP (v8.3.x), Laravel (v11.x)
- **データベース**: MySQL (v8.0.x)
- **インフラ**: AWS, Docker

## 4. スコープ
- 投稿ユーザーによる記事の投稿・管理（文字のみ）、有料コンテンツの提供。
- サイト運営者によるユーザー・記事・収益の管理。
- 記事の閲覧、購入（モック決済）、コメント機能。
- 初期段階では、単発記事購入モデルを想定。

## 5. 成果物
- 要件定義書 (requirements.md)
- 技術設計書 (design.md)
- 開発タスク (tasks.md)
- フロントエンドアプリケーション
- バックエンドAPI
- データベーススキーマ
- デプロイ手順書

## 6. MVPと機能優先順位

### 6.1. MVP (Minimum Viable Product)
本プロジェクトのMVPは、以下の機能に絞り込み、早期のリリースと市場投入を目指す。

#### 6.1.1. 投稿ユーザー向けMVP機能
- **ユーザー登録・ログイン機能**: メールアドレスとパスワードによる登録・ログイン。
- **記事投稿・編集機能**:
    - テキストのみを利用した記事作成。
    - 記事の公開・非公開設定。
    - 記事の有料・無料設定。
- **自身の記事閲覧**: 投稿した記事の一覧表示と詳細閲覧。

#### 6.1.2. サイト運営者向けMVP機能
- **ユーザー管理機能**: 投稿ユーザーの一覧表示、アカウント停止。
- **記事管理機能**: 全記事の一覧表示、内容確認、公開・非公開設定、削除。
- **収益管理機能**: サイト全体の収益状況の確認、決済履歴の確認。

#### 6.1.3. 共通MVP機能
- **記事閲覧機能**:
    - 無料記事の閲覧。
    - 有料記事の購入・閲覧（モック決済）。
    - 記事検索。

### 6.2. 機能優先順位 (MVPリリース後)

#### 6.2.1. 高優先度 (Phase 2)
- **投稿ユーザー向け**:
    - プロフィール管理機能: プロフィール情報（ユーザー名、自己紹介など）の編集。
    - ダッシュボード機能: 自身の収益状況の確認。
    - 収益受け取り設定機能: 収益の振込先口座情報の設定。
- **サイト運営者向け**:
    - 投稿ユーザーへの収益分配管理。
    - サイト設定機能: サイト名、ロゴ、利用規約、プライバシーポリシーなどの設定。
- **共通**:
    - コメント機能: 記事へのコメント投稿、閲覧。
    - お問い合わせ機能: サイト運営者へのお問い合わせフォーム。

#### 6.2.2. 中優先度 (Phase 3)
- **サイト運営者向け**:
    - コンテンツカテゴリ管理機能: 記事のカテゴリの追加・編集・削除。
    - お知らせ・アナウンス機能: サイト全体へのお知らせ配信。
- **投稿ユーザー向け**:
    - 記事のプレビュー機能。

#### 6.2.3. 低優先度 (将来的な拡張)
- サブスクリプションモデルの導入。
- ライブ配信機能。
- 記事の動画コンテンツ対応。
- 高度な分析レポート機能。