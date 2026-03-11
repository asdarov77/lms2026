import { test, expect } from '@playwright/test';

test.describe('UI Тестирование - Формы', () => {
  test('проверка консоли на ошибки', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    page.on('pageerror', err => errors.push(err.message));
    
    await page.goto('/');
    await page.waitForTimeout(3000);
    
    const criticalErrors = errors.filter(e => 
      !e.includes('401') && 
      !e.includes('unauthenticated') &&
      !e.includes('CORS')
    );
    
    if (criticalErrors.length > 0) {
      console.log('Critical errors found:', criticalErrors);
    }
    expect(criticalErrors).toHaveLength(0);
  });

  test('проверка загрузки Vuetify компонентов', async ({ page }) => {
    await page.goto('/');
    await page.waitForTimeout(2000);
    
    const vApp = await page.locator('.v-application').count();
    expect(vApp).toBeGreaterThan(0);
  });
});
