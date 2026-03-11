import { test, expect } from '@playwright/test';

test.describe('Форма логина - полное покрытие', () => {
  
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
  });

  test('1. Отображение всех элементов формы', async ({ page }) => {
    await expect(page.locator('text=Форма авторизации')).toBeVisible();
    await expect(page.locator('input[name="login"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Вход' })).toBeVisible();
  });

  test('2. Валидация: кнопка disabled при пустых полях', async ({ page }) => {
    const button = page.getByRole('button', { name: 'Вход' });
    await expect(button).toBeDisabled();
  });

  test('3. Валидация: только FIO заполнен - кнопка disabled', async ({ page }) => {
    await page.locator('input[name="login"]').fill('Тестовый Пользователь');
    const button = page.getByRole('button', { name: 'Вход' });
    await expect(button).toBeDisabled();
  });

  test('4. Валидация: оба поля заполнены - кнопка enabled', async ({ page }) => {
    await page.locator('input[name="login"]').fill('Тестовый Пользователь');
    await page.locator('input[name="password"]').fill('123456');
    const button = page.getByRole('button', { name: 'Вход' });
    await expect(button).toBeEnabled();
  });

  test('5. Переключение видимости пароля', async ({ page }) => {
    const passwordInput = page.locator('input[name="password"]');
    await expect(passwordInput).toHaveAttribute('type', 'password');
    
    const toggleButton = page.locator('.mdi-eye-off, .mdi-eye').first();
    await toggleButton.click();
    await expect(passwordInput).toHaveAttribute('type', 'text');
    
    await toggleButton.click();
    await expect(passwordInput).toHaveAttribute('type', 'password');
  });

  test('6. Клавиатурная навигация: Tab по полям', async ({ page }) => {
    await page.keyboard.press('Tab');
    const focusedInput = page.locator('input:focus');
    await expect(focusedInput).toHaveAttribute('name', 'login');
  });

  test('7. FIO: поддержка кириллицы', async ({ page }) => {
    await page.locator('input[name="login"]').fill('Иванов Иван Иванович');
    await expect(page.locator('input[name="login"]')).toHaveValue('Иванов Иван Иванович');
  });

  test('8. FIO: поддержка латиницы', async ({ page }) => {
    await page.locator('input[name="login"]').fill('John Doe');
    await expect(page.locator('input[name="login"]')).toHaveValue('John Doe');
  });

  test('9. Доступность: required атрибут', async ({ page }) => {
    const loginRequired = await page.locator('input[name="login"]').getAttribute('required');
    expect(loginRequired).not.toBeNull();
  });

  test('10. Нет критических ошибок в консоли', async ({ page }) => {
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
});

test.describe('Навигация - главное меню', () => {
  test('главное меню доступно после авторизации', async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
    await page.locator('input[name="login"]').fill('admin');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: 'Вход' }).click();
    await page.waitForTimeout(3000);
    
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const appLayout = page.locator('.v-application, .app-layout');
    await expect(appLayout).toBeVisible();
  });
});
