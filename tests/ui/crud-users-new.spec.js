import { test, expect } from '@playwright/test';

test.describe('CRUD Users Tests', () => {
  let authToken: string;

  test.beforeAll(async ({ request }) => {
    // Login and get auth token
    const response = await request.post('http://localhost:8000/api/login', {
      data: {
        fio: 'Администратор',
        password: 'admin123'
      }
    });
    authToken = response.token;
  });

  test('should list all users', async ({ page }) => {
    // Login first
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="fio"]', 'Администратор');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });

    // Navigate to users page
    await page.goto('http://localhost:8000/users');

    // Verify users list is visible
    const usersList = await page.locator('.users-list, table, [data-testid="users-list"]').first();
    await expect(usersList).toBeVisible();
  });

  test('should create a new user', async ({ page }) => {
    // Login first
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="fio"]', 'Администратор');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });

    // Navigate to users page
    await page.goto('http://localhost:8000/users');

    // Click "Add User" button
    await page.click('button:has-text("Добавить"), .add-user-button, [data-testid="add-user"]');

    // Fill user form
    await page.fill('input[name="fio"]', 'Test User ' + Date.now());
    await page.fill('input[name="email"]', 'test' + Date.now() + '@example.com');
    await page.fill('input[name="password"]', 'password123');
    await page.selectOption('select[name="group_id"]', '1');

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should edit an existing user', async ({ page }) => {
    // Login first
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="fio"]', 'Администратор');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });

    // Navigate to users page
    await page.goto('http://localhost:8000/users');

    // Find first user and click edit button
    const firstUser = await page.locator('table tbody tr').first();
    await firstUser.locator('.edit-button, button:has-text("Редактировать")').click();

    // Update user data
    await page.fill('input[name="fio"]', 'Updated User ' + Date.now());

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should delete a user', async ({ page }) => {
    // Login first
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="fio"]', 'Администратор');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });

    // Navigate to users page
    await page.goto('http://localhost:8000/users');

    // Find first user and click delete button
    const firstUser = await page.locator('table tbody tr').first();
    await firstUser.locator('.delete-button, button:has-text("Удалить")').click();

    // Confirm deletion
    await page.click('button:has-text("Да"), .confirm-button');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should search users by name', async ({ page }) => {
    // Login first
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="fio"]', 'Администратор');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });

    // Navigate to users page
    await page.goto('http://localhost:8000/users');

    // Use search functionality
    await page.fill('input[name="search"], .search-input', 'Администратор');
    await page.press('input[name="search"], .search-input', 'Enter');

    // Verify search results
    const searchResults = await page.locator('table tbody tr').all();
    await expect(searchResults.length).toBeGreaterThan(0);
  });
});
