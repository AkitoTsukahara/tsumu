# Tsumu

**前回の自分を基準に、短いトレーニングを積み重ねる。**

個人向けのジムトレーニング記録Webアプリ。現在は初期導入まで完了しており、ログイン画面やトレーニング機能はまだありません。

## ドキュメント

- [プロダクトコンセプト](docs/product-concept.md)：解決する課題、MVP全体、対象外の機能
- [アーキテクチャ](docs/architecture.md)：app / domain / infra、Livewire、Command / Query
- [実装ロードマップ](docs/roadmap.md)：フェーズ別の小さなStepと完了条件
- [テスト方針](docs/testing.md)：Unit / Feature / DbIntegrationとBrowserテスト
- [Codex向けルール](AGENTS.md)：実装前に読む文書とLaravel Boostのガイドライン

初期導入PRは行数制限の対象外。その後は原則1 Stepにつき1 PR、テストを除く追加＋削除で400行程度以内とします。

## 初期導入済み

- PHP 8.5 / Laravel 13
- Blade / Livewire 4 / Tailwind CSS 4
- Pest 5：Unit・Feature・DbIntegration・Browser
- Laravel Pint
- Laravel Boost 2：CodexのMCP設定、ガイドライン、開発スキル

正確なバージョンは`composer.lock`と`package-lock.json`で固定しています。ブラウザテストにはPest Browser＋Playwright（Chromium）を使用します。

## Dockerでのローカルセットアップ

Docker Desktopを起動し、リポジトリのルートで次を実行します。ローカルのPHP、Composer、Node.jsは不要です。

```sh
make setup
make user
```

`make setup`は、初回にSailを含む依存関係、`.env`、SQLiteを用意します。その後、Dockerの起動、migration、PlaywrightのChromium、フロントのビルドまで実行します。既存の`.env`やDBは上書きしません。

`make user`ではログインに使う名前、メールアドレス、パスワードを対話形式で入力します。セットアップ後は[http://localhost:8000/login](http://localhost:8000/login)を開いてください。

## 日常的に使うコマンド

利用できるコマンドと説明は次のコマンドでも確認できます。

```sh
make
```

| 操作 | コマンド |
| --- | --- |
| Dockerを起動 | `make up` |
| Dockerを停止 | `make stop` |
| Dockerを停止してコンテナを削除 | `make down` |
| Dockerを再起動 | `make restart` |
| コンテナのシェルへ接続 | `make shell` |
| rootでコンテナへ接続 | `make root-shell` |
| ログインユーザーを作成 | `make user` |
| migrationを実行 | `make migrate` |
| アプリのログを表示 | `make logs` |
| フロントの開発サーバーを起動 | `make dev` |
| Laravel Boostの生成物を更新 | `make boost-update` |

## 検証

| 検証 | コマンド |
| --- | --- |
| 全テスト | `make test` |
| Unitテスト | `make test-unit` |
| Featureテスト | `make test-feature` |
| DbIntegrationテスト | `make test-db` |
| Browserテスト | `make test-browser` |
| PHPコードの整形 | `make pint` |
| フロントのビルド | `make build` |

`make test`にはBrowserも含まれます。ブラウザのインストールとビルドが必要です。Linuxの追加手順・ブラウザなしでの実行方法は[テスト方針](docs/testing.md#ブラウザテストの実行)を参照してください。

現在のテストは基盤のスモークテストです。Domainの業務ルールやUser境界の振る舞いは、該当機能の実装と一緒に追加します。DbIntegrationは現時点ではインメモリSQLiteで実行し、本番DBが決まり次第そのDBでの検証も追加します。

## Laravel BoostとCodex

プロジェクト用の`.codex/config.toml`に`php artisan boost:mcp`を登録しています。個人のグローバル設定は変更しません。

- `AGENTS.md`にプロダクト・設計・ロードマップ・テスト方針の参照を含めています。
- プロジェクト固有のルール原本は`.ai/guidelines/tsumu.md`です。生成された部分だけを編集せず、原本を更新してください。
- Boostのスキルは`.agents/skills`にあります。
- 設定後、このタスクにMCPツールが自動追加されるとは限りません。利用中のCodexで`laravel-boost`が見えない場合は、プロジェクトを開き直すか新しいタスクで接続を確認してください。

パッケージ構成やプロジェクトルールを変更した後は、以下で生成物を更新し、差分を確認します。

```sh
make boost-update
```

`AGENTS.md`、`boost.json`、`.ai/guidelines`、`.agents/skills`、`.codex/config.toml`はリポジトリで共有します。Boostは開発依存です。本番では`composer install --no-dev`を使い、MCPサーバーを公開しません。

## デプロイ方針

Laravel Cloudで月額約US$5を目標に、休止を活用して本番1環境から始めます。リソースの作成・デプロイは未実施です。詳しくは[アーキテクチャ](docs/architecture.md)と[Phase 4](docs/roadmap.md#phase-4前回の自分を見て最初のリリースへ)を参照してください。
