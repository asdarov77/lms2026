import { Page, expect, type Locator } from '@playwright/test';

export interface AutoFixConfig {
  maxRetries: number;
  waitBetweenRetries: number;
  screenshotOnFailure: boolean;
}

const defaultConfig: AutoFixConfig = {
  maxRetries: 3,
  waitBetweenRetries: 2000,
  screenshotOnFailure: true,
};

interface RetryLog {
  testName: string;
  attempt: number;
  action: string;
  reason: string;
  result: 'success' | 'failed';
  timestamp: Date;
}

const retryLogs: RetryLog[] = [];

export const logRetryAttempt = (
  testName: string,
  attempt: number,
  action: string,
  reason: string,
  result: 'success' | 'failed'
): void => {
  retryLogs.push({
    testName,
    attempt,
    action,
    reason,
    result,
    timestamp: new Date(),
  });
  
  console.log(`[Retry] ${testName} | Attempt ${attempt} | ${action} | ${reason} | ${result}`);
};

export const getRetryLogs = (): RetryLog[] => retryLogs;

export const waitAndRetry = async <T>(
  fn: () => Promise<T>,
  options: Partial<AutoFixConfig> = {}
): Promise<T> => {
  const config = { ...defaultConfig, ...options };
  let lastError: Error | null = null;
  
  for (let attempt = 1; attempt <= config.maxRetries; attempt++) {
    try {
      return await fn();
    } catch (error) {
      lastError = error instanceof Error ? error : new Error(String(error));
      logRetryAttempt(
        'test' as string,
        attempt,
        'waitAndRetry',
        lastError.message,
        'failed'
      );
      
      if (attempt < config.maxRetries) {
        await new Promise(resolve => 
          setTimeout(resolve, config.waitBetweenRetries)
        );
      }
    }
  }
  
  throw lastError;
};

export const findElement = async (
  page: Page,
  selectors: string[],
  options?: { timeout?: number; state?: 'visible' | 'attached' }
): Promise<Locator | null> => {
  for (const selector of selectors) {
    try {
      const element = page.locator(selector).first();
      await element.waitFor({ 
        state: options?.state || 'visible', 
        timeout: options?.timeout || 5000 
      });
      return element;
    } catch {
      continue;
    }
  }
  return null;
};

export const fillForm = async (
  page: Page,
  fields: Record<string, string>
): Promise<void> => {
  for (const [field, value] of Object.entries(fields)) {
    const input = page.locator(`[data-testid="${field}"]`).first();
    await input.fill(value);
  }
};

export const submitForm = async (
  page: Page,
  submitButtonSelector: string = '[data-testid="btn-submit"]'
): Promise<void> => {
  await page.click(submitButtonSelector);
};

export const waitForToast = async (
  page: Page,
  expectedMessage?: string,
  timeout: number = 10000
): Promise<Locator> => {
  const toast = page.locator('.v-snackbar__wrapper, [data-testid="toast"]');
  
  if (expectedMessage) {
    await toast.filter({ hasText: expectedMessage }).waitFor({ timeout });
  } else {
    await toast.first().waitFor({ timeout });
  }
  
  return toast;
};

export const waitForRedirect = async (
  page: Page,
  expectedUrl: string,
  timeout: number = 30000
): Promise<void> => {
  await page.waitForURL(expectedUrl, { timeout });
};

export const waitForApiResponse = async (
  page: Page,
  urlPattern: string | RegExp,
  timeout: number = 30000
): Promise<{ request: unknown; response: unknown }> => {
  const [response] = await Promise.all([
    page.waitForResponse(urlPattern, { timeout }),
    page.waitForLoadState('networkidle'),
  ]);
  
  return {
    request: null,
    response,
  };
};

export const waitForElementState = async (
  page: Page,
  selector: string,
  state: 'visible' | 'hidden' | 'enabled' | 'disabled',
  timeout: number = 10000
): Promise<void> => {
  const element = page.locator(selector);
  
  switch (state) {
    case 'visible':
      await element.waitFor({ state: 'visible', timeout });
      break;
    case 'hidden':
      await element.waitFor({ state: 'hidden', timeout });
      break;
    case 'enabled':
      await expect(element).toBeEnabled({ timeout });
      break;
    case 'disabled':
      await expect(element).toBeDisabled({ timeout });
      break;
  }
};

export const takeScreenshot = async (
  page: Page,
  name: string,
  path: string = 'test-results'
): Promise<string> => {
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  const filename = `${path}/${name}-${timestamp}.png`;
  await page.screenshot({ path: filename, fullPage: true });
  return filename;
};

export const getConsoleLogs = async (page: Page): Promise<string[]> => {
  const logs: string[] = [];
  
  page.on('console', msg => {
    logs.push(`[${msg.type()}] ${msg.text()}`);
  });
  
  return logs;
};

export const maskSensitiveData = (data: Record<string, unknown>): Record<string, unknown> => {
  const masked = { ...data };
  const sensitiveKeys = ['password', 'token', 'secret', 'key', 'credential'];
  
  for (const key of Object.keys(masked)) {
    if (sensitiveKeys.some(sk => key.toLowerCase().includes(sk))) {
      masked[key] = '****';
    }
  }
  
  return masked;
};

export const assertResponseOk = async (
  page: Page,
  expectedStatus: number = 200
): Promise<boolean> => {
  let lastResponseStatus = 0;
  
  page.on('response', response => {
    lastResponseStatus = response.status();
  });
  
  return lastResponseStatus === expectedStatus;
};
