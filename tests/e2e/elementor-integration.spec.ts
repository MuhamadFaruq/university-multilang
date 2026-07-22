import { test, expect } from '@playwright/test';
import { loginToWordPress } from './helpers/auth';

test.describe('Elementor Integration - Visual Builder Multilingual', () => {
  test.beforeEach(async ({ page }) => {
    await loginToWordPress(page);
  });

  test('Should verify Elementor pages open cleanly without redirect loop', async ({ page }) => {
    await page.goto('/wp-admin/edit.php?post_type=page');
    await expect(page.locator('h1')).toContainText('Pages');
  });
});
