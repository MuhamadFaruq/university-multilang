import { Page } from '@playwright/test';

export async function loginToWordPress(page: Page, username = 'admin', password = 'password') {
  await page.goto('/wp-login.php');
  
  // If already logged in, skip login
  if (page.url().includes('wp-admin')) {
    return;
  }

  await page.fill('#user_login', username);
  await page.fill('#user_pass', password);
  await page.click('#wp-submit');
  await page.waitForURL('**/wp-admin/**');
}
