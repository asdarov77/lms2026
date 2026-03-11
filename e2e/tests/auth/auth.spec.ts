import { test, expect } from '@playwright/test';
import { testData } from '../../helpers/testData';
import { verifyRecordByField, execute } from '../../helpers/database';

test.describe('Authentication', () => {
  const baseUrl = process.env.APP_URL || 'http://localhost:8000';

  test.describe('Login', () => {
    test('should show login form', async ({ page }) => {
      await page.goto(`${baseUrl}/login`);
      
      await expect(page.locator('[data-testid="email"]')).toBeVisible();
      await expect(page.locator('[data-testid="password"]')).toBeVisible();
      await expect(page.locator('[data-testid="btn-submit"]')).toBeVisible();
    });

    test('should login with valid credentials', async ({ page }) => {
      await page.goto(`${baseUrl}/login`);
      
      const testUser = testData.user();
      await createTestUserInDb(testUser);
      
      await page.fill('[data-testid="email"]', testUser.email);
      await page.fill('[data-testid="password"]', testUser.password);
      await page.click('[data-testid="btn-submit"]');
      
      await page.waitForURL(`${baseUrl}/dashboard`, { timeout: 30000 });
      await expect(page).toHaveURL(/dashboard/);
    });

    test('should show error with invalid credentials', async ({ page }) => {
      await page.goto(`${baseUrl}/login`);
      
      await page.fill('[data-testid="email"]', 'invalid@test.com');
      await page.fill('[data-testid="password"]', 'wrongpassword');
      await page.click('[data-testid="btn-submit"]');
      
      await expect(page.locator('.v-alert, [data-testid="error-message"]')).toBeVisible();
    });

    test('should validate empty fields', async ({ page }) => {
      await page.goto(`${baseUrl}/login`);
      
      await page.click('[data-testid="btn-submit"]');
      
      await expect(page.locator('text=required, text=Обязательно')).toBeVisible();
    });
  });

  test.describe('Registration', () => {
    test('should show registration form', async ({ page }) => {
      await page.goto(`${baseUrl}/register`);
      
      await expect(page.locator('[data-testid="email"]')).toBeVisible();
      await expect(page.locator('[data-testid="fio"]')).toBeVisible();
      await expect(page.locator('[data-testid="password"]')).toBeVisible();
      await expect(page.locator('[data-testid="password_confirmation"]')).toBeVisible();
    });

    test('should register new user successfully', async ({ page }) => {
      await page.goto(`${baseUrl}/register`);
      
      const formData = testData.registrationForm();
      
      await page.fill('[data-testid="email"]', formData.email);
      await page.fill('[data-testid="fio"]', formData.fio);
      await page.fill('[data-testid="password"]', formData.password);
      await page.fill('[data-testid="password_confirmation"]', formData.password_confirmation);
      await page.click('[data-testid="btn-submit"]');
      
      await page.waitForURL(`${baseUrl}/dashboard`, { timeout: 30000 });
      
      const userInDb = await verifyRecordByField('users', 'email', formData.email);
      expect(userInDb).not.toBeNull();
      expect(userInDb?.email).toBe(formData.email);
    });

    test('should validate password mismatch', async ({ page }) => {
      await page.goto(`${baseUrl}/register`);
      
      await page.fill('[data-testid="email"]', 'test@test.com');
      await page.fill('[data-testid="fio"]', 'Test User');
      await page.fill('[data-testid="password"]', 'password123');
      await page.fill('[data-testid="password_confirmation"]', 'differentpassword');
      await page.click('[data-testid="btn-submit"]');
      
      await expect(page.locator('text=match, text=совпадать')).toBeVisible();
    });
  });

  test.describe('Logout', () => {
    test('should logout successfully', async ({ page }) => {
      await page.goto(`${baseUrl}/login`);
      
      const testUser = testData.user();
      await createTestUserInDb(testUser);
      
      await page.fill('[data-testid="email"]', testUser.email);
      await page.fill('[data-testid="password"]', testUser.password);
      await page.click('[data-testid="btn-submit"]');
      
      await page.waitForURL(`${baseUrl}/dashboard`);
      
      await page.click('[data-testid="btn-logout"]');
      
      await page.waitForURL(`${baseUrl}/login`);
    });
  });
});

async function createTestUserInDb(user: { email: string; password: string; fio: string }): Promise<void> {
  const { execute } = await import('../../helpers/database');
  
  await execute(
    `INSERT INTO users (email, password, fio, role, created_at, updated_at)
     VALUES ($1, $2, $3, 'student', NOW(), NOW())
     ON CONFLICT (email) DO NOTHING`,
    [user.email, user.password, user.fio]
  );
}
