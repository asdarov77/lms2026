import { test, expect } from '@playwright/test';

test.describe('Simple Authentication Tests', () => {
  test('should load login page', async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    await expect(page).toHaveTitle(/LMS/);
  });

  test('should show login form', async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    const form = await page.locator('form').first();
    await expect(form).toBeVisible();
  });

  test('should have FIO input field', async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    const fioInput = await page.locator('input[name="fio"]').first();
    await expect(fioInput).toBeVisible();
  });

  test('should have password input field', async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    const passwordInput = await page.locator('input[name="password"]').first();
    await expect(passwordInput).toBeVisible();
  });
});
