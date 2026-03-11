import { test, expect } from '@playwright/test';
import { testData, type TestUser } from '../../helpers/testData';
import { 
  verifyRecord, 
  verifyRecordByField, 
  execute 
} from '../../helpers/database';

const baseUrl = process.env.APP_URL || 'http://localhost:8000';

test.describe('User CRUD', () => {
  let testUser: TestUser;
  let userId: number;

  test.beforeEach(async ({ page }) => {
    testUser = testData.user();
    
    await page.goto(`${baseUrl}/login`);
    await page.fill('[data-testid="email"]', process.env.TEST_ADMIN_EMAIL || 'admin@test.com');
    await page.fill('[data-testid="password"]', process.env.TEST_ADMIN_PASSWORD || 'password123');
    await page.click('[data-testid="btn-submit"]');
    await page.waitForURL(`${baseUrl}/dashboard`, { timeout: 30000 });
  });

  test.describe('Create User', () => {
    test('should create new user with valid data', async ({ page }) => {
      await page.goto(`${baseUrl}/users/create`);
      
      await page.fill('[data-testid="email"]', testUser.email);
      await page.fill('[data-testid="fio"]', testUser.fio);
      await page.fill('[data-testid="password"]', testUser.password);
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('.v-snackbar__wrapper, [data-testid="toast"]')).toBeVisible({ timeout: 10000 });
      
      const userInDb = await verifyRecordByField<{ id: number; email: string; fio: string }>(
        'users', 
        'email', 
        testUser.email
      );
      
      expect(userInDb).not.toBeNull();
      expect(userInDb?.email).toBe(testUser.email);
      expect(userInDb?.fio).toBe(testUser.fio);
      
      userId = userInDb?.id || 0;
    });

    test('should validate required fields', async ({ page }) => {
      await page.goto(`${baseUrl}/users/create`);
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('text=required, text=Обязательно')).toBeVisible();
    });

    test('should validate unique email', async ({ page }) => {
      const existingUser = testData.user();
      await createTestUserInDb(existingUser);
      
      await page.goto(`${baseUrl}/users/create`);
      
      await page.fill('[data-testid="email"]', existingUser.email);
      await page.fill('[data-testid="fio"]', testUser.fio);
      await page.fill('[data-testid="password"]', testUser.password);
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('text=already, text=существует')).toBeVisible();
    });
  });

  test.describe('Read User', () => {
    test('should display user list', async ({ page }) => {
      await page.goto(`${baseUrl}/users`);
      
      await expect(page.locator('.v-data-table, [data-testid="user-table"]')).toBeVisible();
    });

    test('should display user details', async ({ page }) => {
      const user = await createTestUserInDb(testUser);
      
      await page.goto(`${baseUrl}/users/${user.id}`);
      
      await expect(page.locator(`text=${user.fio}`)).toBeVisible();
    });
  });

  test.describe('Update User', () => {
    test('should update user successfully', async ({ page }) => {
      const user = await createTestUserInDb(testUser);
      const updatedFio = testData.user().fio;
      
      await page.goto(`${baseUrl}/users/${user.id}/edit`);
      
      await page.fill('[data-testid="fio"]', updatedFio);
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('.v-snackbar__wrapper, [data-testid="toast"]')).toBeVisible({ timeout: 10000 });
      
      const updatedUser = await verifyRecord<{ id: number; fio: string }>(
        'users', 
        user.id
      );
      
      expect(updatedUser?.fio).toBe(updatedFio);
    });

    test('should change user password', async ({ page }) => {
      const user = await createTestUserInDb(testUser);
      
      await page.goto(`${baseUrl}/users/${user.id}/chpass`);
      
      const newPassword = 'NewPassword123!';
      await page.fill('[data-testid="password"]', newPassword);
      await page.fill('[data-testid="password_confirmation"]', newPassword);
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('.v-snackbar__wrapper')).toBeVisible({ timeout: 10000 });
    });
  });

  test.describe('Delete User', () => {
    test('should delete user after confirmation', async ({ page }) => {
      const user = await createTestUserInDb(testUser);
      
      await page.goto(`${baseUrl}/users`);
      
      const deleteBtn = page.locator(`[data-testid="btn-delete-${user.id}"]`);
      await deleteBtn.click();
      
      await page.click('[data-testid="btn-confirm"]');
      
      await expect(page.locator('.v-snackbar__wrapper, [data-testid="toast"]')).toBeVisible({ timeout: 10000 });
      
      const deletedUser = await verifyRecord('users', user.id);
      expect(deletedUser).toBeNull();
    });
  });

  test.describe('User Permissions', () => {
    test('should change user role', async ({ page }) => {
      const user = await createTestUserInDb(testUser);
      
      await page.goto(`${baseUrl}/users/${user.id}/chrole`);
      
      await page.selectOption('[data-testid="role"]', 'admin');
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('.v-snackbar__wrapper')).toBeVisible({ timeout: 10000 });
      
      const updatedUser = await verifyRecord<{ id: number; role: string }>(
        'users', 
        user.id
      );
      
      expect(updatedUser?.role).toBe('admin');
    });
  });
});

async function createTestUserInDb(user: TestUser): Promise<{ id: number } & TestUser> {
  const result = await execute(
    `INSERT INTO users (email, password, fio, role, created_at, updated_at)
     VALUES ($1, $2, $3, $4, NOW(), NOW())
     RETURNING id, email, fio, role`,
    [user.email, user.password, user.fio, user.role || 'student']
  );
  
  return {
    id: result.rows[0]?.id,
    email: user.email,
    password: user.password,
    fio: user.fio,
    role: user.role,
  };
}
