import { test, expect } from '@playwright/test';

test.describe('CRUD Topics and Materials Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('http://localhost:8000/login');
    await page.fill('input[name="fio"]', 'Администратор');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });
  });

  test('should list all topics in a course', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click view button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.view-button, button:has-text("Просмотреть")').click();

    // Verify topics list is visible
    const topicsList = await page.locator('.topics-list, [data-testid="topics-list"]').first();
    await expect(topicsList).toBeVisible();
  });

  test('should create a new topic', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click view button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.view-button, button:has-text("Просмотреть")').click();

    // Click "Add Topic" button
    await page.click('button:has-text("Добавить тему"), .add-topic-button, [data-testid="add-topic"]');

    // Fill topic form
    const topicTitle = 'Test Topic ' + Date.now();
    await page.fill('input[name="title"]', topicTitle);
    await page.fill('textarea[name="description"]', 'Test topic description');
    await page.fill('input[name="sort_order"]', '1');

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should edit an existing topic', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click view button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.view-button, button:has-text("Просмотреть")').click();

    // Find first topic and click edit button
    const firstTopic = await page.locator('.topic-item').first();
    await firstTopic.locator('.edit-button, button:has-text("Редактировать")').click();

    // Update topic data
    await page.fill('input[name="title"]', 'Updated Topic ' + Date.now());

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should delete a topic', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click view button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.view-button, button:has-text("Просмотреть")').click();

    // Find first topic and click delete button
    const firstTopic = await page.locator('.topic-item').first();
    await firstTopic.locator('.delete-button, button:has-text("Удалить")').click();

    // Confirm deletion
    await page.click('button:has-text("Да"), .confirm-button');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should create a new material', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click view button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.view-button, button:has-text("Просмотреть")').click();

    // Find first topic and click view button
    const firstTopic = await page.locator('.topic-item').first();
    await firstTopic.locator('.view-button, button:has-text("Просмотреть")').click();

    // Click "Add Material" button
    await page.click('button:has-text("Добавить материал"), .add-material-button, [data-testid="add-material"]');

    // Fill material form
    const materialTitle = 'Test Material ' + Date.now();
    await page.fill('input[name="title"]', materialTitle);
    await page.fill('textarea[name="content"]', 'Test material content');
    await page.fill('input[name="order"]', '1');
    await page.selectOption('select[name="type"]', 'text');

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should edit an existing material', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click view button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.view-button, button:has-text("Просмотреть")').click();

    // Find first topic and click view button
    const firstTopic = await page.locator('.topic-item').first();
    await firstTopic.locator('.view-button, button:has-text("Просмотреть")').click();

    // Find first material and click edit button
    const firstMaterial = await page.locator('.material-item').first();
    await firstMaterial.locator('.edit-button, button:has-text("Редактировать")').click();

    // Update material data
    await page.fill('input[name="title"]', 'Updated Material ' + Date.now());

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should delete a material', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click view button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.view-button, button:has-text("Просмотреть")').click();

    // Find first topic and click view button
    const firstTopic = await page.locator('.topic-item').first();
    await firstTopic.locator('.view-button, button:has-text("Просмотреть")').click();

    // Find first material and click delete button
    const firstMaterial = await page.locator('.material-item').first();
    await firstMaterial.locator('.delete-button, button:has-text("Удалить")').click();

    // Confirm deletion
    await page.click('button:has-text("Да"), .confirm-button');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });

  test('should reorder topics', async ({ page }) => {
    // Navigate to courses page
    await page.goto('http://localhost:8000/courses');

    // Find first course and click view button
    const firstCourse = await page.locator('table tbody tr').first();
    await firstCourse.locator('.view-button, button:has-text("Просмотреть")').click();

    // Click "Reorder Topics" button
    await page.click('button:has-text("Изменить порядок"), .reorder-button, [data-testid="reorder-topics"]');

    // Drag and drop topic to new position
    const firstTopic = await page.locator('.topic-item').first();
    const secondTopic = await page.locator('.topic-item').nth(1);
    
    // Simulate drag and drop
    await firstTopic.dragTo(secondTopic);

    // Submit form
    await page.click('button[type="submit"]');

    // Verify success message
    const successMessage = await page.locator('.success-message, .alert-success').first();
    await expect(successMessage).toBeVisible();
  });
});
