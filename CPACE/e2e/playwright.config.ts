import { defineConfig, devices } from '@playwright/test';

/**
 * Points at an already-running CPACE deployment (BASE_URL env var) — this
 * repo has no Node/npm on its Hostinger hosts (see DEPLOYMENT.md), so
 * Playwright never starts the app itself. Run these against a staging
 * deployment (see ../DEPLOYMENT-STAGING.md), NOT production — every demo
 * account login here is a real login on whatever BASE_URL points to.
 */
export default defineConfig({
  testDir: './tests',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['json', { outputFile: 'report.json' }], ['list']] : 'list',
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8000',
    trace: 'retain-on-failure',
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
  ],
});
