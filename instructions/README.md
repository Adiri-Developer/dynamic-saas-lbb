# Multi-Tenant SaaS for Tutoring Centers (LBB)

A multi-tenant platform for many tutoring centers (Lembaga Bimbingan Belajar): public website, student registration, scheduling, attendance, billing/tuition, and student/parent/tutor portals. One app serves many centers, and each center's data stays isolated.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel |
| Portal frontend | Inertia.js + React/Vue |
| Admin panel | Filament (Livewire) |
| Database | PostgreSQL (Supabase, via pooler) |
| Multi-tenancy | Single database, `tenant_id` column + global scope |
| Billing | Midtrans / Xendit |
| Notifications | WhatsApp API / email |

## Architecture Principles

- **Multi-tenant from day one.** Every tenant-owned table has a `tenant_id`, filtered automatically by a global scope.
- **Filament** for the super admin panel (platform) and center admin panel. **Inertia** for the student/parent/tutor portals and interactive public pages.
- **Supabase is used as plain Postgres.** Auth and storage stay with Laravel. If you use the pooler in transaction mode, mind the prepared statements setting.
- **Every feature must ship with a tenant isolation test.**
- One feature per iteration: build, test, commit.

## Roles

| Role | Scope |
|---|---|
| Super admin | Platform level: manage all tenants and plans |
| Owner | Center owner, full access within the tenant |
| Admin | Day-to-day center operations |
| Tutor | Attendance, grades, materials |
| Student / Parent | View schedule, attendance, invoices, report cards |

---

## Roadmap

### Phase 0: Foundation

- [ ] Set up Laravel + Inertia + Supabase Postgres connection
- [ ] `tenants` table, user-tenant relation, `tenant_id` on all tenant tables
- [ ] Global scope + tenant resolution from subdomain
- [ ] Auth: login, register, password reset
- [ ] Roles and permissions per tenant
- [ ] Super admin panel: tenant list, activate/deactivate
- [ ] Automated tests: no data leaks between tenants

### Phase 1: MVP for pilot centers

- [ ] Center onboarding: sign up, create center, choose subdomain, fill profile and logo
- [ ] CRUD for students, tutors, programs, classes
- [ ] Class schedule (tutor, room, day/time)
- [ ] Online registration form + applicant list (new, contacted, enrolled)
- [ ] Public landing page per tenant: profile, programs, tutors, contact, WhatsApp button
- [ ] Basic branding: logo and colors

### Phase 2: Daily operations

- [ ] Student and tutor attendance per session
- [ ] Monthly invoices/tuition: paid/unpaid status, transfer proof upload
- [ ] Reports: active students, revenue, attendance
- [ ] WhatsApp/email notifications: schedule and invoice reminders
- [ ] Student import from Excel
- [ ] Tutor payroll from sessions taught

### Phase 3: Portals and monetization

- [ ] Student/parent portal: schedule, attendance, invoices, report cards
- [ ] Tutor portal: record attendance and grades, upload materials
- [ ] Subscription plans (Free/Basic/Pro) with limits on students/tutors/features
- [ ] Automated billing (Midtrans/Xendit)
- [ ] Payment gateway for online tuition payments
- [ ] Custom domain per tenant (paid plan)
- [ ] Selectable landing page templates

### Phase 4: Growth

- [ ] Blog/articles for SEO, testimonials, gallery
- [ ] Question bank / online tryouts
- [ ] Announcements or chat to parents
- [ ] PWA/mobile app for parents
- [ ] Audit log, backups, data export

---

## Workflow (Vibe Coding)

1. Keep the spec in `PRD.md` and project rules in `AGENTS.md`.
2. Build one feature per prompt: migration, model, then Filament resource or Inertia page.
3. Test (including tenant isolation), then commit to Git before moving on.
4. Never commit `.env`. Review security before release: password hashing, per-role authorization, input validation, CORS.
5. Validate with 2-3 pilot centers before building automated billing. Manual invoicing is fine at first.

## Local Setup

```bash
git clone <repo-url> && cd <folder>
composer install
npm install
cp .env.example .env
php artisan key:generate
# fill DB_* in .env with your Supabase Postgres credentials
php artisan migrate
npm run dev
php artisan serve
```

## License

Not decided yet.
