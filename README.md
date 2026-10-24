# Company integrations

A small Laravel 13 app I built as a public code sample. Companies have users and integrations, and each integration holds a third-party API key. It covers what a multi-tenant app needs, in miniature: encrypted credentials, company-scoped queries and policies, a migration written for a table that already holds rows, htmx forms, and tests for all of it.

It was built the way I work tickets: Claude Code, a branch per change, and a pull request with a review checklist. The [pull requests](../../pulls?q=is%3Apr) are the best way to read it. Each one explains what it changes and why.

## What it shows

| Requirement | How |
|---|---|
| **Companies and users** | Every user belongs to one company. [`LoginController`](app/Http/Controllers/LoginController.php) is a plain session login, throttled to five attempts a minute. |
| **A third-party API key** | [`Integration`](app/Models/Integration.php) stores it with the `encrypted` cast and hides it with `#[Hidden]` (Laravel 13's attribute form of `$hidden`). Pages only show the last four. Saving makes one test call to the provider first ([`AcceptedByProvider`](app/Rules/AcceptedByProvider.php)), which the tests fake with `Http::fake`. |
| **Company scoping and policies** | [`BelongsToCompany`](app/Models/Concerns/BelongsToCompany.php) adds a global scope and fills in `company_id` on create. `Company` and `Integration` each have a policy that answers another company with a 404. The company and integration controllers call `Gate::authorize` before they validate anything, and the nested routes use `Route::scopeBindings()`. |
| **A migration for a table with rows** | [`add_api_key_last_four_to_integrations_table`](database/migrations/2026_10_24_143830_add_api_key_last_four_to_integrations_table.php) adds a nullable column and backfills it 100 rows at a time with `chunkById`. |
| **Blade, Alpine.js, htmx** | The [integration form](resources/views/integrations/form.blade.php) posts through htmx. A validation error returns just the form (a Blade `@fragment`) with a 422, which htmx swaps in, and success returns `HX-Redirect`. Alpine adds the key field only when you choose to replace a saved key. Tailwind is built by Vite. |
| **PHPUnit feature tests** | Class-based tests in [`tests/Feature`](tests/Feature): happy paths, validation, a cross-company 404 for every route, the key never in HTML or JSON, and the backfill. |
| **CI** | [GitHub Actions](.github/workflows/tests.yml) runs Pint, the Vite build and PHPUnit against MySQL 8.4 on every pull request. |

### Changing an ID in the URL

A request for another company's record meets three separate checks:

1. `{integration}` is resolved through `{company}`, so an integration from a different company is a 404.
2. The global scope hides other companies' integrations from every query, route binding included.
3. The policies answer another company with `denyAsNotFound()`, so the response never confirms the record exists. They run before validation, so an outsider can't trigger the provider call either.

[`CompanyIsolationTest`](tests/Feature/CompanyIsolationTest.php) sends every route as a guest and as a user from another company, puts another company's integration under your own company's URL, and checks the policies with the scope out of the way.

### Keeping the key secret

- Encrypted at rest, so a database dump only shows ciphertext.
- Left out of `toArray()` and `toJson()`.
- Never rendered: pages show `•••• 1234`, and the key field comes back empty after a failed save.
- Never flashed: `api_key` is in `dontFlash`, so a redirect back with input can't write it to the session.
- Sent to the provider in the `Authorization` header, not the query string.

## Run it

You need PHP 8.3+, Composer and Node 22+.

```bash
composer setup        # install, .env, app key, migrations, npm install, Vite build
php artisan db:seed   # two companies, three users, three integrations
php artisan serve
```

Log in as `ada@acme.test` with the password `password`. The other company's page is `/companies/2`, and it's a 404 for Ada.

Saving an integration checks the key against `ADS_API_URL`. To try the form without a real provider, run the bundled fake and set `ADS_API_URL=http://127.0.0.1:8099` in `.env`. It accepts any key that doesn't start with `bad`.

```bash
php -S 127.0.0.1:8099 scripts/fake-ads-api.php
```

Run the tests with `php artisan test`. They use in-memory SQLite by default. CI runs them against MySQL, and you can too by setting `DB_CONNECTION=mysql` and the other `DB_*` variables in your shell first.

## How the AI output was reviewed

Claude Code wrote the first draft of each branch from a ticket-sized brief and the rules in [CLAUDE.md](CLAUDE.md). I reviewed every line of each pull request before merging it, using the checklist in the [PR template](.github/pull_request_template.md). Passing tests are where the review starts. It also checks:

- **Scope.** Every query on company data is scoped, and every new route has a cross-company test.
- **Order.** Authorization comes before validation, so an outsider can't cause side effects.
- **Secrets.** Nothing renders, serializes, logs, flashes or URL-encodes a key.
- **Live data.** Migrations work on tables that already hold rows, and `down()` works.
- **APIs.** Anything unfamiliar is checked against `vendor/` or the docs, because AI drafts sometimes call methods that don't exist.
- **Tests that can fail.** Each test should break when the code is wrong. Swapping `chunkById()` for `chunk()` makes the backfill test fail, which is how that test was checked.

Problems the process caught while building, before review:

- The provider tests matched `ADS_API_URL` from `.env`, so they passed in CI but failed on a machine pointed at the local fake. `phpunit.xml` now pins it.
- The seeder used `WithoutModelEvents`, which would have skipped the hook that sets the last four, leaving seeded rows with `••••` and nothing after it.
- After a rejected replacement key, Cancel hid the key field but left its error on screen. It was found by clicking through in a browser, and the error now sits inside the Alpine template with the field.
- Following this README in a fresh clone left `package-lock.json` modified, because npm names the lockfile after the folder when `package.json` has no name. It has one now.

## Left out on purpose

- Deleting integrations, roles within a company, and OAuth2 integrations with token refresh.
- On a very large table I'd run the backfill as a queued job instead of inside the deploy, then make the column `NOT NULL` in a later migration.
- Rotating `APP_KEY`: Laravel also decrypts with the keys in `APP_PREVIOUS_KEYS`, so a rotation doesn't break saved API keys.
