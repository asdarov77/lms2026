import { test, expect } from '@playwright/test';

test.describe('CRUD Courses Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="fio"]', 'Администратор');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });
  });

  test('should list all courses', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Verify courses list is visible
    const coursesList = await page.locator('.courses-list, table, [data-testid="courses-list"]').first();
    await expect(coursesList).toBeVisible();
  });

  test('should create a new course', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Click "Add Course" button
    await page.click('button:has-text("Добавить"), .add-course-button, [data-testid="add-course"]');

    // Fill course form
    const courseTitle = 'Test Course ' + Date.now();
    await page.fill('input[name="title"]', courseTitle);
    await page.fill('textarea[name="short_description"]', 'Short description');
    await page.fill('textarea[name="long_description"]', 'Long description');
    await page.fill('input[name="path"]', 'test-course-' + Date.now());
    await page.selectOption('select[name="aircraft_id"]', '1');

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should edit an existing course', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click edit button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.edit-button, button:has-text("Редактировать")').click();

    // Update course data
    await page.fill('input[name="title"]', 'Updated Course ' + Date.now());

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should delete a course', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click delete button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.delete-button, button:has-text("Удалить")').click();

    // Confirm deletion
    await page.click('button:has-text("Да"), .confirm-button');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should view course details', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click view button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.view-button, button:has-text("Просмотреть")').click();

    // Verify course details page is visible
    const courseDetails = await page.locator('.course-details, [data-testid="course-details"]').first();
    await expect(courseDetails).toBeVisible();
  });

  test('should search courses by title', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Use search functionality
    await page.fill('input[name="search"], .search-input', 'Курс');
    await page.press('input[name="search"], .search-input', 'Enter');

    // Verify search results
    const searchResults = await page.locator('table tbody tr').all();
    await expect(searchResults.length).toBeGreaterThan(0);
  });

  test('should manage course visibility', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click edit button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.edit-button, button:has-text("Редактировать")').click();

    // Toggle visibility
    await page.check('input[name="visible"]');

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });
});
