# DESIGN.md

UI/UX guidelines for the tutoring center SaaS. All agents must follow these when building interfaces.

## Principles

1. **Clear and fast to use.** Main users are center admins and parents who are not technical.
2. **Mobile-first.** Many parents and tutors use phones. Every page must work well at 360px width.
3. **Friendly and trustworthy.** Education feel: warm, clean, not busy.
4. **Indonesian UI copy.** Short, polite, not overly formal.
5. **Consistent.** Reuse the same components. Don't invent new variants without a reason.

## UI Technology Split

| Area | Technology |
|---|---|
| Super admin and center admin panels | Filament (keep the default theme, apply tenant color theme) |
| Student/parent/tutor portals | Inertia + React/Vue + Tailwind |
| Public tenant landing pages | Inertia/Blade + Tailwind (SEO-friendly) |

## Design Tokens

Themes use CSS variables so each tenant can override branding.

```css
:root {
  --color-primary: #2563eb;        /* default, tenant can override */
  --color-primary-foreground: #ffffff;
  --color-accent: #f59e0b;
  --color-success: #16a34a;
  --color-warning: #d97706;
  --color-danger: #dc2626;
  --color-bg: #ffffff;
  --color-surface: #f8fafc;
  --color-text: #0f172a;
  --color-text-muted: #64748b;
  --color-border: #e2e8f0;
  --radius: 0.75rem;
}
```

- A tenant's primary color must pass WCAG AA contrast against the text on top of it. If it doesn't, automatically pick a suitable dark or light foreground.
- Support **dark mode** via `prefers-color-scheme` for portals. Tenant landing pages may stay light only.

## Typography

- Font: **Inter** for UI and body, optionally **Plus Jakarta Sans** for headings.
- Scale: 12 / 14 / 16 (body) / 20 / 24 / 32 / 40 px.
- Line height 1.5 for body text. Body text at least 16px on mobile.
- Always provide a `system-ui, sans-serif` fallback.

## Spacing and Layout

- Spacing on a 4px grid (Tailwind default).
- Max content width: 1200px (landing), 1440px (dashboard).
- Minimum touch target 44x44px.
- Tables on mobile: turn into cards or scroll horizontally inside their own container.

## Core Components

- **Buttons:** primary (filled), secondary (outline), danger (red). One primary button per area.
- **Forms:** label above input, clear error message below the input in Indonesian, submit button shows a loading state.
- **Status badges:** consistent colors.
  - Applicants: new (blue), contacted (yellow), enrolled (green), cancelled (gray)
  - Invoices: unpaid (yellow), paid (green), overdue (red)
- **Cards:** `--radius` corners, thin border, soft shadow.
- **Data tables:** search, filters, pagination, and an empty state that explains the next step.
- **Notifications:** toasts for success/failure. Confirmation dialogs for destructive actions.

## Tenant Landing Page

Standard section order:
1. Hero (center name, tagline, "Register Now" button + WhatsApp)
2. Programs/classes and pricing
3. Why choose us
4. Tutors
5. Testimonials
6. Gallery
7. Registration form
8. Contact, map, opening hours

Tenants can set: logo, primary color, name, hero text, and section order/visibility. The footer shows "Powered by [Platform Name]" (removable on the Pro plan).

## Accessibility

- WCAG AA contrast minimum.
- Every input has a label. Every image has `alt` text.
- Fully keyboard operable with a visible focus state.
- Never rely on color alone for status (add text or an icon).

## Performance

- Compress images and use `loading="lazy"`. Prefer WebP.
- Public landing pages: target Lighthouse Performance and SEO scores above 90.
- SEO meta tags, Open Graph, and a `sitemap.xml` per tenant.

## Do and Don't

**Do**
- Use enough whitespace and a clear visual hierarchy.
- Give feedback for every action (loading, success, error).
- Show helpful empty states.

**Don't**
- Don't use more than 2 accent colors on one page.
- Don't hide important actions in deep menus.
- Don't use technical jargon in UI aimed at parents and students.
