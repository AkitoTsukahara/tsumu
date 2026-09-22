<laravel-boost-guidelines>
=== .ai/tsumu rules ===

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

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
