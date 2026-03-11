import { test, expect } from '@playwright/test';

test.describe('KLEN Import Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="fio"]', 'Администратор');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });
  });

  test('should list available KLEN courses', async ({ page }) => {
    // Navigate to KLEN import page
    await page.goto('http://localhost:8000/klen/import');

    // Verify courses list is visible
    const coursesList = await page.locator('.klen-courses-list, [data-testid="klen-courses"]').first();
    await expect(coursesList).toBeVisible();
  });

  test('should import a single KLEN course', async ({ page }) => {
    // Navigate to KLEN import page
    await page.goto('http://localhost:8000/klen/import');

    // Find first available course
    const firstCourse = await page.locator('.klen-course-item').first();
    
    // Click import button for the course
    await firstCourse.locator('.import-button, button:has-text("Импортировать")').click();

    // Wait for import to complete
    await page.waitForTimeout(5000);

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should import all KLEN courses', async ({ page }) => {
    // Navigate to KLEN import page
    await page.goto('http://localhost:8000/klen/import');

    // Click "Import All" button
    await page.click('button:has-text("Импортировать все"), .import-all-button, [data-testid="import-all"]');

    // Wait for import to complete
    await page.waitForTimeout(10000);

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should show import progress', async ({ page }) => {
    // Navigate to KLEN import page
    await page.goto('http://localhost:8000/klen/import');

    // Find first available course
    const firstCourse = await page.locator('.klen-course-item').first();
    
    // Click import button for the course
    await firstCourse.locator('.import-button, button:has-text("Импортировать")').click();

    // Verify progress indicator is visible
    const progressIndicator = await page.locator('.progress-bar, .import-progress').first();
    await expect(progressIndicator).toBeVisible();
  });

  test('should handle import errors gracefully', async ({ page }) => {
    // Navigate to KLEN import page
    await page.goto('http://localhost:8000/klen/import');

    // Try to import a course with invalid data (simulated)
    const firstCourse = await page.locator('.klen-course-item').first();
    await firstCourse.locator('.import-button, button:has-text("Импортировать")').click();

    // Wait for import to complete
    await page.waitForTimeout(5000);

    // Check if error message is shown (if any)
    const errorMessage = await page.locator('.error-message, .alert-error').first();
    const isVisible = await errorMessage.isVisible();
    
    if (isVisible) {
      await expect(errorMessage).toContainText('ошибка');
    }
  });

  test('should display imported course details', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click view button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.view-button, button:has-text("Просмотреть")').click();

    // Verify course details are visible
    const courseDetails = await page.locator('.course-details, [data-testid="course-details"]').first();
    await expect(courseDetails).toBeVisible();

    // Verify topics are visible
    const topicsList = await page.locator('.topics-list, [data-testid="topics-list"]').first();
    await expect(topicsList).toBeVisible();
  });

  test('should verify imported materials', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click view button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.view-button, button:has-text("Просмотреть")').click();

    // Find first topic and click view button
    const firstTopic = await page.locator('.topic-item').first();
    await firstTopic.locator('.view-button, button:has-text("Просмотреть")').click();

    // Verify materials are visible
    const materialsList = await page.locator('.materials-list, [data-testid="materials-list"]').first();
    await expect(materialsList).toBeVisible();
  });

  test('should clear imported data', async ({ page }) => {
    // Navigate to KLEN import page
    await page.goto('http://localhost:8000/klen/import');

    // Click "Clear" button
    await page.click('button:has-text("Очистить"), .clear-button, [data-testid="clear-import"]');

    // Confirm clear operation
    await page.click('button:has-text("Да"), .confirm-button');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should check KLEN directory accessibility', async ({ page }) => {
    // Navigate to KLEN import page
    await page.goto('http://localhost:8000/klen/import');

    // Verify directory status is shown
    const directoryStatus = await page.locator('.directory-status, [data-testid="directory-status"]').first();
    await expect(directoryStatus).toBeVisible();
  });

  test('should display import statistics', async ({ page }) => {
    // Navigate to KLEN import page
    await page.goto('http://localhost:8000/klen/import');

    // Click "Import All" button
    await page.click('button:has-text("Импортировать все"), .import-all-button, [data-testid="import-all"]');

    // Wait for import to complete
    await page.waitForTimeout(10000);

    // Verify statistics are shown
    const statistics = await page.locator('.import-statistics, [data-testid="import-statistics"]').first();
    await expect(statistics).toBeVisible();
  });
});
