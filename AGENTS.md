<laravel-boost-guidelines>
=== .ai/tsumu rules ===

# Tsumu project rules

## Read before implementation

Read these repository documents before planning or changing implementation:

1. `docs/product-concept.md` — product vision, complete MVP, exclusions.
2. `docs/architecture.md` — agreed architecture and technology constraints.
3. `docs/roadmap.md` — first release, current stage, open decisions.
4. `docs/testing.md` — Unit, Feature, DbIntegration, and browser test strategy.

The complete MVP is not authorization to build every feature in a single task. Work in small usable increments. Keep unresolved product rules explicit; ask when a decision is needed for the current increment. Update the relevant document when the user changes an agreed decision.

## Review size

- Implement one roadmap step per review unit. Target 100–250 changed lines; keep handwritten implementation, configuration, and documentation additions plus deletions at about 400 lines maximum. Tests may be excluded, but report their size separately.
- Split a step before it exceeds the limit; do not build many steps first and merely divide the commits afterward. Do not combine unrelated refactoring.
- The user explicitly exempts the initial foundation PR (Laravel, Livewire, Pest, Boost, and project documentation) from the line limit. Report generated scaffolding, dependency locks, and generated Boost files separately. This exemption does not apply to subsequent PRs; identify any unavoidable generated-file overage before proceeding.
- Use Pest Unit (no framework or DB), Feature (HTTP/Livewire/application behavior), and DbIntegration (real persistence and queries). Use Pest Browser + Playwright for browser tests in `tests/Browser`, with Chromium as the default. Follow `docs/testing.md` for setup and coverage.

## Step-based pull requests

- `main` is the integration and default branch. Fetch origin and create each new Step branch from the latest `origin/main`; target every PR at `main` (use `gh pr create --base main`). Deliver changes through reviewed PRs rather than pushing directly to `main`. Merge only when the user requests or authorizes it.
- Use exactly one roadmap Step per PR. Before implementation, select a Step and its acceptance criteria; add a Step to `docs/roadmap.md` first if the work is not represented there.
- Name branches `codex/step-<ID>-<slug>` and PRs `[Step <ID>] <purpose>`. Include the Step ID and roadmap phase link in the PR body. If a Step is too large, define child Steps with their own acceptance criteria before splitting the work.
- Fill `.github/pull_request_template.md` with the user story, acceptance criteria, implementation, automated test perspectives/results, and reproducible manual verification steps/results. Use acceptance IDs such as AC1 to connect criteria and verification. Small changes need only concise entries; explain when a section is not applicable.
- Distinguish checks actually run from suggested or unexecuted checks. Include commands and outcomes; never mark acceptance criteria complete solely because code was written. Infrastructure stories may use a developer or reviewer as the actor.
- Measure additions plus deletions over the entire PR against its merge base, not only the latest commit. Report application/config/docs, tests, and generated/lock changes separately.
- After creating a PR, update the Step-to-PR table in `docs/roadmap.md`. A pushed branch or opened PR is not a completed Step; completion requires satisfied criteria, review, and merge. Do not begin the next Step before merge unless the user instructs otherwise.

## Agreed architecture

- Use Laravel with Blade + Livewire and Pest. Target Android Chrome and desktop Chrome, online only.
- `app/Service/Command` owns write use cases; `app/Service/Query` owns read use cases and read contracts/DTOs. Use one database; no command bus or event sourcing initially.
- Root `domain/` (`Domain\`) contains framework-independent business rules and repository contracts.
- Root `infra/` (`Infra\`) contains persistence/external implementations. Eloquent models belong in `infra/Persistence/Eloquent/Models`.
- `app/Http`, `app/Livewire`, and `resources/views` are the presentation boundary. They delegate to services; do not put business rules or database queries there.
- `app/Providers`, Laravel configuration, factories, and seeders may reference Infra to wire the application. Domain must not depend on Laravel, Livewire, App, or Infra.
- Do not add `app/App`, a separate Application layer directory, or a Presentation directory. Use Laravel's singular `database/`.
- These explicit project decisions take precedence over generic generated folder examples. Follow Laravel conventions within these boundaries; inspect installed APIs with Boost.
- Preserve the User ownership boundary for both reads and writes from the first feature. Never trust a client-supplied owner ID.
- Save completed sets individually; support resuming a workout. Do not add offline sync or polling by default.
- Laravel Cloud budget target is about US$5/month, with cold starts acceptable. Do not provision optional always-on services without a demonstrated need.

## Tooling and checks

- Use Laravel Boost's version-aware docs and the relevant generated skills before changing framework-dependent code. If the MCP is unavailable in the current session, say so and inspect installed sources or official docs; do not claim a tool ran when it did not.
- Run relevant Pest tests and Pint yourself. Do not ask the user to perform routine checks that you can run. The initial full suite is small enough to run with `composer test`.
- Keep `.github/select-tests.php` updated in the same PR when application files or tests are added. Map each feature or API to its specific tests; reserve whole-suite mappings for shared infrastructure. An unmapped application change must fail selection rather than silently skipping tests.
- Never commit `.env`, local databases, secrets, vendor, or node_modules.
- The custom source for this section is `.ai/guidelines/tsumu.md`. Regenerate `AGENTS.md` with `php artisan boost:update --no-interaction` after editing it. Keep the source and generated guidelines in version control so future tasks read the same rules.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

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
