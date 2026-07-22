import { Page, expect } from '@playwright/test';

export async function navigateToAdminMenu(page: Page, menuTitle: string) {
  await page.click(`text=${menuTitle}`);
  await page.waitForLoadState('networkidle');
}

export async function assertNotification(page: Page, text: string) {
  const notice = page.locator('.notice, .updated, .notice-success');
  await expect(notice).toContainText(text);
}
