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
- Never commit `.env`, local databases, secrets, vendor, or node_modules.
- The custom source for this section is `.ai/guidelines/tsumu.md`. Regenerate `AGENTS.md` with `php artisan boost:update --no-interaction` after editing it. Keep the source and generated guidelines in version control so future tasks read the same rules.
