# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: admin-workflow.spec.ts >> Admin Workflow - Language Management >> Should navigate to Multilang settings page
- Location: tests/e2e/admin-workflow.spec.ts:9:7

# Error details

```
Test timeout of 30000ms exceeded while running "beforeEach" hook.
```

```
Error: page.waitForURL: Test timeout of 30000ms exceeded.
=========================== logs ===========================
waiting for navigation to "**/wp-admin/**" until "load"
============================================================
```

# Page snapshot

```yaml
- generic [ref=e1]:
  - heading "Log In" [level=1] [ref=e2]
  - generic [ref=e3]:
    - link "Powered by WordPress" [ref=e4] [cursor=pointer]:
      - /url: https://wordpress.org/
    - paragraph [ref=e6]:
      - strong [ref=e7]: "Error:"
      - text: The password you entered for the username
      - strong [ref=e8]: admin
      - text: is incorrect.
      - link "Lost your password?" [ref=e9] [cursor=pointer]:
        - /url: http://ujicoba-plugin.local/wp-login.php?action=lostpassword
    - generic [ref=e10]:
      - paragraph [ref=e11]:
        - generic [ref=e12]: Username or Email Address
        - textbox "Username or Email Address" [ref=e13]: admin
      - generic [ref=e14]:
        - generic [ref=e15]: Password
        - generic [ref=e16]:
          - textbox "Password" [active] [ref=e17]
          - button "Show password" [ref=e18] [cursor=pointer]:
            - generic [ref=e19]: 
      - paragraph [ref=e20]:
        - checkbox "Remember Me" [ref=e21] [cursor=pointer]
        - generic [ref=e22]: Remember Me
      - paragraph:
        - button "Log In" [ref=e23] [cursor=pointer]
    - paragraph [ref=e24]:
      - link "Lost your password?" [ref=e25] [cursor=pointer]:
        - /url: http://ujicoba-plugin.local/wp-login.php?action=lostpassword
    - paragraph [ref=e26]:
      - link "← Go to ujicoba-plugin" [ref=e27] [cursor=pointer]:
        - /url: http://ujicoba-plugin.local/
```

# Test source

```ts
  1  | import { Page } from '@playwright/test';
  2  | 
  3  | export async function loginToWordPress(page: Page, username = 'admin', password = 'password') {
  4  |   await page.goto('/wp-login.php');
  5  |   
  6  |   // If already logged in, skip login
  7  |   if (page.url().includes('wp-admin')) {
  8  |     return;
  9  |   }
  10 | 
  11 |   await page.fill('#user_login', username);
  12 |   await page.fill('#user_pass', password);
  13 |   await page.click('#wp-submit');
> 14 |   await page.waitForURL('**/wp-admin/**');
     |              ^ Error: page.waitForURL: Test timeout of 30000ms exceeded.
  15 | }
  16 | 
```