import { test, expect } from '@playwright/test';

test.describe('Авторизация', () => {
  test('отображение формы логина', async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('text=Форма авторизации')).toBeVisible();
    await expect(page.locator('input[name="login"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Вход' })).toBeVisible();
  });

  test('кнопка disabled при пустых полях', async ({ page }) => {
    await page.goto('/login');
    const button = page.getByRole('button', { name: 'Вход' });
    await expect(button).toBeDisabled();
  });

  test('успешная авторизация', async ({ page }) => {
    await page.goto('/login');
    await page.locator('input[name="login"]').fill('admin');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: 'Вход' }).click();
    await page.waitForTimeout(2000);
  });

  test('переключение видимости пароля', async ({ page }) => {
    await page.goto('/login');
    const passwordInput = page.locator('input[name="password"]');
    await expect(passwordInput).toHaveAttribute('type', 'password');
    await page.locator('.mdi-eye-off, .mdi-eye').click();
    await expect(passwordInput).toHaveAttribute('type', 'text');
  });
});
