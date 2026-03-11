# How to apply the proposed patches

1. Create feature branch from current main:
   git checkout -b refactor/api-$(date +%Y%m%d)

2. Copy files from /docs in this package into your repository root:
   - docs/openapi.yaml -> docs/openapi.yaml
   - docs/TECH_SPEC.md -> docs/TECH_SPEC.md
   - docs/vuex.md etc.

3. Install composer & npm packages as in ASSUMPTIONS.md

4. Run migrations and seeders:
   php artisan migrate
   php artisan db:seed --class=RolePermissionSeeder
   php artisan db:seed --class=DemoDataSeeder

5. Run tests:
   vendor/bin/phpunit
   npm test

6. Create PR and request review.