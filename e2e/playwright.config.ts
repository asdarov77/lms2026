import { defineConfig, devices, type PlaywrightTestConfig, type TestInfo } from '@playwright/test';
import path from 'path';
import fs from 'fs';

const createDirectory = (dirPath: string): void => {
  if (!fs.existsSync(dirPath)) {
    fs.mkdirSync(dirPath, { recursive: true });
  }
};

const logsDir = path.join(process.cwd(), 'test-results', 'logs');
const screenshotsDir = path.join(process.cwd(), 'test-results', 'screenshots');
const tracesDir = path.join(process.cwd(), 'test-results', 'traces');

[logsDir, screenshotsDir, tracesDir].forEach(createDirectory);

export default defineConfig<PlaywrightTestConfig>({
  testDir: './tests',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [
    ['html', { outputFolder: 'playwright-report', open: 'never' }],
    ['json', { outputFile: 'test-results/results.json' }],
    ['list'],
    ['dot']
  ],
  
  use: {
    baseURL: process.env.APP_URL || 'http://localhost:8000',
    trace: {
      mode: 'retain-on-failure',
    },
    screenshot: {
      mode: 'only-on-failure',
      fullPage: true
    },
    video: {
      mode: 'retain-on-failure',
      size: { width: 1280, height: 720 }
    },
    actionTimeout: 10000,
    navigationTimeout: 30000,
    testIdAttribute: 'data-testid',
    viewport: { width: 1280, height: 720 },
    locale: 'ru-RU',
    timezoneId: 'Europe/Moscow',
    
    launchOptions: {
      slowMo: process.env.SLOW_MO ? parseInt(process.env.SLOW_MO) : 0,
    },
  },

  projects: [
    {
      name: 'chromium',
      use: { 
        ...devices['Desktop Chrome'],
        launchOptions: {
          args: ['--disable-dev-shm-usage', '--no-sandbox']
        }
      },
    },
    {
      name: 'firefox',
      use: { 
        ...devices['Desktop Firefox'],
        launchOptions: {
          args: ['--disable-dev-shm-usage', '--no-sandbox']
        }
      },
    },
    {
      name: 'webkit',
      use: { ...devices['Desktop Safari'] },
    },
    {
      name: 'Mobile Chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'Mobile Safari',
      use: { ...devices['iPhone 12'] },
    },
  ],

  webServer: {
    command: 'php artisan serve --host=127.0.0.1 --port=8000',
    url: 'http://localhost:8000',
    reuseExistingServer: !process.env.CI,
    timeout: 120000,
  },

  globalSetup: require.resolve('./e2e/global-setup'),
  globalTeardown: require.resolve('./e2e/global-teardown'),

  timeout: 60000,
  expect: {
    timeout: 10000,
    toHaveScreenshot: {
      maxDiffPixelRatio: 0.1,
    },
  },

  outputDir: 'test-results',
});
