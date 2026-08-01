# Mailora MVP

Email marketing and agency workspace MVP built on the CodeIgniter 4.5 application starter. Composer uses the patched CodeIgniter 4.7 runtime because framework 4.5.8 is blocked by current security advisories.

## Local setup

1. Create a MySQL/MariaDB database named `mailora`.
2. Copy `.env.example` to `.env` and set database credentials and an encryption key.
3. Run `php spark migrate`.
4. Run `php spark db:seed DemoSeeder`.
5. Start with `php spark serve` and open `http://localhost:8080`.

## Implemented MVP

- Application-owned single-table registration, login, logout, secure sessions, throttling and CSRF protection.
- Automatic private workspace provisioning for every new account.
- Workspace roles, individual tri-state permissions, change history and workspace assignment enforcement.
- One users table with five account levels: platform admin, platform team, reseller, reseller team, and customer/company.
- Reseller-owned branding, logo, favicon, colors, custom domains and CNAME-ready domain records.
- Host-based white-label resolution with cross-reseller login isolation.
- Workspace-scoped contacts with search, manual entry, CSV import and duplicate removal.
- Tags and segment-ready schema.
- Campaign drafts, HTML/rich-text content, preview, test-email queue feedback, timezone scheduling.
- Campaign events for delivered/open/click/bounce/spam/unsubscribe analytics.
- Template catalogue and starter templates across five industries.
- Welcome, follow-up and birthday automation records.
- Team members and role-aware schema.
- Agency workspaces, separate data ownership, branding and custom domains.
- SMTP provider configuration schema and provider selection UI.
- Analytics report UI for PDF, CSV and Excel.

## Production integrations still required

Background workers, actual SMTP/API delivery adapters, webhook ingestion, open/click tracking endpoints, encrypted credential entry, XLSX parsing/export generation, a full drag-and-drop editor, form publishing and file/object storage.

Promote the first registered account to platform administrator with:

```bash
php spark mailora:promote-admin owner@example.com
```
