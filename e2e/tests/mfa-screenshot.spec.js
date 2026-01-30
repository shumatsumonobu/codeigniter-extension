// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * MFA Screenshot Tests
 *
 * This script captures screenshots of the complete MFA flow for documentation.
 */

const TEST_USER = {
  email: 'robin@example.com',
  password: 'password'
};

const SCREENSHOT_DIR = '../docs/screenshots';

test.describe('MFA Documentation Screenshots', () => {
  test('1. Capture Login Page', async ({ page }) => {
    await page.goto('/users/login');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${SCREENSHOT_DIR}/01-login-page.png`, fullPage: true });
  });

  test('2. Capture MFA Verify Page', async ({ page }) => {
    await page.goto('/users/login');
    await page.fill('input[name="email"]', TEST_USER.email);
    await page.fill('input[name="password"]', TEST_USER.password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${SCREENSHOT_DIR}/02-mfa-verify.png`, fullPage: true });
  });

  test('3. Capture MFA Setup Page', async ({ page }) => {
    await page.goto('/users/mfa-setup');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${SCREENSHOT_DIR}/03-mfa-setup.png`, fullPage: true });
  });

  test('4. Capture MFA Settings Page', async ({ page }) => {
    await page.goto('/users/mfa-settings');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${SCREENSHOT_DIR}/04-mfa-settings.png`, fullPage: true });
  });

  test('5. Capture MFA Recovery Page', async ({ page }) => {
    await page.goto('/users/mfa-recovery');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${SCREENSHOT_DIR}/05-mfa-recovery.png`, fullPage: true });
  });
});
