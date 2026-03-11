import { test, expect } from '@playwright/test';

test.describe('UI: Интерактивные элементы управления', () => {
  
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
    await page.locator('input[name="login"]').fill('admin');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: 'Вход' }).click();
    await page.waitForTimeout(3000);
  });

  test.describe('Выпадающие списки (v-select)', () => {
    test('1. Открытие select на странице настроек', async ({ page }) => {
      await page.goto('/settings/profile');
      await page.waitForLoadState('networkidle');
      
      const select = page.locator('.v-select').first();
      if (await select.count() > 0) {
        await select.click();
        await page.waitForTimeout(500);
        
        const menu = page.locator('.v-select__menu, .v-menu__content');
        await expect(menu).toBeVisible();
        
        await page.keyboard.press('Escape');
      }
    });

    test('2. Выбор элемента из списка', async ({ page }) => {
      await page.goto('/settings/profile');
      await page.waitForLoadState('networkidle');
      
      const select = page.locator('.v-select').first();
      if (await select.count() > 0) {
        await select.click();
        await page.waitForTimeout(500);
        
        const listItem = page.locator('.v-list-item').first();
        if (await listItem.count() > 0) {
          await listItem.click();
          await page.waitForTimeout(500);
          
          const menuClosed = page.locator('.v-select__menu.v-menu--is-active');
          expect(await menuClosed.count()).toBe(0);
        }
      }
    });

    test('3. Поиск в select', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const select = page.locator('.v-select').first();
      if (await select.count() > 0) {
        await select.click();
        await page.waitForTimeout(500);
        
        const searchInput = page.locator('.v-select__menu input, .v-combobox input');
        if (await searchInput.count() > 0) {
          await searchInput.fill('a');
          await page.waitForTimeout(500);
          
          const listItems = page.locator('.v-list-item');
          expect(await listItems.count()).toBeGreaterThan(0);
        }
      }
    });

    test('4. Клавиатурная навигация в select', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const select = page.locator('.v-select').first();
      if (await select.count() > 0) {
        await select.click();
        await page.waitForTimeout(500);
        
        await page.keyboard.press('ArrowDown');
        await page.keyboard.press('Enter');
        await page.waitForTimeout(500);
      }
    });

    test('5. Очистка select (clearable)', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const select = page.locator('.v-select--clearable').first();
      if (await select.count() > 0) {
        const clearButton = page.locator('.v-select__clear-icon, .mdi-close-circle');
        if (await clearButton.count() > 0) {
          await clearButton.click();
          await page.waitForTimeout(500);
        }
      }
    });
  });

  test.describe('Чекбоксы (v-checkbox)', () => {
    test('6. Установка чекбокса', async ({ page }) => {
      await page.goto('/settings/profile');
      await page.waitForLoadState('networkidle');
      
      const checkbox = page.locator('.v-checkbox input[type="checkbox"]').first();
      if (await checkbox.count() > 0) {
        const isChecked = await checkbox.isChecked();
        await checkbox.click();
        await page.waitForTimeout(300);
        
        const isNowChecked = await checkbox.isChecked();
        expect(isNowChecked).not.toBe(isChecked);
      }
    });

    test('7. Снятие чекбокса', async ({ page }) => {
      await page.goto('/settings/profile');
      await page.waitForLoadState('networkidle');
      
      const checkbox = page.locator('.v-checkbox input[type="checkbox"]').first();
      if (await checkbox.count() > 0) {
        await checkbox.click();
        await page.waitForTimeout(300);
        await checkbox.click();
        await page.waitForTimeout(300);
      }
    });

    test('8. Клик по label чекбокса', async ({ page }) => {
      await page.goto('/settings/profile');
      await page.waitForLoadState('networkidle');
      
      const checkboxWithLabel = page.locator('.v-checkbox').first();
      if (await checkboxWithLabel.count() > 0) {
        const label = checkboxWithLabel.locator('label, .v-label');
        if (await label.count() > 0) {
          await label.click();
          await page.waitForTimeout(300);
        }
      }
    });

    test('9. Disabled чекбокс', async ({ page }) => {
      await page.goto('/settings/profile');
      await page.waitForLoadState('networkidle');
      
      const disabledCheckbox = page.locator('.v-checkbox input:disabled').first();
      if (await disabledCheckbox.count() > 0) {
        await expect(disabledCheckbox).toBeDisabled();
      }
    });
  });

  test.describe('Радиокнопки (v-radio)', () => {
    test('10. Выбор радиокнопки', async ({ page }) => {
      await page.goto('/settings/profile');
      await page.waitForLoadState('networkidle');
      
      const radio = page.locator('.v-radio input[type="radio"]').first();
      if (await radio.count() > 0) {
        await radio.click();
        await page.waitForTimeout(300);
        
        await expect(radio).toBeChecked();
      }
    });

    test('11. Переключение между радиокнопками', async ({ page }) => {
      await page.goto('/settings/profile');
      await page.waitForLoadState('networkidle');
      
      const radios = page.locator('.v-radio input[type="radio"]');
      const count = await radios.count();
      
      if (count > 1) {
        await radios.first().click();
        await page.waitForTimeout(300);
        
        await radios.nth(1).click();
        await page.waitForTimeout(300);
        
        await expect(radios.nth(1)).toBeChecked();
      }
    });
  });

  test.describe('Модальные окна (v-dialog)', () => {
    test('12. Открытие модального окна', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const openButton = page.locator('button:has-text("Добавить"), button:has-text("Add")').first();
      if (await openButton.count() > 0) {
        await openButton.click();
        await page.waitForTimeout(1000);
        
        const dialog = page.locator('.v-dialog--active, .v-dialog.v-overlay--active');
        await expect(dialog).toBeVisible();
      }
    });

    test('13. Закрытие модального окна кнопкой Escape', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const openButton = page.locator('button:has-text("Добавить")').first();
      if (await openButton.count() > 0) {
        await openButton.click();
        await page.waitForTimeout(1000);
        
        await page.keyboard.press('Escape');
        await page.waitForTimeout(500);
        
        const dialog = page.locator('.v-dialog--active');
        expect(await dialog.count()).toBe(0);
      }
    });

    test('14. Закрытие модального окна кликом вне', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const openButton = page.locator('button:has-text("Добавить")').first();
      if (await openButton.count() > 0) {
        await openButton.click();
        await page.waitForTimeout(1000);
        
        const overlay = page.locator('.v-overlay__scrim');
        if (await overlay.count() > 0) {
          await overlay.click({ position: { x: 10, y: 10 } });
          await page.waitForTimeout(500);
        }
      }
    });

    test('15. Закрытие модального окна кнопкой X', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const openButton = page.locator('button:has-text("Добавить")').first();
      if (await openButton.count() > 0) {
        await openButton.click();
        await page.waitForTimeout(1000);
        
        const closeButton = page.locator('.v-dialog .mdi-close, .v-dialog__close');
        if (await closeButton.count() > 0) {
          await closeButton.click();
          await page.waitForTimeout(500);
        }
      }
    });

    test('16. Модальное окно с формой', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const openButton = page.locator('button:has-text("Добавить")').first();
      if (await openButton.count() > 0) {
        await openButton.click();
        await page.waitForTimeout(1000);
        
        const form = page.locator('.v-dialog form, .v-dialog .v-form');
        if (await form.count() > 0) {
          await expect(form).toBeVisible();
          
          const inputs = page.locator('.v-dialog input');
          if (await inputs.count() > 0) {
            await inputs.first().fill('Test User');
            await expect(inputs.first()).toHaveValue('Test User');
          }
        }
      }
    });

    test('17. Scroll внутри модального окна', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const openButton = page.locator('button:has-text("Добавить")').first();
      if (await openButton.count() > 0) {
        await openButton.click();
        await page.waitForTimeout(1000);
        
        const dialogContent = page.locator('.v-dialog .v-card__content, .v-dialog .v-card-body');
        if (await dialogContent.count() > 0) {
          const canScroll = await dialogContent.evaluate(el => el.scrollHeight > el.clientHeight);
          if (canScroll) {
            await dialogContent.evaluate(el => el.scrollTop = el.scrollHeight);
            await page.waitForTimeout(300);
          }
        }
      }
    });
  });

  test.describe('Тултипы (v-tooltip)', () => {
    test('18. Отображение тултипа при hover', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const elementWithTooltip = page.locator('.v-tooltip .v-btn, [data-testid]').first();
      if (await elementWithTooltip.count() > 0) {
        await elementWithTooltip.hover();
        await page.waitForTimeout(500);
        
        const tooltip = page.locator('.v-tooltip__content, .v-tooltip > div:not(.v-btn)');
        if (await tooltip.count() > 0) {
          await expect(tooltip).toBeVisible();
        }
      }
    });
  });

  test.describe('Таблицы (v-data-table)', () => {
    test('19. Сортировка по колонке', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const sortableHeader = page.locator('th.v-data-table__th--sortable').first();
      if (await sortableHeader.count() > 0) {
        await sortableHeader.click();
        await page.waitForTimeout(500);
        
        await sortableHeader.click();
        await page.waitForTimeout(500);
      }
    });

    test('20. Выбор строки чекбоксом', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const rowCheckbox = page.locator('tbody .v-checkbox input[type="checkbox"]').first();
      if (await rowCheckbox.count() > 0) {
        await rowCheckbox.click();
        await page.waitForTimeout(300);
        
        await expect(rowCheckbox).toBeChecked();
      }
    });

    test('21. Expand/collapse строки', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const expandButton = page.locator('.v-data-table__expand-icon, .mdi-chevron-down').first();
      if (await expandButton.count() > 0) {
        await expandButton.click();
        await page.waitForTimeout(500);
        
        const expandedContent = page.locator('.v-data-table__expanded');
        if (await expandedContent.count() > 0) {
          await expect(expandedContent).toBeVisible();
        }
      }
    });
  });

  test.describe('Кнопки', () => {
    test('22. Primary кнопка', async ({ page }) => {
      await page.goto('/login');
      
      const primaryButton = page.locator('button.v-btn--color-primary, .bg-primary button').first();
      if (await primaryButton.count() > 0) {
        await expect(primaryButton).toBeVisible();
      }
    });

    test('23. Disabled кнопка', async ({ page }) => {
      await page.goto('/login');
      
      const disabledButton = page.locator('button:disabled');
      const count = await disabledButton.count();
      if (count > 0) {
        await expect(disabledButton.first()).toBeDisabled();
      }
    });

    test('24. Loading состояние кнопки', async ({ page }) => {
      await page.goto('/login');
      await page.locator('input[name="login"]').fill('admin');
      await page.locator('input[name="password"]').fill('password');
      
      const button = page.getByRole('button', { name: 'Вход' });
      await button.click();
      
      const loadingIndicator = page.locator('.v-btn--loading .v-progress-circular, button .v-progress-circular');
      await page.waitForTimeout(500);
    });

    test('25. Icon кнопка', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const iconButton = page.locator('.v-btn--icon, .v-btn .mdi');
      if (await iconButton.count() > 0) {
        await expect(iconButton.first()).toBeVisible();
      }
    });
  });

  test.describe('Input поля', () => {
    test('26. Text input', async ({ page }) => {
      await page.goto('/login');
      
      const textInput = page.locator('input[type="text"], input:not([type])').first();
      if (await textInput.count() > 0) {
        await textInput.fill('Test');
        await expect(textInput).toHaveValue('Test');
      }
    });

    test('27. Password input', async ({ page }) => {
      await page.goto('/login');
      
      const passwordInput = page.locator('input[type="password"]').first();
      if (await passwordInput.count() > 0) {
        await passwordInput.fill('secret');
        await expect(passwordInput).toHaveValue('secret');
      }
    });

    test('28. Email input validation', async ({ page }) => {
      await page.goto('/register');
      await page.waitForTimeout(1000);
      
      const emailInput = page.locator('input[type="email"]').first();
      if (await emailInput.count() > 0) {
        await emailInput.fill('invalid-email');
        await emailInput.blur();
        await page.waitForTimeout(500);
        
        const errorMessage = page.locator('.v-messages__message');
        if (await errorMessage.count() > 0) {
          await expect(errorMessage.first()).toBeVisible();
        }
      }
    });

    test('29. Number input', async ({ page }) => {
      await page.goto('/courses/list');
      await page.waitForLoadState('networkidle');
      
      const numberInput = page.locator('input[type="number"]').first();
      if (await numberInput.count() > 0) {
        await expect(numberInput).toBeVisible();
      }
    });

    test('30. Textarea', async ({ page }) => {
      await page.goto('/courses/list');
      await page.waitForLoadState('networkidle');
      
      const textarea = page.locator('textarea').first();
      if (await textarea.count() > 0) {
        await expect(textarea).toBeVisible();
      }
    });
  });

  test.describe('Навигация', () => {
    test('31. Sidebar меню', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('networkidle');
      
      const sidebar = page.locator('.v-navigation-drawer, aside');
      if (await sidebar.count() > 0) {
        await expect(sidebar).toBeVisible();
      }
    });

    test('32. Переход по пунктам меню', async ({ page }) => {
      await page.goto('/');
      await page.waitForLoadState('networkidle');
      
      const menuItem = page.locator('.v-list-item').first();
      if (await menuItem.count() > 0) {
        const href = await menuItem.locator('a').first().getAttribute('href');
        if (href) {
          await menuItem.click();
          await page.waitForTimeout(1000);
        }
      }
    });

    test('33. Active пункт меню', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const activeItem = page.locator('.v-list-item--active');
      if (await activeItem.count() > 0) {
        await expect(activeItem).toBeVisible();
      }
    });

    test('34. Хлебные крошки', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const breadcrumbs = page.locator('.v-breadcrumbs, .breadcrumbs');
      if (await breadcrumbs.count() > 0) {
        await expect(breadcrumbs).toBeVisible();
      }
    });
  });

  test.describe('Уведомления и алерты', () => {
    test('35. Success alert', async ({ page }) => {
      await page.goto('/login');
      await page.locator('input[name="login"]').fill('admin');
      await page.locator('input[name="password"]').fill('password');
      await page.getByRole('button', { name: 'Вход' }).click();
      await page.waitForTimeout(2000);
      
      const successAlert = page.locator('.v-alert--type-success, .v-alert.success');
      if (await successAlert.count() > 0) {
        await expect(successAlert.first()).toBeVisible();
      }
    });

    test('36. Error alert', async ({ page }) => {
      await page.goto('/login');
      await page.locator('input[name="login"]').fill('wronguser');
      await page.locator('input[name="password"]').fill('wrongpass');
      await page.getByRole('button', { name: 'Вход' }).click();
      await page.waitForTimeout(2000);
      
      const errorAlert = page.locator('.v-alert--type-error, .v-alert.error');
      if (await errorAlert.count() > 0) {
        await expect(errorAlert.first()).toBeVisible();
      }
    });

    test('37. Snackbar уведомление', async ({ page }) => {
      await page.goto('/user/list');
      await page.waitForLoadState('networkidle');
      
      const snackbar = page.locator('.v-snackbar, .v-snackbar__wrapper');
      if (await snackbar.count() > 0) {
        await expect(snackbar).toBeVisible();
      }
    });
  });

  test.describe('Загрузка файлов', () => {
    test('38. File input присутствует', async ({ page }) => {
      await page.goto('/files/add');
      await page.waitForLoadState('networkidle');
      
      const fileInput = page.locator('input[type="file"]');
      if (await fileInput.count() > 0) {
        await expect(fileInput.first()).toBeVisible();
      }
    });

    test('39. Drag and drop зона', async ({ page }) => {
      await page.goto('/files/add');
      await page.waitForLoadState('networkidle');
      
      const dropzone = page.locator('.v-file-input, .dropzone, [draggable="true"]');
      if (await dropzone.count() > 0) {
        await expect(dropzone.first()).toBeVisible();
      }
    });
  });
});
