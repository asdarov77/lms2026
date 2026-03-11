import { test as baseTest, expect, type Page, TestInfo } from '@playwright/test';
import fs from 'fs';
import path from 'path';

const testResultsDir = path.join(process.cwd(), 'test-results');

interface ConsoleLog {
  timestamp: string;
  type: string;
  text: string;
  location?: string;
}

interface NetworkRequest {
  timestamp: string;
  url: string;
  method: string;
  status?: number;
  responseSize?: number;
}

function ensureDirectory(dirPath: string): void {
  if (!fs.existsSync(dirPath)) {
    fs.mkdirSync(dirPath, { recursive: true });
  }
}

function getTestLogPaths(testName: string): {
  consoleLog: string;
  networkLog: string;
} {
  const sanitized = testName.replace(/[/\\?%*:|"<>]/g, '_');
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  const baseName = `test-${sanitized}-${timestamp}`;
  
  return {
    consoleLog: path.join(testResultsDir, 'logs', `${baseName}-console.log`),
    networkLog: path.join(testResultsDir, 'logs', `${baseName}-network.log`),
  };
}

function writeLogHeader(filePath: string, testName: string): void {
  const header = `=== ${path.basename(filePath)} ===
Test: ${testName}
Started: ${new Date().toISOString()}
${'='.repeat(50)}
`;
  fs.writeFileSync(filePath, header);
}

export const test = baseTest.extend<{
  consoleLogs: ConsoleLog[];
  networkRequests: NetworkRequest[];
  testLogPaths: { consoleLog: string; networkLog: string };
}>({
  consoleLogs: async ({}, use, testInfo) => {
    const logs: ConsoleLog[] = [];
    const { title } = testInfo as TestInfo;
    
    // Initialize logs with header
    const logPaths = getTestLogPaths(title);
    writeLogHeader(logPaths.consoleLog, title);
    writeLogHeader(logPaths.networkLog, title);
    
    // Return console log function for tests to use
    await use(logs);
    
    // After test, write logs to file
    if (logs.length > 0) {
      const consoleContent = logs.map(log => 
        `[${log.timestamp}] [${log.type}] ${log.text}${log.location ? ` (${log.location})` : ''}`
      ).join('\n');
      fs.appendFileSync(logPaths.consoleLog, consoleContent);
    }
  },
  
  networkRequests: async ({}, use, testInfo) => {
    const requests: NetworkRequest[] = [];
    const { title } = testInfo as TestInfo;
    
    await use(requests);
    
    // After test, write network logs
    const logPaths = getTestLogPaths(title);
    if (requests.length > 0) {
      const networkContent = requests.map(req => 
        `[${req.timestamp}] ${req.method} ${req.url} - Status: ${req.status ?? 'N/A'} - Size: ${req.responseSize ?? 'N/A'} bytes`
      ).join('\n');
      fs.appendFileSync(logPaths.networkLog, networkContent);
    }
  },
  
  testLogPaths: async ({}, use, testInfo) => {
    const { title } = testInfo as TestInfo;
    const logPaths = getTestLogPaths(title);
    await use(logPaths);
  },
});

// Export expect for assertions
export { expect };

// Helper to setup console and network listeners on a page
export function setupPageLogging(page: Page, testInfo: TestInfo): {
  consoleLogs: ConsoleLog[];
  networkRequests: NetworkRequest[];
} {
  const consoleLogs: ConsoleLog[] = [];
  const networkRequests: NetworkRequest[] = [];
  
  page.on('console', msg => {
    const log: ConsoleLog = {
      timestamp: new Date().toISOString(),
      type: msg.type(),
      text: msg.text(),
      location: `${msg.location().url}:${msg.location().lineNumber}`,
    };
    consoleLogs.push(log);
    
    // Also write to console for visibility
    const prefix = `[${msg.type().toUpperCase()}]`;
    console.log(`${prefix} ${msg.text()}`);
  });
  
  page.on('requestfailed', request => {
    const url = request.url();
    const method = request.method();
    
    console.error(`❌ Request failed: ${method} ${url}`);
    console.error(`   Failure: ${request.failure()?.errorText || 'Unknown error'}`);
  });
  
  page.on('request', request => {
    const url = request.url();
    const method = request.method();
    
    // Only log API requests (filter out assets, etc.)
    if (url.includes('/api/') || url.includes('/sanctum/csrf-cookie')) {
      const networkReq: NetworkRequest = {
        timestamp: new Date().toISOString(),
        url,
        method,
      };
      networkRequests.push(networkReq);
      console.log(`📡 Request: ${method} ${url}`);
    }
  });
  
  page.on('response', async response => {
    const request = response.request();
    const url = request.url();
    
    // Only track API requests
    if (url.includes('/api/') || url.includes('/sanctum/csrf-cookie')) {
      const status = response.status();
      let responseSize = 0;
      
      try {
        const body = await response.body();
        responseSize = body.length;
      } catch (e) {
        // Body might not be available for some responses
      }
      
      // Update the network request with response info
      const index = networkRequests.findIndex(req => req.url === url && req.method === request.method());
      if (index !== -1) {
        networkRequests[index] = {
          ...networkRequests[index],
          status,
          responseSize,
        };
      }
      
      console.log(`📤 Response: ${status} from ${url} (${responseSize} bytes)`);
      
      // Log error responses
      if (status >= 400) {
        try {
          const body = await response.text();
          console.error(`⚠️  Error response (${status}): ${body.substring(0, 500)}`);
        } catch (e) {
          // Ignore if we can't read body
        }
      }
    }
  });
  
  return { consoleLogs, networkRequests };
}

// Retrofit existing tests: extend base test with logging capabilities
export * from '@playwright/test';