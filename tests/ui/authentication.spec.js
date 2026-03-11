import { test, expect } from '@playwright/test';

test.describe('Authentication Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Navigate to the application
    await page.goto('http://localhost:8000');
  });

  test('should login with valid credentials', async ({ page }) => {
    // Find and fill login form
    await page.fill('input[name="fio"]', 'Администратор');
    await page.fill('input[name="password"]', 'admin123');
    
    // Submit login form
    await page.click('button[type="submit"]');
    
    // Verify successful login - should be redirected to dashboard or see user info
    await page.waitForURL('**/dashboard', { timeout: 5000 });
    
    // Verify user is logged in
    const userElement = await page.locator('.user-info, .user-name, [data-testid="user-info"]').first();
    await expect(userElement).toBeVisible();
  });

  test('should show error with invalid credentials', async ({ page }) => {
    // Find and fill login form with invalid credentials
    await page.fill('input[name="fio"]', 'InvalidUser');
    await page.fill('input[name="password"]', 'wrongpassword');
    
    // Submit login form
    await page.click('button[type="submit"]');
    
    // Verify error message is shown
    const errorMessage = await page.locator('.error-message, .alert, [data-testid="error"]').first();
    await expect(errorMessage).toBeVisible();
    await expect(errorMessage).toContainText('неверный пароль');
  });

  test('should logout successfully', async ({ page }) => {
    // Login first
    await page.fill('input[name="fio"]', 'Администратор');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });
    
    // Find and click logout button
    const logoutButton = await page.locator('button:has-text("Выход"), .logout-button, [data-testid="logout"]').first();
    await logoutButton.click();
    
    // Verify redirect to login page
    await page.waitForURL('**/login', { timeout: 5000 });
    
    // Verify login form is visible
    const loginForm = await page.locator('form').first();
    await expect(loginForm).toBeVisible();
  });

  test('should register new user', async ({ page }) => {
    // Navigate to registration page
    await page.goto('http://localhost:8000/register');
    
    // Fill registration form
    await page.fill('input[name="fio"]', 'Test User');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password123');
    
    // Submit registration form
    await page.click('button[type="submit"]');
    
    // Verify successful registration
    await page.waitForURL('**/dashboard', { timeout: 5000 });
    
    // Verify user is logged in
    const userElement = await page.locator('.user-info, .user-name, [data-testid="user-info"]').first();
    await expect(userElement).toBeVisible();
  });

  test('should validate required fields on login', async ({ page }) => {
    // Try to submit empty form
    await page.click('button[type="submit"]');
    
    // Verify validation errors
    const fioError = await page.locator('input[name="fio"] + .error, .invalid-feedback').first();
    const passwordError = await page.locator('input[name="password"] + .error, .invalid-feedback').first();
    
    await expect(fioError).toBeVisible();
    await expect(passwordError).toBeVisible();
  });

  test('should validate password confirmation on registration', async ({ page }) => {
    // Navigate to registration page
    await page.goto('http://localhost:8000/register');
    
    // Fill form with mismatching passwords
    await page.fill('input[name="fio"]', 'Test User');
    await page.fill('input[name="email"]', 'test@example.com');
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'differentpassword');
    
    // Submit registration form
    await page.click('button[type="submit"]');
    
    // Verify password confirmation error
    const confirmPasswordError = await page.locator('input[name="password_confirmation"] + .error, .invalid-feedback').first();
    await expect(confirmPasswordError).toBeVisible();
  });
});
