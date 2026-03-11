import { test, expect } from '@playwright/test';

test.describe('CRUD: Управление пользователями - полный цикл', () => {
  const uniqueId = Date.now();
  const testUser = {
    fio: `Test User ${uniqueId}`,
    email: `test${uniqueId}@example.com`,
    role: 'Студент'
  };

  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
    
    await page.locator('input[name="login"]').fill('Администратор');
    await page.locator('input[name="password"]').fill('admin123');
    await page.getByRole('button', { name: 'Вход' }).click();
    
    await page.waitForTimeout(3000);
  });

  test('1. CREATE: Открыть форму добавления', async ({ page }) => {
    await page.goto('/user/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000);
    
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(1000);
    
    const addButton = page.locator('button:has-text("Добавить пользователя")');
    await addButton.waitFor({ state: 'visible', timeout: 10000 });
    await addButton.click();
    await page.waitForTimeout(1000);
    
    const dialog = page.locator('.v-dialog--active');
    await expect(dialog).toBeVisible();
  });

  test('2. CREATE: Заполнить и сохранить', async ({ page }) => {
    await page.goto('/user/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000);
    
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(1000);
    
    const addButton = page.locator('button:has-text("Добавить пользователя")');
    await addButton.waitFor({ state: 'visible', timeout: 10000 });
    await addButton.click();
    await page.waitForTimeout(2000);
    
    const inputs = page.locator('.v-dialog input');
    await inputs.nth(0).fill(testUser.fio);
    await inputs.nth(1).fill(testUser.email);
    
    const roleSelect = page.locator('.v-dialog .v-select').first();
    await roleSelect.click();
    await page.waitForTimeout(1000);
    
    const options = page.locator('.v-list-item');
    const optionCount = await options.count();
    console.log('Options count:', optionCount);
    
    if (optionCount > 0) {
      await options.first().click();
      await page.waitForTimeout(1000);
    } else {
      await page.keyboard.press('Escape');
    }
    
    await page.waitForTimeout(1000);
    
    const saveBtn = page.locator('.v-dialog button:has-text("Сохранить")').first();
    await expect(saveBtn).toBeEnabled({ timeout: 15000 });
    await saveBtn.click();
    
    await page.waitForTimeout(3000);
    
    expect(page.url()).not.toContain('/login');
  });

  test('3. READ: Страница пользователей', async ({ page }) => {
    await page.goto('/user/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    await expect(page.locator('.v-application')).toBeVisible();
  });

  test('4. UPDATE: Редактирование', async ({ page }) => {
    await page.goto('/user/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const editButtons = page.locator('.mdi-pencil');
    if (await editButtons.count() > 0) {
      await editButtons.first().click();
      await page.waitForTimeout(1500);
    }
  });

  test('5. DELETE: Удаление', async ({ page }) => {
    await page.goto('/user/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const deleteButtons = page.locator('.mdi-delete');
    if (await deleteButtons.count() > 0) {
      await deleteButtons.first().click();
      await page.waitForTimeout(1000);
    }
  });
});
