import { test, expect } from '@playwright/test';

test.describe('Главная страница', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('загрузка без ошибок', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });
    await page.waitForTimeout(2000);
    expect(consoleErrors.filter(e => !e.includes('401'))).toHaveLength(0);
  });
});

test.describe('Страницы без авторизации', () => {
  test('страница 404', async ({ page }) => {
    await page.goto('/nonexistent-page-12345');
    await expect(page.locator('text=Страница не найдена')).toBeVisible({ timeout: 10000 });
  });
});
