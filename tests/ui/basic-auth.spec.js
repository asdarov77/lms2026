import { test, expect } from '@playwright/test';

test.describe('Basic Authentication Tests', () => {
  test('should load login page', async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    const title = await page.title();
    expect(title.length).toBeGreaterThan(0);
  });

  test('should have form on login page', async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    await page.locator('form').waitFor();
    const forms = await page.locator('form').count();
    expect(forms).toBeGreaterThan(0);
  });

  test('should have input fields', async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    await page.locator('input').waitFor();
    const inputs = await page.locator('input').count();
    expect(inputs).toBeGreaterThan(0);
  });

  test('should have submit button', async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    await page.locator('button').waitFor();
    const buttons = await page.locator('button').count();
    expect(buttons).toBeGreaterThan(0);
  });
});
