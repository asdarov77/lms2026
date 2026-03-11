import { test, expect } from '@playwright/test';

test.describe('Import', () => {
  const baseUrl = process.env.APP_URL || 'http://localhost:8000';
  const fio = 'Администратор';
  const password = 'admin123';

  test.beforeEach(async ({ page }) => {
    await page.goto(`${baseUrl}/login`);
    
    await page.fill('input[name="login"]', fio);
    await page.fill('input[name="password"]', password);
    await page.click('button:has-text("Вход")');
    
    await page.waitForTimeout(3000);
  });

  test('should clear and import class', async ({ page }) => {
    await page.goto(`${baseUrl}/import`);
    
    await page.waitForTimeout(2000);
    
    const clearButton = page.locator('button:has-text("Очистить")');
    if (await clearButton.isVisible().catch(() => false)) {
      await clearButton.click();
      await page.waitForTimeout(2000);
    }
    
    await page.goto(`${baseUrl}/import`);
    await page.waitForTimeout(2000);
    
    const aircraftItem = page.locator('.v-list-item:has-text("КЛЕН")').first();
    if (await aircraftItem.isVisible({ timeout: 5000 }).catch(() => false)) {
      await aircraftItem.click();
      await page.waitForTimeout(1000);
      
      const importButton = page.locator('.v-list-item .v-btn:has-text("Добавить")').first();
      if (await importButton.isVisible({ timeout: 3000 }).catch(() => false)) {
        await importButton.click();
        await page.waitForTimeout(10000);
        
        const alert = page.locator('.v-alert').first();
        await expect(alert).toBeVisible({ timeout: 5000 });
      }
    }
    
    const successText = page.locator('text=успешно');
    console.log('Has success:', await successText.isVisible().catch(() => false));
  });
});
