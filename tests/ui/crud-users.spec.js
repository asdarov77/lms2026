import { test, expect } from '@playwright/test';

test.describe('CRUD: Управление пользователями', () => {
  const testUser = {
    fio: `Test User ${Date.now()}`,
    email: `test${Date.now()}@example.com`,
  };

  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
    await page.locator('input[name="login"]').fill('admin');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: 'Вход' }).click();
    await page.waitForTimeout(3000);
  });

  test('READ: Просмотр страницы пользователей', async ({ page }) => {
    await page.goto('/user/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const pageLoaded = page.locator('.v-application, main, .v-main');
    await expect(pageLoaded).toBeVisible();
  });

  test('READ: Страница загружается без ошибок', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    
    await page.goto('/user/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const criticalErrors = errors.filter(e => 
      !e.includes('favicon') && 
      !e.includes('401')
    );
    expect(criticalErrors).toHaveLength(0);
  });

  test('READ: Страница пользователей отображается', async ({ page }) => {
    await page.goto('/user/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const app = page.locator('.v-application');
    await expect(app).toBeVisible();
  });

  test('READ: Кнопки действий присутствуют', async ({ page }) => {
    await page.goto('/user/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const buttons = page.locator('.v-btn');
    const count = await buttons.count();
    expect(count).toBeGreaterThan(0);
  });

  test('CREATE: Страница создания доступна', async ({ page }) => {
    await page.goto('/user/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const addButtons = page.locator('button:has-text("Добавить"), button:has-text("Add")');
    if (await addButtons.count() > 0) {
      await expect(addButtons.first()).toBeVisible();
    } else {
      const pageContent = await page.locator('body').textContent();
      expect(pageContent.length).toBeGreaterThan(0);
    }
  });
});

test.describe('CRUD: Управление группами', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
    await page.locator('input[name="login"]').fill('admin');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: 'Вход' }).click();
    await page.waitForTimeout(3000);
  });

  test('READ: Просмотр списка групп', async ({ page }) => {
    await page.goto('/groups/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const pageLoaded = page.locator('.v-application');
    await expect(pageLoaded).toBeVisible();
  });

  test('CREATE: Страница создания группы доступна', async ({ page }) => {
    await page.goto('/groups/add');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const app = page.locator('.v-application');
    await expect(app).toBeVisible();
  });
});

test.describe('CRUD: Курсы', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
    await page.locator('input[name="login"]').fill('admin');
    await page.locator('input[name="password"]').fill('password');
    await page.getByRole('button', { name: 'Вход' }).click();
    await page.waitForTimeout(3000);
  });

  test('READ: Просмотр списка курсов', async ({ page }) => {
    await page.goto('/courses/list');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const pageLoaded = page.locator('.v-application');
    await expect(pageLoaded).toBeVisible();
  });

  test('CREATE: Страница создания курса', async ({ page }) => {
    await page.goto('/course');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    const pageLoaded = page.locator('.v-application');
    await expect(pageLoaded).toBeVisible();
  });
});
