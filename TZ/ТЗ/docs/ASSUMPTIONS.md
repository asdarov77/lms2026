# ASSUMPTIONS

- Laravel 10+ is used (if repo has Laravel 9, minimal adaptions required).
- Frontend is Vue 3 + Vuetify 3. If repo uses Vue 2, components need backporting.
- Sanctum chosen for SPA auth; alternative Passport can be used if OAuth needed.
- Media files stored in S3-compatible storage; dev uses local disk.
- Spatie/permission will be installed and migrations run to create roles/permissions tables.
- SCORM import complexity: we process manifest and copy files to storage; full API for runtime SCORM tracking is out of scope.
- CI uses GitHub Actions; adapt to GitLab CI if required.