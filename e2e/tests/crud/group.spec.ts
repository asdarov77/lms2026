import { test, expect } from '@playwright/test';
import { testData, type TestGroup } from '../../helpers/testData';
import { 
  verifyRecord, 
  verifyRecordByField, 
  execute 
} from '../../helpers/database';

const baseUrl = process.env.APP_URL || 'http://localhost:8000';

test.describe('Group CRUD', () => {
  let testGroup: TestGroup;
  let groupId: number;

  test.beforeEach(async ({ page }) => {
    testGroup = testData.group();
    
    await page.goto(`${baseUrl}/login`);
    await page.fill('[data-testid="email"]', process.env.TEST_ADMIN_EMAIL || 'admin@test.com');
    await page.fill('[data-testid="password"]', process.env.TEST_ADMIN_PASSWORD || 'password123');
    await page.click('[data-testid="btn-submit"]');
    await page.waitForURL(`${baseUrl}/dashboard`, { timeout: 30000 });
  });

  test.describe('Create Group', () => {
    test('should create new group with valid data', async ({ page }) => {
      await page.goto(`${baseUrl}/groups/create`);
      
      await page.fill('[data-testid="name"]', testGroup.name);
      
      if (await page.locator('[data-testid="description"]').isVisible()) {
        await page.fill('[data-testid="description"]', testGroup.description || '');
      }
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('.v-snackbar__wrapper, [data-testid="toast"]')).toBeVisible({ timeout: 10000 });
      
      const groupInDb = await verifyRecordByField<{ id: number; name: string }>(
        'groups', 
        'name', 
        testGroup.name
      );
      
      expect(groupInDb).not.toBeNull();
      expect(groupInDb?.name).toBe(testGroup.name);
      
      groupId = groupInDb?.id || 0;
    });

    test('should validate required name field', async ({ page }) => {
      await page.goto(`${baseUrl}/groups/create`);
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('text=required, text=Обязательно')).toBeVisible();
    });
  });

  test.describe('Read Group', () => {
    test('should display group list', async ({ page }) => {
      await page.goto(`${baseUrl}/groups`);
      
      await expect(page.locator('.v-data-table, [data-testid="group-table"]')).toBeVisible();
    });

    test('should display group details', async ({ page }) => {
      const group = await createTestGroupInDb(testGroup);
      
      await page.goto(`${baseUrl}/groups/${group.id}`);
      
      await expect(page.locator(`text=${group.name}`)).toBeVisible();
    });
  });

  test.describe('Update Group', () => {
    test('should update group successfully', async ({ page }) => {
      const group = await createTestGroupInDb(testGroup);
      const updatedData = { ...testGroup, name: testData.group().name };
      
      await page.goto(`${baseUrl}/groups/${group.id}/edit`);
      
      await page.fill('[data-testid="name"]', updatedData.name);
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('.v-snackbar__wrapper, [data-testid="toast"]')).toBeVisible({ timeout: 10000 });
      
      const updatedGroup = await verifyRecord<{ id: number; name: string }>(
        'groups', 
        group.id
      );
      
      expect(updatedGroup?.name).toBe(updatedData.name);
    });
  });

  test.describe('Delete Group', () => {
    test('should delete group after confirmation', async ({ page }) => {
      const group = await createTestGroupInDb(testGroup);
      
      await page.goto(`${baseUrl}/groups`);
      
      const deleteBtn = page.locator(`[data-testid="btn-delete-${group.id}"]`);
      await deleteBtn.click();
      
      await page.click('[data-testid="btn-confirm"]');
      
      await expect(page.locator('.v-snackbar__wrapper, [data-testid="toast"]')).toBeVisible({ timeout: 10000 });
      
      const deletedGroup = await verifyRecord('groups', group.id);
      expect(deletedGroup).toBeNull();
    });
  });

  test.describe('Group Users Management', () => {
    test('should add users to group', async ({ page }) => {
      const group = await createTestGroupInDb(testGroup);
      
      await page.goto(`${baseUrl}/groups/${group.id}/edit`);
      
      const addUserBtn = page.locator('[data-testid="btn-add-users"]');
      if (await addUserBtn.isVisible()) {
        await addUserBtn.click();
        
        await page.click('[data-testid="user-checkbox-1"]');
        await page.click('[data-testid="btn-confirm"]');
        
        await expect(page.locator('.v-snackbar__wrapper')).toBeVisible({ timeout: 10000 });
      }
    });
  });
});

async function createTestGroupInDb(group: TestGroup): Promise<{ id: number } & TestGroup> {
  const result = await execute(
    `INSERT INTO groups (name, description, created_at, updated_at)
     VALUES ($1, $2, NOW(), NOW())
     RETURNING id, name, description`,
    [group.name, group.description]
  );
  
  return {
    id: result.rows[0]?.id,
    name: group.name,
    description: group.description,
  };
}
