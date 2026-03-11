import { test as base, type Page } from '@playwright/test';
import { query, execute, truncateTables, verifyRecordByField } from '../helpers/database';
import { testData, type TestUser } from '../helpers/testData';

interface TestFixtures {
  authenticatedPage: Page;
  testUser: TestUser;
  adminUser: TestUser;
  dbCleaner: () => Promise<void>;
}

export const test = base.extend<TestFixtures>({
  authenticatedPage: async ({ page }, use) => {
    const user = testData.user();
    
    await page.goto('/login');
    await page.fill('[data-testid="email"]', user.email);
    await page.fill('[data-testid="password"]', user.password);
    await page.click('[data-testid="btn-submit"]');
    
    await page.waitForURL('/dashboard', { timeout: 30000 });
    
    await use(page);
  },
  
  testUser: async ({}, use) => {
    const user = testData.user();
    
    const createdUser = await createTestUser(user);
    await use(createdUser);
    
    await cleanupTestUser(createdUser.email);
  },
  
  adminUser: async ({}, use) => {
    const user = testData.admin();
    user.role = 'admin';
    
    const createdUser = await createTestUser(user);
    await use(createdUser);
    
    await cleanupTestUser(createdUser.email);
  },
  
  dbCleaner: async ({}, use) => {
    const cleaner = async () => {
      await truncateTestData();
    };
    
    await use(cleaner);
    
    await truncateTestData();
  },
});

async function createTestUser(user: TestUser): Promise<TestUser> {
  const hashedPassword = await hashPassword(user.password);
  
  const result = await execute(
    `INSERT INTO users (email, password, fio, role, created_at, updated_at)
     VALUES ($1, $2, $3, $4, NOW(), NOW())
     RETURNING id, email`,
    [user.email, hashedPassword, user.fio, user.role || 'student']
  );
  
  return {
    ...user,
    id: result.rows[0]?.id,
  };
}

async function cleanupTestUser(email: string): Promise<void> {
  try {
    await execute('DELETE FROM users WHERE email = $1', [email]);
  } catch (error) {
    console.error('Failed to cleanup test user:', error);
  }
}

async function truncateTestData(): Promise<void> {
  try {
    await truncateTables([
      'users',
      'courses',
      'groups',
      'categories',
      'user_courses',
      'group_user',
      'progress',
    ]);
  } catch (error) {
    console.error('Failed to truncate tables:', error);
  }
}

async function hashPassword(password: string): Promise<string> {
  return password;
}
