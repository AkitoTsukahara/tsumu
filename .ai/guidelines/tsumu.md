# Tsumuプロジェクトルール

## 実装前に読む文書

実装の計画や変更を始める前に、以下のリポジトリ内文書を読むこと。

1. `docs/product-concept.md` — プロダクトの構想、MVP全体、対象外の機能。
2. `docs/architecture.md` — 合意済みのアーキテクチャと技術的な制約。
3. `docs/roadmap.md` — 最初のリリース、現在の段階、未決定事項。
4. `docs/testing.md` — Unit、Feature、DbIntegration、Browserのテスト方針。

MVP全体の記載は、すべての機能を一度に実装してよいという意味ではない。小さく利用可能な単位で進める。未決定のプロダクトルールは明示し、現在の変更に判断が必要ならユーザーへ確認する。合意事項が変わった場合は、関連する文書を更新する。

## レビューする差分の大きさ

- 1回のレビュー単位につき、ロードマップのStepを一つ実装する。変更は100〜250行を目安とし、手書きの実装・設定・文書の追加と削除を合計約400行以内に収める。テストは除外してよいが、行数を別に報告する。
- 上限を超える前にStepを分割する。複数のStepを先に実装して、後からコミットだけを分割しない。無関係なリファクタリングを混ぜない。
- Laravel、Livewire、Pest、Boost、プロジェクト文書を含む初期基盤PRは、ユーザーの明示的な指示により行数制限の対象外。生成されたひな形、依存関係のlockファイル、Boostの生成ファイルは別に報告する。この例外は以降のPRには適用しない。生成ファイルによりやむを得ず上限を超える場合は、作業前に明示する。
- PestのUnit（フレームワークやDBを使わない）、Feature（HTTP・Livewire・アプリケーションの振る舞い）、DbIntegration（実際の永続化とクエリ）を使う。`tests/Browser`のブラウザテストにはPest Browser＋Playwrightを使い、Chromiumを既定とする。設定と対象範囲は`docs/testing.md`に従う。

## Step単位のPR

- `main`を統合先および既定ブランチとする。`git fetch origin`の後、最新の`origin/main`から各Stepの作業ブランチを作り、すべてのPRを`main`へ向ける（`gh pr create --base main`を使う）。`main`へ直接Pushせず、レビュー可能なPRとして変更を提出する。ユーザーから指示または許可を受けるまでマージしない。
- 1 PRにつきロードマップのStepを一つだけ扱う。実装前にStepと完了条件を選ぶ。該当するStepがない作業は、先に`docs/roadmap.md`へ追加する。
- ブランチ名は`codex/step-<ID>-<概要>`、PRタイトルは`[Step <ID>] <目的>`とする。PR本文にStep IDとロードマップの該当Phaseへのリンクを記載する。Stepが大きすぎる場合は、実装前にそれぞれの完了条件を持つ子Stepへ分割する。
- `.github/pull_request_template.md`に、ユーザーストーリー、完了条件、実装内容、自動テストの観点と結果、再現可能な手動確認の手順と結果を記載する。AC1などのIDで完了条件と検証を対応づける。小さな変更では簡潔でよく、対象外の項目には理由を書く。
- 実際に実行した確認と、提案または未実行の確認を区別する。コマンドと結果を記載し、コードを書いただけで完了条件を満たしたことにしない。基盤整備では、開発者やレビュアーをユーザーストーリーの主体にしてよい。
- 最新コミットだけではなく、マージベースからPR全体の追加行と削除行を測る。アプリケーション・設定・文書、テスト、生成物・lockファイルを分けて報告する。
- PR作成後に`docs/roadmap.md`のStep・PR対応表を更新する。ブランチのPushやPR作成だけではStep完了としない。完了条件を満たし、レビュー後にマージされて完了となる。ユーザーの指示がない限り、マージ前に次のStepを始めない。

## 合意済みのアーキテクチャ

- Laravel、Blade＋Livewire、Pestを使う。Android ChromeとPC Chromeを対象とし、オンライン利用のみとする。
- `app/Service/Command`が更新ユースケース、`app/Service/Query`が参照ユースケースと参照用の契約・DTOを担当する。DBは一つとし、初期段階ではCommand Busやイベントソーシングを導入しない。
- ルートの`domain/`（`Domain\`）に、フレームワークから独立した業務ルールとRepository契約を置く。
- ルートの`infra/`（`Infra\`）に、永続化と外部サービスの実装を置く。Eloquentモデルは`infra/Persistence/Eloquent/Models`に配置する。
- Repositoryの実装は、Eloquent、Query Builder、生SQLのいずれを使う場合も`infra/Persistence/Repositories`に配置する。Domainの契約とInfraの実装はいずれも`UserRepository`のように命名し、名前空間と`UserRepositoryContract`などのimport aliasで区別する。複数の実装を実際に区別する必要がない限り、`Eloquent`接頭辞を付けない。永続化固有の型を契約へ漏らさない。理由は`docs/architecture.md`を参照する。
- 責務、命名、依存関係の境界に影響する有意な設計案が複数ある場合は、長所と短所を説明し、新しい規約を採用する前にユーザーと相談する。提案と合意済みの決定を区別する。CLI入力のバリデーション配置は引き続き検討中。
- `app/Http`、`app/Livewire`、`resources/views`をプレゼンテーション境界とする。これらはServiceへ処理を委譲し、業務ルールやDBクエリを置かない。
- `app/Providers`、Laravelの設定、factory、seederは、結線のためにInfraを参照できる。DomainはLaravel、Livewire、App、Infraに依存してはならない。
- `app/App`、独立したApplicationレイヤー、Presentationディレクトリは追加しない。Laravel標準の単数形`database/`を使う。
- ここに明記したプロジェクトの決定は、一般的な生成例より優先する。この境界の中ではLaravelの規約に従い、インストール済みAPIをBoostで確認する。
- 読み取りと書き込みの両方で、最初の機能からUser所有者境界を守る。クライアントから渡された所有者IDを信用しない。
- User、Equipment、Exercise、WorkoutなどDomainデータの主キーと外部キーにはUUIDv7を使う。Laravel内部のjobs、cache、migration管理などは標準の識別子を維持し、内部連番IDと公開UUIDを二重に持たない。UUIDを認可の代替にしない。Domain境界を越えるIDは必要になったものから専用の値オブジェクトにし、UUIDv7の検証・正規化は`Domain\Shared\UuidV7Id`基底クラスへ集約する。
- 完了したセットは個別に保存し、トレーニングを再開できるようにする。既定ではオフライン同期やポーリングを追加しない。
- Laravel Cloudの予算目標は月額約US$5で、コールドスタートは許容する。必要性が確認できるまで、常時稼働する任意リソースを用意しない。

## ツールと確認

- フレームワーク依存のコードを変更する前に、Laravel Boostのバージョン対応ドキュメントと関連する生成済みスキルを使う。現在のセッションでMCPが利用できない場合はその旨を伝え、インストール済みソースまたは公式ドキュメントを確認する。実行していないツールを実行したと報告しない。
- 関連するPestテストとPintを自分で実行する。ユーザーに通常の確認作業を依頼しない。初期段階の全テストは小規模なため、`composer test`で実行できる。
- アプリケーションファイルやテストを追加した場合は、同じPRで`.github/select-tests.php`も更新する。各機能やAPIを固有のテストへ対応づけ、全suiteの指定は共通基盤に限る。対応表にないアプリケーション変更は、テストを黙って省略せず選択処理を失敗させる。
- `.env`、ローカルDB、秘密情報、`vendor`、`node_modules`をコミットしない。
- この節のプロジェクト固有ルールの原本は`.ai/guidelines/tsumu.md`。編集後は`php artisan boost:update --no-interaction`で`AGENTS.md`を再生成する。原本と生成済みガイドラインの両方をバージョン管理し、将来のタスクでも同じルールを参照できるようにする。
