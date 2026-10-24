## What and why

<!-- The ticket in a sentence or two, and anything a reviewer should know first. -->

## How I tested it

<!-- Commands run, plus what to click if the change touches the UI. -->

## Review checklist

I read every line of this diff myself before merging, including the code Claude Code wrote.

- [ ] Company data is scoped to the signed-in user's company, and every new route has a cross-company 404 test
- [ ] Controllers call `Gate::authorize` before validating input or writing anything
- [ ] Secrets are encrypted at rest and never reach HTML, JSON, logs, the session or a URL
- [ ] Migrations are safe on tables that already hold rows, and `down()` works
- [ ] Every input is validated, and the failure paths have tests
- [ ] External HTTP calls are faked in tests
- [ ] No leftover debug code, dead code, or framework APIs I haven't checked against the docs or `vendor/`
- [ ] `php artisan test` and `vendor/bin/pint --test` pass locally, and CI is green
