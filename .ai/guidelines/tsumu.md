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
- Use Pest Unit (no framework or DB), Feature (HTTP/Livewire/application behavior), and DbIntegration (real persistence and queries). Browser testing is proposed in `docs/testing.md`; do not silently choose or install a browser runner until that choice is settled.

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
