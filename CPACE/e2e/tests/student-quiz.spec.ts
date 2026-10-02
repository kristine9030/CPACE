import { test, expect } from '@playwright/test';

/**
 * Covers the student's core feature end-to-end in a real browser: pick a
 * subject + length on /adaptive-quizzes, answer every question of a Training
 * mode quiz (immediate feedback, excluded from performance analytics — see
 * ../../DEPLOYMENT-STAGING.md and QuizFlowTest.php's grading coverage), and
 * confirm the results page renders a score. This is the browser-level
 * counterpart to QuizFlowTest/QuizSubmissionTest (which exercise the same
 * flow directly against Laravel, without a browser) — safe to run
 * repeatedly against staging since Training mode never touches performance
 * records, never production (see tests/login.spec.ts for the account note).
 */

const QUIZ_LENGTH = 5;

test('student can take a training-mode quiz end to end and see a score', async ({ page }) => {
  test.setTimeout(60000);

  await page.goto('login');
  await page.fill('#email', 'student@cpace.test');
  await page.fill('#password', 'Student123');
  await page.locator('.btn-login').click();
  await expect(page).toHaveURL(/\/dashboard/);

  await page.goto('adaptive-quizzes');

  // Steps 1 (Adaptive) and 2 (Training) already default to the values this
  // test wants, so only length (step 3) and subject (step 4) need picking.
  await page.locator(`.length-chip[data-count="${QUIZ_LENGTH}"]`).click();

  const subjectCell = page.locator('.subject-cell:not(.disabled)').first();
  await expect(subjectCell).toBeVisible();
  await subjectCell.click();

  const startBtn = page.locator('#startQuizBtn');
  await expect(startBtn).toBeEnabled();
  await startBtn.click();

  await expect(page).toHaveURL(/\/quiz\/\d+\/take/);

  // Answer every question: click the first choice of the active slide, then
  // advance. Training mode reveals correct/incorrect immediately but never
  // blocks moving on. On the last question, "Next" opens the submit modal
  // instead of navigating further.
  for (let i = 0; i < QUIZ_LENGTH; i++) {
    await page.locator('.q-slide.active .choice').first().click();
    await page.locator('#nextBtn').click();
  }

  await expect(page.locator('#endModal')).toHaveClass(/open/);
  await page.locator('#endModal .modal-btn.primary').click();

  await expect(page).toHaveURL(/\/quiz\/\d+\/results/, { timeout: 15000 });
  await expect(page.locator('.score-pct')).toBeVisible();
});
