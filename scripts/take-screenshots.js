const { chromium } = require('playwright');
const path = require('path');

const BASE_URL = 'https://ria.jafamhis.ir/welfare';
const SCREENSHOT_DIR = path.join(__dirname, '..', 'public', 'screenshots');

// Admin credentials from seeder
const ADMIN_EMAIL = 'admin@bankmelli.ir';
const ADMIN_PASSWORD = 'password';

async function takeScreenshots() {
  console.log('🚀 Starting screenshot capture...');

  const browser = await chromium.launch({
    headless: true,
    args: ['--ignore-certificate-errors']
  });

  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    locale: 'fa-IR',
    ignoreHTTPSErrors: true
  });

  const page = await context.newPage();

  try {
    // 1. Login Page
    console.log('📸 1/10: Capturing login page...');
    await page.goto(BASE_URL, { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);
    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'screenshot-1-login.png'),
      fullPage: false
    });

    // Perform login
    console.log('🔐 Logging in...');
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);

    // 2. Dashboard
    console.log('📸 2/10: Capturing dashboard...');
    await page.goto(`${BASE_URL}/dashboard`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'screenshot-2-dashboard.png'),
      fullPage: false
    });

    // 3. Personnel Requests List
    console.log('📸 3/10: Capturing requests list...');
    await page.goto(`${BASE_URL}/personnel-requests`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'screenshot-3-requests-list.png'),
      fullPage: false
    });

    // 4. Request Form - Supervisor Section
    console.log('📸 4/10: Capturing request form (supervisor section)...');
    await page.goto(`${BASE_URL}/personnel-requests/create`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);

    // Fill in supervisor data for better screenshot
    await page.fill('input[name="employee_code"]', '12345');
    await page.fill('input[name="full_name"]', 'علی احمدی');
    await page.fill('input[name="national_code"]', '1234567890');
    await page.fill('input[name="phone"]', '09123456789');

    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'screenshot-4-request-form-supervisor.png'),
      fullPage: false
    });

    // 5. Request Form - Family Section
    console.log('📸 5/10: Capturing request form (family section)...');

    // Add a family member
    const addButton = await page.$('button:has-text("افزودن همراه")');
    if (addButton) {
      await addButton.click();
      await page.waitForTimeout(500);

      // Fill first family member
      await page.fill('input[name="family_members[0][full_name]"]', 'فاطمه محمدی');
      await page.selectOption('select[name="family_members[0][relation]"]', 'همسر');
      await page.fill('input[name="family_members[0][national_code]"]', '9876543210');
      await page.selectOption('select[name="family_members[0][gender]"]', 'female');

      await page.waitForTimeout(500);
    }

    // Scroll to family section
    await page.evaluate(() => {
      const element = document.querySelector('.card-header:has-text("همراهان")');
      if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
    await page.waitForTimeout(1000);

    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'screenshot-5-request-form-family.png'),
      fullPage: false
    });

    // 6. Request Details (need to find an existing request or create one)
    console.log('📸 6/10: Capturing request details...');

    // Try to go back to list and click first request
    await page.goto(`${BASE_URL}/personnel-requests`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);

    const firstViewButton = await page.$('a:has-text("مشاهده")');
    if (firstViewButton) {
      await firstViewButton.click();
      await page.waitForNavigation({ waitUntil: 'networkidle' });
      await page.waitForTimeout(2000);

      await page.screenshot({
        path: path.join(SCREENSHOT_DIR, 'screenshot-6-request-details.png'),
        fullPage: true
      });
    } else {
      console.log('⚠️  No requests found, skipping request details screenshot');
      // Create a placeholder
      await page.screenshot({
        path: path.join(SCREENSHOT_DIR, 'screenshot-6-request-details.png'),
        fullPage: false
      });
    }

    // 7. Introduction Letter Form
    console.log('📸 7/10: Capturing letter form...');
    await page.goto(`${BASE_URL}/introduction-letters/create`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'screenshot-7-letter-form.png'),
      fullPage: false
    });

    // 8. Issued Letter (need to find an existing letter)
    console.log('📸 8/10: Capturing issued letter...');
    await page.goto(`${BASE_URL}/introduction-letters`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);

    const firstLetterView = await page.$('a:has-text("مشاهده")');
    if (firstLetterView) {
      await firstLetterView.click();
      await page.waitForNavigation({ waitUntil: 'networkidle' });
      await page.waitForTimeout(2000);

      await page.screenshot({
        path: path.join(SCREENSHOT_DIR, 'screenshot-8-issued-letter.png'),
        fullPage: false
      });
    } else {
      console.log('⚠️  No letters found, taking list page screenshot instead');
      await page.screenshot({
        path: path.join(SCREENSHOT_DIR, 'screenshot-8-issued-letter.png'),
        fullPage: false
      });
    }

    // 9. Quota Management
    console.log('📸 9/10: Capturing quota management...');
    await page.goto(`${BASE_URL}/admin/user-center-quota`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'screenshot-9-quota-management.png'),
      fullPage: false
    });

    // 10. Registration Control
    console.log('📸 10/10: Capturing registration control...');
    await page.goto(`${BASE_URL}/admin/registration-control`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'screenshot-10-registration-control.png'),
      fullPage: false
    });

    console.log('✅ All screenshots captured successfully!');

  } catch (error) {
    console.error('❌ Error capturing screenshots:', error);
    throw error;
  } finally {
    await browser.close();
  }
}

// Run the script
takeScreenshots()
  .then(() => {
    console.log('🎉 Screenshot capture completed!');
    process.exit(0);
  })
  .catch((error) => {
    console.error('💥 Script failed:', error);
    process.exit(1);
  });
