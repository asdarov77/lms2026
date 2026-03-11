const puppeteer = require('puppeteer');
const { Pool } = require('pg');

const config = {
  baseUrl: process.env.APP_URL || 'http://localhost:8000',
  db: {
    host: process.env.DB_HOST || '127.0.0.1',
    port: parseInt(process.env.DB_PORT || '5432'),
    database: process.env.DB_DATABASE || 'lms2025_db',
    user: process.env.DB_USERNAME || 'lms',
    password: process.env.DB_PASSWORD || 'lms',
  },
  credentials: {
    adminEmail: process.env.TEST_ADMIN_EMAIL || 'admin@test.com',
    adminPassword: process.env.TEST_ADMIN_PASSWORD || 'password123',
  },
  screenshotDir: './e2e/test-results/puppeteer',
};

const pool = new Pool(config.db);

async function query(text, params = []) {
  const result = await pool.query(text, params);
  return result.rows;
}

async function takeScreenshot(page, name) {
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  const filename = `${config.screenshotDir}/${name}-${timestamp}.png`;
  await page.screenshot({ path: filename, fullPage: true });
  return filename;
}

async function login(page) {
  await page.goto(`${config.baseUrl}/login`);
  await page.waitForSelector('[data-testid="email"]');
  
  await page.type('[data-testid="email"]', config.credentials.adminEmail);
  await page.type('[data-testid="password"]', config.credentials.adminPassword);
  await page.click('[data-testid="btn-submit"]');
  
  await page.waitForURL(`${config.baseUrl}/dashboard`, { timeout: 30000 });
  console.log('Logged in successfully');
}

async function deleteCourse(id) {
  await query('DELETE FROM courses WHERE id = $1', [id]);
}

async function runCourseCrudTest() {
  let browser = null;
  let courseId = null;
  
  try {
    console.log('Starting Course CRUD test with Puppeteer...');
    
    browser = await puppeteer.launch({
      headless: 'new',
      args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });
    
    const page = await browser.newPage();
    
    await login(page);
    
    const courseName = `Course ${Date.now()}`;
    console.log(`Creating course: ${courseName}`);
    
    await page.goto(`${config.baseUrl}/courses/create`);
    await page.waitForSelector('[data-testid="name"]');
    
    await page.type('[data-testid="name"]', courseName);
    await page.type('[data-testid="description"]', 'Test Description');
    
    await page.click('[data-testid="btn-save"]');
    
    await page.waitForSelector('.v-snackbar__wrapper', { timeout: 10000 });
    console.log('Course created, checking database...');
    
    const courseInDb = await query(
      'SELECT * FROM courses WHERE name = $1',
      [courseName]
    );
    
    if (!courseInDb[0]) {
      throw new Error('Course not found in database after creation');
    }
    
    courseId = courseInDb[0].id;
    console.log(`Course created with ID: ${courseId}`);
    
    console.log('Updating course...');
    await page.goto(`${config.baseUrl}/courses/${courseId}/edit`);
    await page.waitForSelector('[data-testid="name"]');
    
    await page.type('[data-testid="name"]', ' Updated', { delay: 100 });
    await page.click('[data-testid="btn-save"]');
    
    await page.waitForSelector('.v-snackbar__wrapper', { timeout: 10000 });
    
    const updatedCourse = await query(
      'SELECT * FROM courses WHERE id = $1',
      [courseId]
    );
    
    console.log(`Updated course name: ${updatedCourse[0].name}`);
    
    console.log('Deleting course...');
    await page.goto(`${config.baseUrl}/courses`);
    
    const deleteBtn = await page.$(`[data-testid="btn-delete-${courseId}"]`);
    if (deleteBtn) {
      await deleteBtn.click();
      await page.waitForSelector('[data-testid="btn-confirm"]');
      await page.click('[data-testid="btn-confirm"]');
      await page.waitForSelector('.v-snackbar__wrapper', { timeout: 10000 });
    }
    
    const deletedCourse = await query(
      'SELECT * FROM courses WHERE id = $1',
      [courseId]
    );
    
    if (deletedCourse[0]) {
      throw new Error('Course still exists in database after deletion');
    }
    
    console.log('Course CRUD test PASSED');
    
  } catch (error) {
    console.error('Course CRUD test FAILED:', error);
    throw error;
  } finally {
    if (browser) {
      await browser.close();
    }
    if (courseId) {
      await deleteCourse(courseId);
    }
    await pool.end();
  }
}

runCourseCrudTest()
  .then(() => {
    console.log('All tests completed successfully');
    process.exit(0);
  })
  .catch((error) => {
    console.error('Test failed:', error);
    process.exit(1);
  });
