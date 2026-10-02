import { test, expect } from '@playwright/test';

/**
 * login.spec.ts only checks that Chair/Super Admin land on their dashboard
 * URL and heading. This goes one level deeper: the dashboards' own data
 * widgets (stat cards, activity feed) actually render, which is what would
 * break if a dashboard query/view regressed without touching the route or
 * page title. Read-only — no data is created or changed.
 */

test('Program Chair dashboard renders its key widgets', async ({ page }) => {
  await page.goto('login');
  await page.locator('.demo-btn', { hasText: 'Program Chair' }).click();
  await expect(page).toHaveURL(/\/chair\/dashboard/);

  await expect(page.locator('.stat-card').first()).toBeVisible();
  await expect(page.locator('.card-title', { hasText: 'Class-Level Performance' })).toBeVisible();
  await expect(page.locator('.card-title', { hasText: 'Faculty Workload' })).toBeVisible();
});

test('Super Admin dashboard renders its key widgets', async ({ page }) => {
  await page.goto('login');
  await page.locator('.demo-btn', { hasText: 'Super Admin' }).click();
  await expect(page).toHaveURL(/\/superadmin\/dashboard/);

  await expect(page.locator('.stat-card').first()).toBeVisible();
  await expect(page.locator('.card-title', { hasText: 'Accounts by Role' })).toBeVisible();
  await expect(page.locator('.card-title', { hasText: 'Recent Activity' })).toBeVisible();
});
