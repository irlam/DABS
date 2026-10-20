# Carefoot Suite integration — deployment gates

Target: Carefoot PLC / Leigh Infirmary – Jean Heys Unit 2.
Suite organisation 10 and project 10 must be revalidated against their names before any deployment mutation.

This branch is a configuration foundation, not a completed Suite adapter. Do not deploy it over an existing DABS installation until private credentials are installed and verified.

## Configuration migration

Move the existing database settings into `private/dabs-database.php` alongside (outside) `httpdocs`, readable only by the hosting account (0600; private directory 0700). Return an array containing host, port, name, user and password. Alternatively set DABS_DB_HOST, DABS_DB_PORT, DABS_DB_NAME, DABS_DB_USER and DABS_DB_PASSWORD in the server environment. DABS_DATABASE_CONFIG may identify another owner-only file outside the web root. Never enter secrets in a scheduled task command or source control.

The repository previously contained a literal database password. Rotate that credential using the hosting panel and update the private deployment configuration. Removing it from the current source does not remove previous Git history.

## Required before enabling Carefoot

- Create a dedicated empty Carefoot DABS database and private evidence storage. Do not import the repository's seeded schema data or logs. Use schema-only migrations reviewed separately.
- Add a single-use Suite handoff, server-side session validation/revocation, mapped local user roles, and a pinned company/project binding.
- Enforce that binding on every page, AJAX action, download, report and export. Existing `hasPermission()` currently allows every permission; replace it with explicit policy and rejection tests.
- Add CSRF checks to mutating actions and demonstrate viewer read-only access.
- Remove or protect logs, SQL dumps, private includes and deployment files from web access. Do not package repository logs or sample records for Carefoot.
- Prove cross-company read/write denial, replay rejection, logout and active revocation before marking an instance ready.
- Register DABS in Suite's module catalogue and help/status listings only with truthful readiness and a verified instance binding.

## Live inspection on 20 October 2026

Carefoot's existing module switches were enabled. They do not establish isolated instances. Deliveries incorrectly referenced a shared synthetic test site; that reference was cleared. Defects lists Leigh Infirmary (17), QA lists Leigh Infirmary (1), but their ownership and access isolation must still be checked. Programme, Safety, Permits and Notices list no Carefoot source project. Dilapidations discovery returns unauthorized. SiteBeam is currently a platform-admin shortcut; Handover is disabled globally. No tool should be enabled for company users by bypassing these gates.

The Plesk Git token prepared for the original 12 repositories excludes DABS. Its selected repositories must be updated by the owner if DABS is made private. Private pulls and the Suite cross-product workflow credential setup remain separate deployment checks.
