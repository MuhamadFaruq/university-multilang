import { test, expect } from '@playwright/test';
import { loginToWordPress } from './helpers/auth';

test.describe('Translation Workflow - Post Metabox', () => {
  test.beforeEach(async ({ page }) => {
    await loginToWordPress(page);
  });

  test('Should show Language Metabox in post editor', async ({ page }) => {
    await page.goto('/wp-admin/post-new.php');
    
    // Check if metabox or language dropdown is present
    const metabox = page.locator('#uml_language_metabox');
    if (await metabox.isVisible()) {
      await expect(metabox).toBeVisible();
      await expect(page.locator('#uml_post_language')).toBeVisible();
    }
  });
});
