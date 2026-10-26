# Carefoot Suite integration — deployment gates

Target: Carefoot PLC / Leigh Infirmary – Jean Heys Unit 2.
Suite organisation 10 and project 10 must be revalidated against their names before any deployment mutation.

This branch includes private configuration, request security and a draft dedicated Suite adapter. Live deployment and isolation acceptance remain incomplete. Do not deploy it over an existing DABS installation until private credentials are installed and verified.

## Configuration migration

Move the existing database settings into `private/dabs-database.php` alongside (outside) `httpdocs`, readable only by the hosting account (0600; private directory 0700). Return an array containing host, port, name, user and password. Alternatively set DABS_DB_HOST, DABS_DB_PORT, DABS_DB_NAME, DABS_DB_USER and DABS_DB_PASSWORD in the server environment. DABS_DATABASE_CONFIG may identify another owner-only file outside the web root. Never enter secrets in a scheduled task command or source control.

The repository previously contained a literal database password. Rotate that credential using the hosting panel and update the private deployment configuration. Removing it from the current source does not remove previous Git history.

## Required before enabling Carefoot

- Create a dedicated empty Carefoot DABS database and private evidence storage. Do not import the repository's seeded schema data or logs. Use schema-only migrations reviewed separately.
- Verify the draft adapter on the dedicated host: one-use Suite handoff, server-side session validation/revocation, mapped local roles, and pinned company/project/database binding.
- Enforce that binding on every page, AJAX action, download, report and export. The branch now has explicit role/action policy and endpoint rejection tests; these do not establish company ownership or Suite session validation.
- CSRF and viewer mutation denial are covered by branch tests. Verify the complete browser workflows against the dedicated database before deployment.
- Remove or protect logs, SQL dumps, private includes and deployment files from web access. Do not package repository logs or sample records for Carefoot.
- Prove cross-company read/write denial, replay rejection, logout and active revocation before marking an instance ready.
- Register DABS in Suite's module catalogue and help/status listings only with truthful readiness and a verified instance binding.

## Live inspection on 20 October 2026

Carefoot's existing module switches were enabled. They do not establish isolated instances. Deliveries incorrectly referenced a shared synthetic test site; that reference was cleared. Defects lists Leigh Infirmary (17), QA lists Leigh Infirmary (1), but their ownership and access isolation must still be checked. Programme, Safety, Permits and Notices list no Carefoot source project. Dilapidations discovery returns unauthorized. SiteBeam is currently a platform-admin shortcut; Handover is disabled globally. No tool should be enabled for company users by bypassing these gates.

The Plesk Git token prepared for the original 12 repositories excludes DABS. Its selected repositories must be updated by the owner if DABS is made private. Private pulls and the Suite cross-product workflow credential setup remain separate deployment checks.


## Recovery verification — 26 October 2026

- GitHub PR #20 remains draft and unmerged on `work/carefoot-suite-foundation`. The original foundation CI passed, but did not test roles or isolation.
- Plesk is signed in. DABS domain 955 reports Git commit `24629b4`, PHP 8.4.27 and document root `dabs.defecttracker.uk/httpdocs`. This is the main commit, not the foundation commit. No deployment was performed during recovery.
- The private Suite inventory contains 15 connections, all bound to organisations/projects 7 or 8; none is bound to Carefoot project 10. Do not repurpose them.
- Carefoot's live module form has all ten visible switches checked. Defects selects Leigh Infirmary (17); Deliveries, Safety, Permits, QA, Notices and Programme have no selection. QA offers Leigh Infirmary but its ownership is unverified. Dilapidations lookup is unauthorized. SiteBeam remains a platform-admin shortcut, Handover is globally disabled, and DABS is absent from the module catalogue.
- The attempted read-only Plesk command failed before PHP with `execv("/bin/bash") failed: No such file or directory`. Nothing was scheduled or provisioned. Use a PHP-script task or restore the hosting shell before command-based provisioning.

### Security changes in this branch

All Notes, Safety, Resource Statistics, Attendees, Activity Schedule and logout database connections now use the same private configuration loader; source constructors remain only in `includes/db_connect.php`. This prevents auxiliary requests from silently using the legacy database.

All nine JSON entry points reject unauthenticated access before configuration/database/vendor loading. A default-deny role/action policy enforces POST and session CSRF for changes, rejects project-switch parameters, limits admin/email actions, and denies viewer writes. Main pages attach CSRF headers only to their own origin. Login regenerates the session ID and validates that the selected project is active. Viewing the dashboard no longer creates projects, and viewers do not create daily briefings or upgrade the attendees schema.

The draft adapter is implemented separately from native login. Its private binding requires a dedicated HTTPS subdomain and private session storage, checks an immutable database deployment row and exactly one local project, validates access with Suite on every request, maps current Suite roles, consumes browser sign-in state, exchanges one-use Suite codes, revokes sessions on logout and blocks native identity/credential administration. Legacy project selection still lacks verified per-user company ownership and must never be used for Carefoot access. Live shared-sign-in, replay, revocation, private-evidence and cross-company tests remain mandatory. Apache rules protect includes, logs, SQL/deployment files and generated reports; confirm they are effective on the target host before launch.

## Dedicated DABS adapter deployment

1. Create `carefoot.dabs.defecttracker.uk` with HTTPS and an application-only code deployment. Exclude repository logs, SQL dumps, reports, sample records, Git history and private configuration. Do not copy a working tenant database.
2. Create a dedicated empty database and dedicated database user with access only to that database. Import only `database/suite-empty-schema.sql`, which contains reviewed DDL, no records and the attendee email column used by the current handlers. The installer does not derive or silently fix schemas. Never import `database/schema.sql`, which contains legacy records.
3. Install owner-only `private/dabs-database.php` outside `httpdocs`, plus `private/dabs-suite.php` (0600 within a 0700 directory). The Suite binding returns integer `instance_id`, `organization_id` 10, `project_id` 10, `local_project_id` 1, `module_key` `dabs`, exact `origin` `https://carefoot.dabs.defecttracker.uk`, a new random 64-hex `key`, and a canonical private `session_path` directory (0700 outside the document root). Use an unused instance ID confirmed against the live inventory; example ID 16 in tests is synthetic, not reserved.
4. Revalidate Carefoot names and project IDs against Suite before mutation. Run `bin/provision-suite.php --check` using Plesk **Run a PHP script / PHP 8.4**, then `--install` only after the check passes. The installer refuses any records in the target schema and pins the single Carefoot project. It does not register the Suite connection or declare readiness. If a partial installation is reported, inspect it; do not reset the database or rerun blindly.
5. The installer creates `.suite-required` and `.user.ini` with the absolute `suite-gate.php` auto-prepend path. The database helper also enforces the marker, failing closed if prepend is missing. Wait for FPM configuration refresh and test unauthenticated HTML/API access, private file denial, HTTPS host binding and secure host-only cookies.
6. Register a disabled, unverified `dabs` instance in Suite's private inventory using the same identity/key. The companion Suite catalogue change requires a verified instance and provides no fallback launch to the legacy DABS host. No readiness flag may be set just because PHP or unit tests pass.
7. Validate real sign-in and one-use replay rejection, manager/user/viewer actions, CSRF, role downgrade, logout, active revocation, copied foreign cookies, project/record tampering, PDF/evidence denial and database-user cross-database denial. Test output must distinguish synthetic checks from live acceptance. Enable Carefoot only after these pass.

The old DABS credential remains compromised by Git history and requires rotation with a coordinated private-config update. Do not rotate it without updating the current live installation's connection at the same time.

### Clean deployment package

CI builds `dabs-suite.zip` and its SHA-256 digest from a strict source-file allowlist. Logs, reports, uploads, seeded SQL, Git history, private config, native credential/reset pages and test records are excluded. Composer runtime dependencies are not included; install them on the staged target before enabling it. The generic DABS mark is a neutral placeholder; replace it with the approved Carefoot logo during deployment. Code-package creation is not deployment acceptance.

The 26 October private audit additionally confirmed no `private/dabs-database.php`, no `private/dabs-suite.php` and no request policy on the existing DABS host. QA project 1 is Leigh Infirmary; the QA projects table contains no company ownership column, so its name alone cannot establish Carefoot isolation. PHP-script tasks work and completed both audits successfully; the missing shell blocks command-mode tasks only.
