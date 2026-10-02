import { test, expect } from '@playwright/test';

/**
 * Browser-level counterpart to FacultyTestBankTest.php's coverage of the
 * Test Bank screen: confirms the page itself renders for a faculty account
 * and that "Add Question" reaches a real question form, rather than a
 * broken link or a blank page. Read-only — doesn't submit a question, so
 * it's safe to run repeatedly against staging without seeding the test bank
 * with throwaway rows on every CI run.
 */
test('faculty can open Test Bank and reach the Add Question form', async ({ page }) => {
  await page.goto('login');
  await page.fill('#email', 'faculty@cpace.test');
  await page.fill('#password', 'Faculty123');
  await page.locator('.btn-login').click();
  await expect(page).toHaveURL(/\/faculty\/dashboard/);

  await page.goto('faculty/test-bank');
  await expect(page.locator('.page-title')).toContainText('Test Bank');

  await page.locator('a', { hasText: 'Add Question' }).click();
  await expect(page).toHaveURL(/\/faculty\/test-bank\/create/);
  await expect(page.locator('.page-title')).toContainText('Add New Question');
  await expect(page.locator('#questionForm')).toBeVisible();
});
