const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const LOG_FILE = path.join(__dirname, 'browser-logs.txt');

async function test() {
  const browser = await chromium.launch({ headless: false });
  const page = await browser.newPage();
  
  const logs = [];
  
  page.on('console', msg => {
    const log = `[${msg.type()}] ${msg.text()}`;
    logs.push(log);
    console.log(log);
  });
  
  page.on('pageerror', err => {
    const log = `[ERROR] ${err.message}`;
    logs.push(log);
    console.log(log);
  });
  
  // Login
  console.log('=== Login ===');
  await page.goto('http://localhost:8000/login');
  await page.waitForTimeout(2000);
  await page.fill('input[name="login"]', 'Администратор');
  await page.fill('input[name="password"]', 'admin123');
  await page.click('button:has-text("Вход")');
  await page.waitForTimeout(3000);
  
  // Create group
  console.log('\n=== Create Group ===');
  await page.goto('http://localhost:8000/groups/add');
  await page.waitForTimeout(5000);
  
  // Fill form
  const inputs = await page.$$('.v-text-field input');
  console.log('Inputs found:', inputs.length);
  
  if (inputs.length >= 2) {
    await inputs[0].fill('Тестовая группа ' + Date.now());
    await inputs[1].fill('Описание');
    console.log('Form filled');
  }
  
  // Click save
  console.log('Click save...');
  await page.click('button:has-text("СОХРАНИТЬ")');
  await page.waitForTimeout(5000);
  
  console.log('\nURL after save:', page.url());
  
  // Save logs to file
  fs.writeFileSync(LOG_FILE, logs.join('\n'), 'utf-8');
  console.log('\nLogs saved to:', LOG_FILE);
  
  await browser.close();
}

test().catch(console.error);
