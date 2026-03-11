# E2E Test Framework for LMS2025

Comprehensive end-to-end testing framework using Playwright and Puppeteer.

## Features

- **Playwright** - Modern E2E testing with multi-browser support (Chromium, Firefox, WebKit)
- **Puppeteer** - Alternative testing framework for comparison
- **Database Verification** - Direct PostgreSQL verification for CRUD operations
- **Auto-retry** - Automatic retry logic for flaky tests
- **Screenshots & Videos** - Automatic artifact collection on failures
- **CI/CD Ready** - GitHub Actions workflow included

## Prerequisites

- Node.js 18+
- PHP 8.3+
- PostgreSQL 14+
- Laravel 12+ application running

## Installation

```bash
# Install dependencies
cd e2e
npm install

# Install Playwright browsers
npx playwright install --with-deps
```

## Configuration

1. Copy `.env.example` to `.env` and configure:

```bash
cp .env.example .env
```

2. Update `.env` with your settings:

```env
APP_URL=http://localhost:8000
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=lms2025_db
DB_USERNAME=lms
DB_PASSWORD=lms
TEST_ADMIN_EMAIL=admin@test.com
TEST_ADMIN_PASSWORD=password123
```

## Running Tests

### Playwright

```bash
# All tests
npm test

# With UI (headed mode)
npm run test:ui

# Smoke tests only
npm run test:smoke

# Auth tests only
npm run test:auth

# CRUD tests only
npm run test:crud

# Specific test file
npm run test:crud:course

# With debug
npm run test:debug
```

### Puppeteer

```bash
npm run puppeteer:test
```

## Test Structure

```
e2e/
├── tests/
│   ├── auth/           # Authentication tests
│   │   └── auth.spec.ts
│   ├── crud/           # CRUD operation tests
│   │   ├── course.spec.ts
│   │   ├── group.spec.ts
│   │   └── user.spec.ts
│   └── smoke/          # Smoke tests
│       └── smoke.spec.ts
├── helpers/
│   ├── database.ts     # Database utilities
│   ├── playwright.ts   # Playwright helpers
│   └── testData.ts     # Test data generation
├── fixtures/
│   └── auth.ts         # Test fixtures
├── config/
│   └── playwright.config.ts
├── puppeteer/
│   └── tests/
│       └── course-crud.js
├── .github/
│   └── workflows/
│       └── e2e-tests.yml
└── package.json
```

## Test Data

Test data is generated using Faker with seeded randomization for reproducibility:

```typescript
import { testData } from './helpers/testData';

const user = testData.user();     // Random user
const course = testData.course(); // Random course
const group = testData.group();   // Random group
```

## Database Verification

After each CRUD operation, tests verify data in the database:

```typescript
import { verifyRecordByField, verifyRecord } from './helpers/database';

// Verify record exists
const course = await verifyRecordByField('courses', 'name', 'Test Course');

// Verify field values
const isValid = await verifyFieldValue('courses', courseId, 'name', 'Updated Name');

// Verify record deleted
const deleted = await verifyRecord('courses', courseId);
expect(deleted).toBeNull();
```

## Auto-Fix Strategies

The framework includes automatic retry logic:

1. **Element Not Found** - Try alternative selectors, wait for load
2. **Network Instability** - Retry request, reload page
3. **Race Conditions** - Add wait between steps

## Reports

Reports are generated in multiple formats:

- **HTML**: `playwright-report/index.html`
- **JSON**: `test-results/results.json`

## CI/CD

GitHub Actions workflow is included in `.github/workflows/e2e-tests.yml`.

### Environment Variables for CI

```yaml
env:
  APP_URL: http://localhost:8000
  DB_HOST: localhost
  DB_PORT: 5432
  DB_DATABASE: lms2025_db
  DB_USERNAME: lms
  DB_PASSWORD: lms
```

## Best Practices

1. Use `data-testid` attributes for stable element selection
2. Always verify data in database after CRUD operations
3. Clean up test data in `afterEach` hooks
4. Use seeded faker for reproducible tests
5. Take screenshots on failure for debugging

## Troubleshooting

### Tests fail to connect to database

Ensure PostgreSQL is running and credentials are correct in `.env`.

### Server not starting

Make sure port 8000 is available:

```bash
php artisan serve --port=8080
```

### Browser not installed

```bash
npx playwright install chromium
```

## License

ISC
