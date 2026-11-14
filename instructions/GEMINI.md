# GEMINI.md

All project rules live in `AGENTS.md`. **Read that file first and follow it completely.**
This file only adds notes specific to Gemini CLI / Antigravity.

## Notes for Gemini / Antigravity

- Always start by reading `AGENTS.md`, `README.md` (roadmap), and `DESIGN.md` (UI).
- Write a short plan before changing many files, then work step by step, one small feature at a time.
- When running terminal commands, avoid destructive ones (`migrate:fresh`, `rm -rf`, `DROP`) against any non-local database.
- When writing migrations, always include `tenant_id` and the appropriate indexes for tenant-owned tables.
- When finished, run `php artisan test` and show the results.
- If you use a browser agent to check the UI, also test the mobile layout and color contrast per `DESIGN.md`.

## Additional Rules

- Do not modify core config files (`config/database.php`, the tenant middleware) without explaining why.
- Do not add dependencies without approval.
- If unsure about tutoring-center business rules (tuition, attendance, tutor pay), ask.
