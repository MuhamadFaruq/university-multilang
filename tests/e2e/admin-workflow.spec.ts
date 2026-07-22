import { test, expect } from '@playwright/test';
import { loginToWordPress } from './helpers/auth';

test.describe('Admin Workflow - Language Management', () => {
  test.beforeEach(async ({ page }) => {
    await loginToWordPress(page);
  });

  test('Should navigate to Multilang settings page', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=uml-languages');
    await expect(page.locator('h1')).toContainText('University Multilang');
  });

  test('Should add a new language via admin interface', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=uml-languages');
    
    // Fill out language form
    await page.fill('input[name="lang_name"]', 'Spanish E2E');
    await page.fill('input[name="lang_slug"]', 'es-e2e');
    await page.fill('input[name="lang_locale"]', 'es_ES');
    await page.click('input[type="submit"]');

    await expect(page.locator('.wrap')).toContainText('Spanish E2E');
  });

  test('Should delete a language from admin interface', async ({ page }) => {
    await page.goto('/wp-admin/admin.php?page=uml-languages');
    
    // Find delete button for es-e2e
    const row = page.locator('tr', { hasText: 'es-e2e' });
    if (await row.count() > 0) {
      page.once('dialog', dialog => dialog.accept());
      await row.locator('a.delete-lang').click();
      await expect(page.locator('.wrap')).not.toContainText('es-e2e');
    }
  });
});
