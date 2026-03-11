# CHANGELOG (proposed patches to apply)

## [Unapplied] refactor/api-yyyymmdd
- feat(api): standardize API envelope and add /api/v1 prefix
- feat(auth): add login/logout endpoints and token envelope
- feat(rbac): add spatie/permission integration (migrations + seeder)
- fix(db): add indices for courses.title (GIN), lessons.aukstructure_id, test_results.user_id
- feat(openapi): add docs/openapi.yaml
- docs: add docs/TECH_SPEC.md, ui-design.md, vuex.md, ASSUMPTIONS.md
- ci: add GitHub Actions workflow for lint/test/build

## TODOs (manual apply)
- Install composer packages: spatie/laravel-permission, predis/redis, barryvdh/laravel-dompdf
- Migrate DB and run seeders
- Wire up frontend to new API envelope and error handling
- Add unit tests to cover business logic and endpoints