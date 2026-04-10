# Flash Cards (Quiz Application)

## 概要 (Overview)
このプロジェクトは、ユーザーがオリジナルの問題を作成・解答・学習できる、LaravelベースのWebアプリケーションです。
これまでの開発で、問題の作成から解説の追加、学習履歴の記録まで、一連の学習サイクルをサポートする機能が実装されています。

## 主な機能 (Features)
- **クイズ作成・管理機能**
  - 問題文、選択肢、正解の設定
  - 解答に対する「解説フィールド」のサポート
  - クイズの物理削除機能、画像ファイルの安全な管理
- **タグ・カテゴリ機能**
  - クイズへのタグ付けによる整理
  - 重複タグの自動整理・統合機能
- **プレイ・履歴機能**
  - スコアの算出および解答履歴（未解答含む）の保存
  - 復習可能なレビュー画面
- **アクセス制御と公開設定**
  - クイズの公開/非公開設定
  - URL直接入力による非公開クイズへの不正アクセス防止
- **UI/UX機能**
  - クイズ一覧画面の無限スクロール
  - クイズ作成フォームでの不適切ワード検出

## 技術スタック (Tech Stack)
- **バックエンド**: PHP, Laravel ( `web/laravel/` 配下)
- **フロントエンド**: Blade Templates, JavaScript
- **データベース**: MySQL または SQLite (テスト用途)
- **インフラ環境**: Docker, docker-compose

## ディレクトリ構成 (Directory Structure)
```text
flash_cards/
├── docker/                 # Dockerファイル（Web, DB等の設定）
├── docker-compose.yml      # コンテナ構成管理
└── web/
    └── laravel/            # Laravelアプリケーションのルート
        ├── app/            # コントローラー、モデル（Eloqeunt）等
        ├── database/       # マイグレーション、シーダー等
        ├── resources/      # Bladeビュー、CSS、JSファイル
        ├── routes/         # ルーティング設定
        └── tests/          # PHPUnitおよびFeature/Unitテスト
```

## ローカル環境の構築手順 (Getting Started)

1. **Dockerコンテナの立ち上げ**
   ルートディレクトリで以下のコマンドを実行し、環境を起動します。
   ```bash
   docker-compose up -d
   ```

2. **アプリケーションディレクトリへの移動**
   Webコンテナ内に入り、Laravelのディレクトリへ移動します。
   ```bash
   docker-compose exec web bash
   cd web/laravel
   ```

3. **依存パッケージのインストール**
   ```bash
   composer install
   npm install
   npm run build # または npm run dev
   ```

4. **環境変数の準備とキーの生成**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **データベースのマイグレーション**
   ```bash
   php artisan migrate
   ```

6. **アプリケーションへのアクセス**
   ブラウザで設定されたポート（標準は `http://localhost:8080` 等）へアクセスし、動作を確認してください。

## テストの実行 (Running Tests)
継続的にPHPUnitを用いたテストが拡充されています。テストを実行する場合は以下のコマンドを利用します。
```bash
docker-compose exec web bash
cd web/laravel
php artisan test
```
