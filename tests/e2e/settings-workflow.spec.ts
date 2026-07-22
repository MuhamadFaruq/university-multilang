import { test, expect } from '@playwright/test';
import { loginToWordPress } from './helpers/auth';

test.describe('Settings Workflow - Plugin Configuration', () => {
  test.beforeEach(async ({ page }) => {
    await loginToWordPress(page);
  });

  test('Should navigate to Settings page and save options', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=uml-settings');
    await expect(page.locator('h1')).toContainText('University Multilang Settings');

    // Toggle hide default language checkbox
    const hideDefaultCheckbox = page.locator('#uml_hide_default_language');
    if (await hideDefaultCheckbox.isVisible()) {
      await hideDefaultCheckbox.click();
      await page.click('input[type="submit"]');
      await expect(page.locator('.notice-success')).toBeVisible();
    }
  });
});
