import { test, expect } from '../../fixtures/logging';
import { Page } from '@playwright/test';

test.describe('MCP E2E Tests', () => {
  test('should display homepage correctly', async ({ page }) => {
    // Navigate to the app
    await page.goto('/');
    
    // Check page title
    await expect(page).toHaveTitle(/LMS2025/);
    
    // Verify main heading exists
    const heading = page.locator('h1').first();
    await expect(heading).toBeVisible();
  });

  test('should handle user authentication', async ({ page, consoleLogs, networkRequests }) => {
    // Navigate to login page
    await page.goto('/login');
    
    // Fill login form (adjust selectors based on your app)
    await page.fill('[data-testid="email"]', 'admin@example.com');
    await page.fill('[data-testid="password"]', 'password');
    await page.click('[data-testid="btn-submit"]');
    
    // Wait for navigation
    await page.waitForURL('/dashboard');
    
    // Verify successful login
    await expect(page).toHaveURL(/\/dashboard/);
    
    // Check for success toast or message
    const toast = page.locator('.v-snackbar__wrapper, [data-testid="toast"]').first();
    await expect(toast).toContainText('Успешно');
    
    console.log('📊 This test collected', consoleLogs.length, 'console messages');
    console.log('🌐 This test made', networkRequests.length, 'network requests');
  });

  test('should create a user via API and verify in UI', async ({ page }) => {
    // First, create a user via API
    const apiResponse = await page.request.post('/api/users', {
      data: {
        name: 'Test User',
        email: `test${Date.now()}@example.com`,
        password: 'password123',
      }
    });
    
    expect(apiResponse.ok()).toBeTruthy();
    const userData = await apiResponse.json();
    expect(userData.email).toContain('test@example.com');
    
    // Now verify the user appears in the UI user list
    await page.goto('/admin/users');
    
    // Search for the new user
    const searchInput = page.locator('[data-testid="search-users"]');
    await searchInput.fill(userData.email);
    
    // Wait for results
    await page.waitForTimeout(1000);
    
    // Verify user row exists
    const userRow = page.locator(`tr:has-text("${userData.email}")`);
    await expect(userRow).toBeVisible();
  });

  test('should load courses with pagination', async ({ page, networkRequests }) => {
    await page.goto('/courses');
    
    // Wait for courses to load
    await page.waitForLoadState('networkidle');
    
    // Check that course cards are visible
    const courseCards = page.locator('[data-testid="course-card"]');
    await expect(courseCards.first()).toBeVisible();
    
    // Test pagination
    const nextButton = page.locator('[data-testid="pagination-next"]');
    if (await nextButton.isEnabled()) {
      await nextButton.click();
      await page.waitForLoadState('networkidle');
      
      // Verify course list changed
      const firstCourseAfter = await courseCards.first().textContent();
      expect(firstCourseAfter).not.toBeNull();
    }
    
    console.log('Network requests during test:', networkRequests);
  });
});