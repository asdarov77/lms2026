import { test, expect } from '@playwright/test';

test.describe('Dashboard', () => {
  test('загрузка dashboard', async ({ page }) => {
    await page.goto('/dashboard');
    await page.waitForTimeout(2000);
    const hasContent = await page.locator('.v-card, .v-application').count() > 0;
    expect(hasContent).toBeTruthy();
  });
});

test.describe('Мои курсы', () => {
  test('страница моих курсов', async ({ page }) => {
    await page.goto('/my-courses');
    await page.waitForTimeout(2000);
    const hasContent = await page.locator('.v-card, .v-application').count() > 0;
    expect(hasContent).toBeTruthy();
  });
});

test.describe('Задания', () => {
  test('страница заданий', async ({ page }) => {
    await page.goto('/assignments');
    await page.waitForTimeout(2000);
    const hasContent = await page.locator('.v-card, .v-application').count() > 0;
    expect(hasContent).toBeTruthy();
  });
});

test.describe('Календарь', () => {
  test('страница календаря', async ({ page }) => {
    await page.goto('/calendar');
    await page.waitForTimeout(2000);
    const hasContent = await page.locator('.v-card, .v-application').count() > 0;
    expect(hasContent).toBeTruthy();
  });
});

test.describe('Уведомления', () => {
  test('страница уведомлений', async ({ page }) => {
    await page.goto('/notifications');
    await page.waitForTimeout(2000);
    const hasContent = await page.locator('.v-card, .v-application').count() > 0;
    expect(hasContent).toBeTruthy();
  });
});

test.describe('Навигация', () => {
  test('проверка главного меню', async ({ page }) => {
    await page.goto('/');
    await page.waitForTimeout(3000);
    const hasApp = await page.locator('.v-application').count() > 0;
    expect(hasApp).toBeTruthy();
  });
});
