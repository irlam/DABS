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

These are security prerequisites, not a Suite adapter. Legacy project selection still lacks verified per-user company ownership. Shared sign-in, pinned deployment/database binding, replay protection, revocation, private evidence delivery and live cross-company tests remain mandatory. Apache rules protect includes, logs, SQL/deployment files and generated reports; confirm they are effective on the target host before launch.
