# CLAUDE.md

@AGENTS.md

All project rules live in `AGENTS.md` (imported above). This file only adds notes specific to Claude Code.

## Notes for Claude Code

- For large tasks, use **plan mode** first: write the plan (files to change, migrations, tests), wait for approval, then execute.
- Split work into small features. When done: run `php artisan test` and `./vendor/bin/pint`, then summarize the changes.
- Before changing the database schema, show the migration you intend to create.
- For context, read `README.md` (roadmap) and `DESIGN.md` (UI) first instead of exploring the whole repo.
- Do not create new documentation files unless asked.

## Suggested Prompt Patterns

- "Implement Phase X, feature Y from the README. Make a plan first."
- "Review this change specifically for cross-tenant data leaks."
- "Write tenant isolation tests for model Z."

## Done Checklist

- [ ] Model uses `BelongsToTenant`
- [ ] Policy and authorization in place
- [ ] Feature test + tenant isolation test written
- [ ] UI copy goes through Indonesian lang files
- [ ] No secrets committed
