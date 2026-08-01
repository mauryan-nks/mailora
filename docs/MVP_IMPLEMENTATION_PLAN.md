# Mailora Revised MVP — Implementation Plan

This document translates the Revised Detailed MVP Specification v2 into the implementation baseline for this repository. The attached specification is the source of truth whenever earlier prototype behavior conflicts with it.

## Non-negotiable architecture

- One `users` table for platform administrators, platform team, reseller owners, reseller team, customer owners, and customer team members.
- Exact account levels: `1 platform_admin`, `2 platform_team`, `3 reseller`, `4 reseller_team`, `5 customer`.
- A workspace represents one customer company. Customer team members share that workspace.
- Every customer-owned row carries `workspace_id`; every reseller-owned row carries `reseller_id`.
- Tenant IDs are derived from the authenticated user and resolved tenant context, never trusted from a form or API request.
- Roles are presets. Effective authorization also applies individual allow/deny/inherit overrides, assigned workspaces, and resource restrictions. Explicit deny wins.
- Reseller branding is resolved from a verified request hostname. Unknown domains never disclose reseller data.
- Public identifiers use UUIDs. Numeric IDs remain internal only.
- Imports, campaign preparation/sending, automations, reports, URL imports, webhooks, domain checks, and aggregation use durable queues.
- SMTP credentials, API secrets, signing keys, and 2FA secrets are encrypted at rest.
- Administrative and sensitive operations write immutable audit records without secrets.

## Required correction to the current scaffold

The current implementation uses CodeIgniter Shield. Shield keeps credentials in `auth_identities`, which conflicts with the explicit single-table authentication requirement. Before feature development continues, authentication must be moved to an application-owned session authenticator backed only by `users.email` and `users.password_hash`.

The replacement must retain:

- Argon2id/bcrypt hashing through PHP password APIs.
- Session ID regeneration after authentication.
- CSRF, secure/HTTP-only/SameSite cookies, throttling, failed-login audit events, remember-token rotation, email verification hooks, password reset hooks, and session revocation.
- Existing account data migration from Shield identities into `users` before Shield tables are retired.

No Shield table should be dropped until migration verification, login tests, rollback preparation, and a database backup are complete.

## Current implementation status

| Area | Current state | Revised MVP gap |
|---|---|---|
| Authentication | Shield login/register/logout | Must consolidate credentials into `users`; add verification, reset, 2FA hooks, throttling, revocation |
| Account hierarchy | Five labels and parent/reseller columns | Rename `admin_team` to `platform_team`; add exact levels, UUID, workspace, role, permissions, status/security fields |
| Workspaces | Basic workspace and membership | Add owner/reseller, UUID, plan, limits, usage, approval, status, trial and assignment enforcement |
| White label | Basic colors/logo/favicon/domain resolution | Add full branding fields, primary domain, SSL states, CNAME verification jobs, reports/forms/pages branding |
| Permissions | Group presets only | Add tri-state individual permissions, overrides, role presets, workspace assignments, history and enforcement |
| Contacts | Basic CRUD/CSV/deduplication | Expand fields, consent, suppression, lists, custom fields, merge rules, activity, Excel and queued imports |
| Campaigns | Basic draft/editor/schedule form | Add audience snapshots, approval, sender verification, UTC scheduling, validation and queue pipeline |
| Templates | Starter records only | Add ownership levels, 48 templates, folders/tags, versions, HTML/ZIP/paste/URL import and sanitation |
| SMTP | Schema/provider cards | Add encrypted credentials, ownership priority, limits, tests, sender identities and provider adapters |
| Analytics | Event counters | Add recipients, unique/total events, tracking, webhooks, timelines, device/location/provider metrics |
| Automation | Starter records | Add triggers, actions, waits, conditions, runs, queue processing and reports |
| Forms/pages | Starter cards | Add builders, publication endpoints, anti-abuse, submissions, conversion analytics and branding |
| Reports/API/webhooks | UI placeholders | Add queued exports, scoped API keys, signatures, retryable outbound webhooks and delivery logs |
| Audit/notifications | Missing | Add append-only audit log and user notification center/preferences |

## Target backend modules

`Auth`, `Users`, `Roles`, `Permissions`, `Teams`, `Resellers`, `Workspaces`, `TenantResolver`, `Branding`, `Domains`, `Contacts`, `Lists`, `Tags`, `Segments`, `CustomFields`, `Imports`, `Exports`, `Campaigns`, `CampaignApprovals`, `EmailBuilder`, `HtmlEditor`, `TemplateImporter`, `Templates`, `TemplateVersions`, `Assets`, `Scheduling`, `Sending`, `Queue`, `SMTP`, `SenderIdentities`, `Automations`, `Forms`, `LandingPages`, `Analytics`, `Tracking`, `Webhooks`, `Reports`, `Notifications`, `ApiKeys`, `Usage`, `Plans`, `AuditLogs`, `Settings`.

Controllers will validate request shape and delegate to services. Tenant, permission, limit, audit, and transaction rules belong in services and filters rather than views or controllers.

## Delivery sequence

### Phase 1 — Correct foundation

1. Back up and inspect the existing remote schema.
2. Add exact user/workspace UUID, hierarchy, security, limit, and status fields.
3. Migrate Shield identities into the single `users` table.
4. Implement the application-owned authenticator and remove Shield route dependence.
5. Implement `TenantContext`, `TenantResolverService`, `WorkspaceAccessService`, and mandatory request filters.
6. Add role presets, tri-state permission definitions, individual overrides, workspace assignments, and `PermissionService`.
7. Add audit logs and instrument authentication, hierarchy, branding, permission, and impersonation actions.
8. Complete reseller branding/domain states and asynchronous SSL lifecycle hooks.
9. Add tenant-isolation and direct-deny acceptance tests.

### Phase 2 — Contact domain

Expand contacts and implement lists, tags, custom fields, segments, consent, suppression, activity, notes, duplicate merging, import jobs, row errors, mapping, CSV/XLS/XLSX, progress, and queued exports. Google Places fields and disabled integration placeholders are included; discovery remains off.

### Phase 3 — Email studio

Implement builder JSON and rendering, advanced code/rich-text modes, assets, revisions, 48 templates, HTML/ZIP/paste import, SSRF-safe URL importing, HTML/CSS sanitation, CSS inlining, JavaScript removal, link checking, plain-text generation, spam-risk heuristics, compatibility warnings, and previews.

### Phase 4 — Campaigns and approvals

Implement the nine-step campaign flow, audience/exclusion snapshots, personalization, approval history/comments, immutable approval hashes, reapproval after material edits, permission gates, UTC schedules, calendar, tests, and preflight checks.

### Phase 5 — SMTP and sending

Implement encrypted provider accounts, sender verification, ownership fallback, quotas, idempotent recipients/jobs, batching, locks, provider rate limits, retries, pause/resume, dead letters, and scheduled dispatch commands.

### Phase 6 — Tracking and analytics

Implement signed open/click/unsubscribe routes, provider webhook verification/idempotency, hard-bounce/complaint suppression, recipient activity, aggregate metrics, devices, browsers, operating systems, geography, links, lists, segments, and provider performance.

### Phase 7 — Automation

Implement MVP triggers/actions, waits, date/birthday timezone handling, open/click conditions, execution state, idempotency, retries, pause/resume, and analytics.

### Phase 8 — Forms and landing pages

Implement embedded/popup/inline/hosted forms, double opt-in, CAPTCHA/honeypot/rate limits, publication, submissions, conversion events, landing-page blocks, SEO, scripts permission, branding and analytics.

### Phase 9 — Platform operations

Implement queued PDF/CSV/XLSX reports, scoped/rotatable API keys, signed outbound webhooks, notification preferences, usage and plans, reseller quotas, queue monitoring, failed jobs, cleanup and operational commands.

## Definition of done for each module

- Schema migration and rollback are reviewed.
- Service layer owns business rules and transactions.
- Tenant context is mandatory and server-derived.
- Permission and usage-limit checks run before mutation.
- UUIDs are used externally.
- Sensitive changes are audited.
- Secrets and personal data are not written to logs.
- Validation, authorization, tenant-isolation and main success/failure paths have automated tests.
- UI works on desktop, tablet, and mobile.
- Background work is idempotent and retry-safe.

## Immediate next milestone

The next code milestone is **Phase 1A: single-table authentication and exact tenant hierarchy**. Feature work on contacts, campaigns, templates, and sending should pause until this correction is complete, because those modules depend on trustworthy tenant and permission context.
