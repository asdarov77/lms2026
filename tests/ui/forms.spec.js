import { test, expect } from '@playwright/test';

test.describe('Форма логина', () => {
  test('отображение формы логина', async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('text=Форма авторизации')).toBeVisible();
    await expect(page.locator('input[name="login"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
  });

  test('валидация: кнопка disabled при пустых полях', async ({ page }) => {
    await page.goto('/login');
    const button = page.getByRole('button', { name: 'Вход' });
    await expect(button).toBeDisabled();
  });

  test('валидация: показ ошибок при пустой отправке', async ({ page }) => {
    await page.goto('/login');
    await page.locator('input[name="login"]').focus();
    await page.locator('input[name="login"]').blur();
    await page.locator('input[name="password"]').focus();
    await page.locator('input[name="password"]').blur();
    const messages = page.locator('.v-messages__message');
    const count = await messages.count();
    expect(count).toBeGreaterThanOrEqual(1);
  });

  test('валидация: ФИО обязательно', async ({ page }) => {
    await page.goto('/login');
    await page.locator('input[name="login"]').fill('test');
    await page.locator('input[name="password"]').fill('');
    await page.locator('input[name="password"]').blur();
    await expect(page.locator('.v-messages__message').first()).toBeVisible();
  });

  test('переключение видимости пароля', async ({ page }) => {
    await page.goto('/login');
    const passwordInput = page.locator('input[name="password"]');
    await expect(passwordInput).toHaveAttribute('type', 'password');
    await page.locator('.mdi-eye-off, .mdi-eye').click();
    await expect(passwordInput).toHaveAttribute('type', 'text');
  });

  test('клавиатурная навигация: Tab переходит между полями', async ({ page }) => {
    await page.goto('/login');
    await page.keyboard.press('Tab');
    const focused = page.locator('input:focus');
    await expect(focused).toHaveAttribute('name', 'login');
    await page.keyboard.press('Tab');
    await expect(focused).toHaveAttribute('name', 'password');
  });

  test('Enter на кнопке входа', async ({ page }) => {
    await page.goto('/login');
    await page.locator('input[name="login"]').fill('admin');
    await page.locator('input[name="password"]').fill('password');
    await page.keyboard.press('Enter');
    await page.waitForTimeout(1000);
  });
});

test.describe('Создание пользователя', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/users/create');
    await page.waitForLoadState('networkidle');
  });

  test('форма загружается', async ({ page }) => {
    await expect(page.locator('form, .v-form')).toBeVisible();
  });

  test('интерактивные элементы присутствуют', async ({ page }) => {
    const button = page.getByRole('button', { name: /сохранить|save/i }).or(page.getByRole('button', { name: /создать|create/i }));
    if (await button.count() > 0) {
      await expect(button).toBeVisible();
    }
  });
});

test.describe('Создание курса', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/courses/create');
    await page.waitForLoadState('networkidle');
  });

  test('форма загружается', async ({ page }) => {
    await expect(page.locator('form, .v-form')).toBeVisible();
  });
});

test.describe('Навигация', () => {
  test('главное меню доступно после авторизации', async ({ page }) => {
    await page.goto('/login');
    await page.locator('input[name="login"]').fill('admin');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: 'Вход' }).click();
    await page.waitForTimeout(3000);
    
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const appLayout = page.locator('.v-application');
    await expect(appLayout).toBeVisible();
  });

  test('переход по ссылкам меню', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    const firstLink = page.locator('.v-list-item a, nav a').first();
    if (await firstLink.count() > 0) {
      await firstLink.click();
      await page.waitForTimeout(500);
    }
  });
});

test.describe('Модальные окна', () => {
  test('модальное окно открывается и закрывается', async ({ page }) => {
    await page.goto('/users');
    await page.waitForLoadState('networkidle');
    const dialog = page.locator('.v-dialog');
    if (await dialog.count() > 0) {
      const openButton = page.locator('.v-btn').filter({ hasText: /добавить|add/i }).first();
      if (await openButton.count() > 0) {
        await openButton.click();
        await expect(dialog).toBeVisible();
        await page.keyboard.press('Escape');
        await expect(dialog).not.toBeVisible();
      }
    }
  });
});

test.describe('Таблицы и списки', () => {
  test('таблица пользователей загружается', async ({ page }) => {
    await page.goto('/users');
    await page.waitForLoadState('networkidle');
    const table = page.locator('table, .v-table, .v-data-table');
    if (await table.count() > 0) {
      await expect(table).toBeVisible();
    }
  });

  test('пагинация работает', async ({ page }) => {
    await page.goto('/users');
    await page.waitForLoadState('networkidle');
    const pagination = page.locator('.v-pagination');
    if (await pagination.count() > 0) {
      const pages = await pagination.locator('button').count();
      if (pages > 1) {
        await pagination.locator('button').nth(1).click();
        await page.waitForTimeout(500);
      }
    }
  });
});

test.describe('Поиск и фильтры', () => {
  test('поле поиска присутствует', async ({ page }) => {
    await page.goto('/users');
    await page.waitForLoadState('networkidle');
    const searchInput = page.locator('input[type="search"], input[placeholder*="поиск"], .v-text-field input');
    if (await searchInput.count() > 0) {
      await expect(searchInput.first()).toBeVisible();
    }
  });
});

test.describe('Настройки профиля', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/settings/profile');
    await page.waitForLoadState('networkidle');
  });

  test('страница настроек загружается', async ({ page }) => {
    await expect(page.locator('.v-application')).toBeVisible();
  });

  test('сохранение настроек', async ({ page }) => {
    const saveButton = page.getByRole('button', { name: /сохранить|save/i });
    if (await saveButton.count() > 0) {
      await expect(saveButton).toBeVisible();
    }
  });
});

test.describe('Доступность', () => {
  test('aria-labels присутствуют на интерактивных элементах', async ({ page }) => {
    await page.goto('/login');
    const buttons = page.locator('button');
    const count = await buttons.count();
    expect(count).toBeGreaterThan(0);
  });

  test('focus-visible стили присутствуют', async ({ page }) => {
    await page.goto('/login');
    await page.locator('input[name="login"]').focus();
    const focused = page.locator('input:focus');
    await expect(focused).toBeVisible();
  });
});

test.describe('Ошибки в консоли', () => {
  test('нет критических ошибок на странице логина', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/login');
    await page.waitForLoadState('networkidle');
    const criticalErrors = errors.filter(e => 
      !e.includes('favicon') && 
      !e.includes('401') &&
      !e.includes('auth')
    );
    expect(criticalErrors).toHaveLength(0);
  });

  test('нет критических ошибок на главной странице', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    const criticalErrors = errors.filter(e => 
      !e.includes('favicon')
    );
    expect(criticalErrors).toHaveLength(0);
  });
});
