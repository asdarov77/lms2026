import { test, expect } from '@playwright/test';
import { testData, type TestCourse } from '../../helpers/testData';
import { 
  query, 
  verifyRecord, 
  verifyRecordByField, 
  verifyFieldValue,
  execute 
} from '../../helpers/database';

const baseUrl = process.env.APP_URL || 'http://localhost:8000';

test.describe('Course CRUD', () => {
  let testCourse: TestCourse;
  let courseId: number;

  test.beforeEach(async ({ page }) => {
    testCourse = testData.course();
    
    await page.goto(`${baseUrl}/login`);
    await page.fill('[data-testid="email"]', process.env.TEST_ADMIN_EMAIL || 'admin@test.com');
    await page.fill('[data-testid="password"]', process.env.TEST_ADMIN_PASSWORD || 'password123');
    await page.click('[data-testid="btn-submit"]');
    await page.waitForURL(`${baseUrl}/dashboard`, { timeout: 30000 });
  });

  test.describe('Create Course', () => {
    test('should create new course with valid data', async ({ page }) => {
      await page.goto(`${baseUrl}/courses/create`);
      
      await page.fill('[data-testid="name"]', testCourse.name);
      await page.fill('[data-testid="description"]', testCourse.description);
      
      if (await page.locator('[data-testid="code"]').isVisible()) {
        await page.fill('[data-testid="code"]', testCourse.code || '');
      }
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('.v-snackbar__wrapper, [data-testid="toast"]')).toBeVisible({ timeout: 10000 });
      
      const courseInDb = await verifyRecordByField<{ id: number; name: string; description: string }>(
        'courses', 
        'name', 
        testCourse.name
      );
      
      expect(courseInDb).not.toBeNull();
      expect(courseInDb?.name).toBe(testCourse.name);
      expect(courseInDb?.description).toBe(testCourse.description);
      
      courseId = courseInDb?.id || 0;
    });

    test('should validate required fields on create', async ({ page }) => {
      await page.goto(`${baseUrl}/courses/create`);
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('text=required, text=Обязательно')).toBeVisible();
    });
  });

  test.describe('Read Course', () => {
    test('should display course list', async ({ page }) => {
      await page.goto(`${baseUrl}/courses`);
      
      await expect(page.locator('.v-data-table, [data-testid="course-table"]')).toBeVisible();
    });

    test('should display course details', async ({ page }) => {
      const course = await createTestCourseInDb(testCourse);
      
      await page.goto(`${baseUrl}/courses/${course.id}`);
      
      await expect(page.locator(`text=${course.name}`)).toBeVisible();
    });
  });

  test.describe('Update Course', () => {
    test('should update course successfully', async ({ page }) => {
      const course = await createTestCourseInDb(testCourse);
      const updatedData = testData.updateFields(testCourse) as TestCourse;
      
      await page.goto(`${baseUrl}/courses/${course.id}/edit`);
      
      await page.fill('[data-testid="name"]', updatedData.name);
      await page.fill('[data-testid="description"]', updatedData.description);
      
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('.v-snackbar__wrapper, [data-testid="toast"]')).toBeVisible({ timeout: 10000 });
      
      const updatedCourse = await verifyRecord<{ id: number; name: string; description: string }>(
        'courses', 
        course.id
      );
      
      expect(updatedCourse?.name).toBe(updatedData.name);
    });

    test('should validate required fields on update', async ({ page }) => {
      const course = await createTestCourseInDb(testCourse);
      
      await page.goto(`${baseUrl}/courses/${course.id}/edit`);
      
      await page.fill('[data-testid="name"]', '');
      await page.click('[data-testid="btn-save"]');
      
      await expect(page.locator('text=required, text=Обязательно')).toBeVisible();
    });
  });

  test.describe('Delete Course', () => {
    test('should delete course after confirmation', async ({ page }) => {
      const course = await createTestCourseInDb(testCourse);
      
      await page.goto(`${baseUrl}/courses`);
      
      const deleteBtn = page.locator(`[data-testid="btn-delete-${course.id}"]`);
      await deleteBtn.click();
      
      await page.click('[data-testid="btn-confirm"]');
      
      await expect(page.locator('.v-snackbar__wrapper, [data-testid="toast"]')).toBeVisible({ timeout: 10000 });
      
      const deletedCourse = await verifyRecord('courses', course.id);
      expect(deletedCourse).toBeNull();
    });
  });
});

test.describe('Course Filters & Pagination', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${baseUrl}/login`);
    await page.fill('[data-testid="email"]', process.env.TEST_ADMIN_EMAIL || 'admin@test.com');
    await page.fill('[data-testid="password"]', process.env.TEST_ADMIN_PASSWORD || 'password123');
    await page.click('[data-testid="btn-submit"]');
    await page.waitForURL(`${baseUrl}/dashboard`, { timeout: 30000 });
  });

  test('should filter courses by name', async ({ page }) => {
    await page.goto(`${baseUrl}/courses`);
    
    const searchInput = page.locator('[data-testid="search"]');
    if (await searchInput.isVisible()) {
      const course = testData.course();
      await createTestCourseInDb(course);
      
      await searchInput.fill(course.name);
      await page.waitForTimeout(500);
      
      await expect(page.locator(`text=${course.name}`)).toBeVisible();
    }
  });

  test('should paginate course list', async ({ page }) => {
    await page.goto(`${baseUrl}/courses`);
    
    const pagination = page.locator('.v-pagination');
    if (await pagination.isVisible()) {
      await page.click('.v-pagination__item >> nth=1');
      await page.waitForTimeout(500);
    }
  });
});

async function createTestCourseInDb(course: TestCourse): Promise<{ id: number } & TestCourse> {
  const result = await execute(
    `INSERT INTO courses (name, description, code, created_at, updated_at)
     VALUES ($1, $2, $3, NOW(), NOW())
     RETURNING id, name, description, code`,
    [course.name, course.description, course.code]
  );
  
  return {
    id: result.rows[0]?.id,
    name: course.name,
    description: course.description,
    code: course.code,
  };
}
