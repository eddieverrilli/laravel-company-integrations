# Working in this repo

A small Laravel 13 app: companies, their users, and per-company integrations that hold a third-party API key. Blade with Alpine.js and htmx, Tailwind via Vite, and PHPUnit feature tests. CI runs the suite against MySQL on every pull request.

## How changes land

- One branch per change, merged through a pull request with the template's checklist filled in.
- Keep each pull request small enough to review line by line.
- Before opening one, run `php artisan test` and `vendor/bin/pint --test`.

## Rules

- **Company isolation.** Anything a company owns is scoped to the signed-in user's company. Controllers call `Gate::authorize` before they validate input or write. Another company's record is a 404, never a 403, so its existence doesn't leak. Every route that takes a model gets a cross-company test.
- **Secrets.** Third-party credentials use the `encrypted` cast and are hidden from serialization. Never render them back into a page, return them in JSON, log them, flash them to the session or put them in a URL.
- **External calls.** Use Laravel's `Http` client with a timeout. Tests fake every call, and a stray request fails the test.
- **Migrations.** Assume the table already holds rows: add columns as nullable or with a default, backfill in chunks with `chunkById`, use the query builder rather than models, and write a `down()` that works.
- **Tests.** Class-based PHPUnit feature tests covering the happy path, validation failures and the cross-company case.
- **Framework APIs.** Check anything you're unsure of against `vendor/laravel/framework` before using it.
