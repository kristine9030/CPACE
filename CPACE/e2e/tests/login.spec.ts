import { test, expect } from '@playwright/test';

/**
 * Read-only login smoke tests: sign in as each role and confirm it lands
 * on that role's own dashboard. No quiz submissions, no record creation,
 * no data mutation — safe to run repeatedly against staging (never
 * production; see ../../DEPLOYMENT-STAGING.md).
 *
 * Program Chair and Super Admin use the login page's own demo buttons
 * (chair@cpace.test / superadmin@cpace.test) — both are purpose-built
 * @cpace.test accounts seeded by database/cpace_database.sql, so they
 * exist on any fresh install. Student/Faculty/Alumni instead fill the
 * login form directly with their own dedicated @cpace.test accounts (also
 * seeded by the same dump) rather than clicking the login page's
 * Student/Faculty/Alumni demo buttons, which point at real people's
 * personal accounts that only exist in production, not a fresh staging DB.
 */

type Role = { heading: string; urlPart: string } & (
  | { via: 'demo-button'; button: string }
  | { via: 'form'; email: string; password: string }
);

const roles: Record<string, Role> = {
  'Program Chair': { via: 'demo-button', button: 'Program Chair', heading: 'Program Chair Dashboard', urlPart: '/chair/dashboard' },
  'Super Admin': { via: 'demo-button', button: 'Super Admin', heading: 'System Console', urlPart: '/superadmin/dashboard' },
  'Student': { via: 'form', email: 'student@cpace.test', password: 'Student123', heading: 'Home', urlPart: '/dashboard' },
  'Faculty': { via: 'form', email: 'faculty@cpace.test', password: 'Faculty123', heading: 'Faculty Dashboard', urlPart: '/faculty/dashboard' },
  'Alumni': { via: 'form', email: 'alumni@cpace.test', password: 'Alumni123', heading: 'Alumni Community', urlPart: '/community' },
};

test.describe('Login routes each role to its own dashboard', () => {
  for (const [name, role] of Object.entries(roles)) {
    test(`${name} lands on ${role.urlPart}`, async ({ page }) => {
      await page.goto('login');

      if (role.via === 'demo-button') {
        await expect(page.locator('.demo-btn', { hasText: role.button })).toBeVisible();
        await page.locator('.demo-btn', { hasText: role.button }).click();
      } else {
        await page.fill('#email', role.email);
        await page.fill('#password', role.password);
        await page.locator('.btn-login').click();
      }

      await expect(page).toHaveURL(new RegExp(role.urlPart.replace('/', '\\/')));
      await expect(page.locator('.page-title')).toContainText(role.heading);

      // No explicit logout needed — each test gets its own fresh browser
      // context (cookies/session), so nothing persists past this test.
    });
  }
});

test('an invalid login shows an error and does not authenticate', async ({ page }) => {
  await page.goto('login');
  await page.fill('#email', 'nobody@example.com');
  await page.fill('#password', 'wrong-password');
  await page.locator('.btn-login').click();

  await expect(page).toHaveURL(/\/login/);
  await expect(page.locator('body')).toContainText(/credentials do not match/i);
});
