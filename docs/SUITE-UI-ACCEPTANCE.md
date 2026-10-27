# Suite DABS interface and acceptance

The dedicated Suite build uses the Suite's light surfaces, navy actions, amber accents,
responsive navigation, project context and a CSRF-protected POST sign-out. Existing
briefing print styles remain separate. Native login remains available only on native
installations; the mandatory gate still owns authentication on dedicated installations.

The existing TinyMCE 5 integration is served locally (5.10.9, LGPL notice retained)
instead of contacting Tiny Cloud with a client key. This preserves the editor's current
major version; it is not a supported-major upgrade. The deployment package includes the
runtime, plugins, skins and license. Safety editing also tolerates an unavailable editor
and retains entered content after a failed save.

## Test information

Use clearly labelled disposable records: `TEST Suite Demo Build Co`,
`TEST Riverside Training Project`, `TEST Northside Electrical`,
`TEST Westfield Joinery`, and contacts ending in `@example.invalid`.
Never use real contractor contact details or send briefing emails during acceptance.
Never insert a second project into a dedicated DABS database: its gateway deliberately
rejects databases containing a different or additional project.

## Deployment boundaries

Deploy only reviewed packaged code to the dedicated Carefoot DABS host, preserving
its private configuration, database, upload folders, PHP restrictions and existing vendor
dependencies. Do not replace legacy DABS with this branch: the existing credential
migration remains unresolved. The foundation PR remains separate from this visual change.

## Required live acceptance before readiness

- Suite owner handoff, expiry, replay rejection, project sign-out and Suite revocation.
- Correct company/project context and a visible DABS module for entitled users.
- Manager creation/editing of a labelled contractor, attendee, activity, safety and notes.
- Viewer read access and rejected writes; disabled account and cross-company denial.
- Project/company isolation, including guessed record IDs and report/file access.
- Desktop/mobile layouts, Bootstrap dropdowns/modals and the two rich text editors.
- Printed briefing and generated PDF; outbound email is a separate explicitly approved test.

Anonymous asset responses and fixture tests do not establish these live results.
Do not set gateway/isolation readiness flags until the corresponding checks pass.
