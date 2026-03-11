import { test, expect } from '@playwright/test';

const baseUrl = process.env.APP_URL || 'http://localhost:8000';

test.describe('Smoke Tests', () => {
  test('should load login page', async ({ page }) => {
    await page.goto(`${baseUrl}/login`);
    await expect(page).toHaveTitle(/Laravel|Login/);
    await expect(page.locator('[data-testid="email"]')).toBeVisible();
  });

  test('should load dashboard after login', async ({ page }) => {
    await page.goto(`${baseUrl}/login`);
    await page.fill('[data-testid="email"]', process.env.TEST_ADMIN_EMAIL || 'admin@test.com');
    await page.fill('[data-testid="password"]', process.env.TEST_ADMIN_PASSWORD || 'password123');
    await page.click('[data-testid="btn-submit"]');
    
    await page.waitForURL(`${baseUrl}/dashboard`, { timeout: 30000 });
    await expect(page.locator('text=Dashboard')).toBeVisible();
  });

  test('should load courses page', async ({ page }) => {
    await page.goto(`${baseUrl}/login`);
    await page.fill('[data-testid="email"]', process.env.TEST_ADMIN_EMAIL || 'admin@test.com');
    await page.fill('[data-testid="password"]', process.env.TEST_ADMIN_PASSWORD || 'password123');
    await page.click('[data-testid="btn-submit"]');
    await page.waitForURL(`${baseUrl}/dashboard`);
    
    await page.goto(`${baseUrl}/courses`);
    await expect(page.locator('.v-data-table, [data-testid="course-table"], text=Courses')).toBeVisible();
  });

  test('should load users page', async ({ page }) => {
    await page.goto(`${baseUrl}/login`);
    await page.fill('[data-testid="email"]', process.env.TEST_ADMIN_EMAIL || 'admin@test.com');
    await page.fill('[data-testid="password"]', process.env.TEST_ADMIN_PASSWORD || 'password123');
    await page.click('[data-testid="btn-submit"]');
    await page.waitForURL(`${baseUrl}/dashboard`);
    
    await page.goto(`${baseUrl}/users`);
    await expect(page.locator('.v-data-table, [data-testid="user-table"], text=Users')).toBeVisible();
  });

  test('should load groups page', async ({ page }) => {
    await page.goto(`${baseUrl}/login`);
    await page.fill('[data-testid="email"]', process.env.TEST_ADMIN_EMAIL || 'admin@test.com');
    await page.fill('[data-testid="password"]', process.env.TEST_ADMIN_PASSWORD || 'password123');
    await page.click('[data-testid="btn-submit"]');
    await page.waitForURL(`${baseUrl}/dashboard`);
    
    await page.goto(`${baseUrl}/groups`);
    await expect(page.locator('.v-data-table, [data-testid="group-table"], text=Groups')).toBeVisible();
  });

  test('should show 404 for unknown route', async ({ page }) => {
    await page.goto(`${baseUrl}/unknown-page-12345`);
    await expect(page.locator('text=404, text=Not Found')).toBeVisible();
  });

  test('should logout successfully', async ({ page }) => {
    await page.goto(`${baseUrl}/login`);
    await page.fill('[data-testid="email"]', process.env.TEST_ADMIN_EMAIL || 'admin@test.com');
    await page.fill('[data-testid="password"]', process.env.TEST_ADMIN_PASSWORD || 'password123');
    await page.click('[data-testid="btn-submit"]');
    await page.waitForURL(`${baseUrl}/dashboard`);
    
    await page.click('[data-testid="btn-logout"]');
    await page.waitForURL(`${baseUrl}/login`);
  });
});
