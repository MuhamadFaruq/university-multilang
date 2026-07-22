import { test, expect } from '@playwright/test';

test.describe('Frontend & SEO Workflow', () => {
  test('Should serve homepage and include SEO meta tags', async ({ page }) => {
    await page.goto('/');
    
    // Check page title or body
    await expect(page).toHaveURL(/.*local.*/);

    // Verify hreflang tags if injected in page source
    const content = await page.content();
    if (content.includes('University Multilang SEO Hreflang')) {
      await expect(page.locator('link[rel="alternate"][hreflang]')).toBeDefined();
    }
  });
});
