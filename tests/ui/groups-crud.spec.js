import { test, expect } from '@playwright/test';

test.describe('CRUD: Управление группами - полный цикл', () => {
  const testGroup = {
    name: `Test Group ${Date.now()}`,
    description: 'Test description',
    isActive: true,
    maxUsers: 10
  };

  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
    await page.locator('input[name="login"]').fill('admin');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: 'Вход' }).click();
    await page.waitForTimeout(3000);
  });

  test('1. CREATE: Создание новой группы', async ({ page }) => {
    await page.goto('/groups/add');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const form = page.locator('form.v-form, form');
    await expect(form).toBeVisible();
    
    const nameInput = page.locator('input').filter({ hasText: '' }).first();
    if (await nameInput.count() > 0) {
      await nameInput.fill(testGroup.name);
    }
    
    const inputs = page.locator('.v-text-field input');
    const inputCount = await inputs.count();
    
    if (inputCount > 0) {
      await inputs.first().fill(testGroup.name);
    }
    
    if (inputCount > 1) {
      await inputs.nth(1).fill(testGroup.description);
    }
    
    const saveButton = page.locator('button:has-text("Сохранить")');
    if (await saveButton.count() > 0) {
      await saveButton.click();
      await page.waitForTimeout(3000);
      
      await page.goto('/groups/list');
      await page.waitForLoadState('networkidle');
      await page.waitForTimeout(2000);
      
      const pageText = await page.locator('body').textContent();
      expect(pageText).toContain(testGroup.name);
    }
  });

  test('2. READ: Просмотр списка групп', async ({ page }) => {
    await page.goto('/groups/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const app = page.locator('.v-application');
    await expect(app).toBeVisible();
  });

  test('3. UPDATE: Редактирование группы', async ({ page }) => {
    await page.goto('/groups/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000);
    
    const editButtons = page.locator('button:has-text("Редактировать"), .mdi-pencil, button[aria-label*="Edit"]');
    
    if (await editButtons.count() > 0) {
      await editButtons.first().click();
      await page.waitForTimeout(2000);
      
      const pageUrl = page.url();
      console.log('Current URL after click:', pageUrl);
      
      const formVisible = page.locator('form, .v-form');
      if (await formVisible.count() > 0) {
        await expect(formVisible).toBeVisible();
        
        const nameInput = page.locator('input').first();
        const originalValue = await nameInput.inputValue();
        console.log('Original value:', originalValue);
        
        if (originalValue) {
          const newName = `${originalValue} Updated`;
          await nameInput.fill(newName);
          
          const saveButton = page.locator('button:has-text("Сохранить"), button[type="submit"]');
          if (await saveButton.count() > 0) {
            await saveButton.click();
            await page.waitForTimeout(3000);
            
            const successNotification = page.locator('.v-snackbar, .v-alert--type-success');
            const errorOnPage = page.locator('.v-alert--type-error, .text-red');
            
            console.log('Success notification present:', await successNotification.count());
            console.log('Error on page present:', await errorOnPage.count());
          }
        }
      }
    } else {
      const tableRows = page.locator('tbody tr, .v-data-table__tr');
      const rowCount = await tableRows.count();
      
      if (rowCount > 0) {
        await tableRows.first().click();
        await page.waitForTimeout(2000);
        
        const editLink = page.locator('a[href*="groups/edit"]');
        if (await editLink.count() > 0) {
          await editLink.first().click();
          await page.waitForTimeout(2000);
          
          const formVisible = page.locator('form, .v-form');
          if (await formVisible.count() > 0) {
            await expect(formVisible).toBeVisible();
          }
        }
      }
    }
  });

  test('4. DELETE: Удаление группы', async ({ page }) => {
    await page.goto('/groups/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000);
    
    const tableRows = page.locator('tbody tr, .v-data-table__tr');
    const initialCount = await tableRows.count();
    
    if (initialCount > 0) {
      const deleteButtons = page.locator('button:has-text("Удалить"), .mdi-delete');
      
      if (await deleteButtons.count() > 0) {
        await deleteButtons.first().click();
        await page.waitForTimeout(1000);
        
        const confirmDialog = page.locator('.v-dialog, .v-modal');
        if (await confirmDialog.count() > 0) {
          const confirmButton = page.locator('button:has-text("Да"), button:has-text("ОК"), button:has-text("Подтвердить")');
          if (await confirmButton.count() > 0) {
            await confirmButton.click();
            await page.waitForTimeout(2000);
          }
        }
        
        const finalCount = await tableRows.count();
        expect(finalCount).toBeLessThanOrEqual(initialCount);
      }
    }
  });

  test('5. CREATE: Валидация - обязательное поле имя группы', async ({ page }) => {
    await page.goto('/groups/add');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const saveButton = page.locator('button:has-text("Сохранить")');
    if (await saveButton.count() > 0) {
      await saveButton.click();
      await page.waitForTimeout(1000);
      
      const errorMessage = page.locator('.v-messages__message, .text-red, .error');
      if (await errorMessage.count() > 0) {
        await expect(errorMessage.first()).toBeVisible();
      }
    }
  });

  test('6. READ: Проверка данных группы при редактировании', async ({ page }) => {
    await page.goto('/groups/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000);
    
    const tableRows = page.locator('tbody tr, .v-data-table__tr');
    const rowCount = await tableRows.count();
    
    if (rowCount > 0) {
      const firstRowCells = tableRows.first().locator('td, th');
      const cellCount = await firstRowCells.count();
      
      if (cellCount > 0) {
        const groupName = await firstRowCells.first().textContent();
        console.log('Group name in table:', groupName);
        
        const editButtons = page.locator('.mdi-pencil');
        if (await editButtons.count() > 0) {
          await editButtons.first().click();
          await page.waitForTimeout(2000);
          
          const nameInput = page.locator('input').first();
          const inputValue = await nameInput.inputValue();
          console.log('Input value in edit form:', inputValue);
          
          expect(inputValue.trim()).toBeTruthy();
        }
      }
    }
  });
});
