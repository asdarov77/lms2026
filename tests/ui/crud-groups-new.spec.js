import { test, expect } from '@playwright/test';

test.describe('CRUD Groups Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="fio"]', 'Администратор');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });
  });

  test('should list all groups', async ({ page }) => {
    // Navigate to groups page
    await page.goto('http://localhost:8000/groups');

    // Verify groups list is visible
    const groupsList = await page.locator('.groups-list, table, [data-testid="groups-list"]').first();
    await expect(groupsList).toBeVisible();
  });

  test('should create a new group', async ({ page }) => {
    // Navigate to groups page
    await page.goto('http://localhost:8000/groups');

    // Click "Add Group" button
    await page.click('button:has-text("Добавить"), .add-group-button, [data-testid="add-group"]');

    // Fill group form
    const groupName = 'Test Group ' + Date.now();
    await page.fill('input[name="name"]', groupName);
    await page.fill('textarea[name="description"]', 'Test group description');

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should edit an existing group', async ({ page }) => {
    // Navigate to groups page
    await page.goto('http://localhost:8000/groups');

    // Find first group and click edit button
    const firstGroup = await page.locator('table tbody tr').first();
    await firstGroup.locator('.edit-button, button:has-text("Редактировать")').click();

    // Update group data
    await page.fill('input[name="name"]', 'Updated Group ' + Date.now());

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should delete a group', async ({ page }) => {
    // Navigate to groups page
    await page.goto('http://localhost:8000/groups');

    // Find first group and click delete button
    const firstGroup = await page.locator('table tbody tr').first();
    await firstGroup.locator('.delete-button, button:has-text("Удалить")').click();

    // Confirm deletion
    await page.click('button:has-text("Да"), .confirm-button');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should assign users to group', async ({ page }) => {
    // Navigate to groups page
    await page.goto('http://localhost:8000/groups');

    // Find first group and click edit button
    const firstGroup = await page.locator('table tbody tr').first();
    await firstGroup.locator('.edit-button, button:has-text("Редактировать")').click();

    // Select users to assign
    await page.check('.user-checkbox input[type="checkbox"]');

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should view group details', async ({ page }) => {
    // Navigate to groups page
    await page.goto('http://localhost:8000/groups');

    // Find first group and click view button
    const firstGroup = await page.locator('table tbody tr').first();
    await firstGroup.locator('.view-button, button:has-text("Просмотреть")').click();

    // Verify group details page is visible
    const groupDetails = await page.locator('.group-details, [data-testid="group-details"]').first();
    await expect(groupDetails).toBeVisible();
  });
});
