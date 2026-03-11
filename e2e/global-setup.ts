import { chromium, type FullConfig } from '@playwright/test';
import fs from 'fs';
import path from 'path';

const createTestLogFile = (testName: string): string => {
  const sanitized = testName.replace(/[/\\?%*:|"<>]/g, '_');
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  const filename = `test-${sanitized}-${timestamp}.log`;
  const logPath = path.join(process.cwd(), 'test-results', 'logs', filename);
  
  const header = `=== Test Log: ${testName} ===
Started: ${new Date().toISOString()}
`;
  fs.writeFileSync(logPath, header);
  return logPath;
};

const appendToLog = (logPath: string, message: string): void => {
  fs.appendFileSync(logPath, `[${new Date().toISOString()}] ${message}\n`);
};

export default async function globalSetup(config: FullConfig): Promise<void> {
  console.log('🚀 Starting E2E tests...');
  console.log(`📂 Working directory: ${process.cwd()}`);
  
  const outputDir = path.join(process.cwd(), 'test-results');
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }
  
  fs.mkdirSync(path.join(outputDir, 'logs'), { recursive: true });
  fs.mkdirSync(path.join(outputDir, 'screenshots'), { recursive: true });
  fs.mkdirSync(path.join(outputDir, 'traces'), { recursive: true });
  
  console.log('✅ Global setup completed');
}

export function globalTeardown(results: any): void | Promise<void> {
  console.log('🏁 Tests finished. Generating summary...');
  
  const summaryPath = path.join(process.cwd(), 'test-results', 'summary.txt');
  const summary = results && results.stats ? `
=== Test Summary ===
Total: ${results.stats.total}
Passed: ${results.stats.passed}
Failed: ${results.stats.failed}
Skipped: ${results.stats.skipped}
Warnings: ${results.stats.warnings}
===================
` : 'No results available';
  
  fs.writeFileSync(summaryPath, summary);
  console.log('📊 Summary written to:', summaryPath);
}