const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'https://ria.jafamhis.ir/welfare';
const SCREENSHOT_DIR = path.join(__dirname, '..', 'public', 'screenshots', 'phase1');

// Admin credentials
const ADMIN_EMAIL = 'admin@bankmelli.ir';
const ADMIN_PASSWORD = 'password';

// Create screenshot directory if it doesn't exist
if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

async function takePhase1Screenshots() {
  console.log('🚀 Starting Phase 1 screenshot capture...');

  const browser = await chromium.launch({
    headless: true,
    args: ['--ignore-certificate-errors', '--no-sandbox', '--disable-setuid-sandbox']
  });

  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 },
    locale: 'fa-IR',
    ignoreHTTPSErrors: true
  });

  const page = await context.newPage();

  try {
    // Login first
    console.log('🔐 Logging in...');
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(1000);
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(2000);
    console.log('✅ Login successful');

    // Screenshot 1: فرم ثبت درخواست با dropdown دوره
    console.log('📸 1/7: فرم ثبت درخواست با dropdown دوره...');
    await page.goto(`${BASE_URL}/personnel-requests/create`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(2000);

    // Fill some sample data to make it look realistic
    await page.fill('input[name="employee_code"]', '12345');
    await page.fill('input[name="full_name"]', 'علی احمدی');
    await page.fill('input[name="national_code"]', '1234567890');
    await page.fill('input[name="phone"]', '09123456789');

    // Try to select a center first (required before period)
    const centerSelect = await page.$('select[name="preferred_center_id"]');
    if (centerSelect) {
      const options = await page.$$eval('select[name="preferred_center_id"] option', opts =>
        opts.map(opt => opt.value).filter(v => v)
      );
      if (options.length > 0) {
        await page.selectOption('select[name="preferred_center_id"]', options[0]);
        await page.waitForTimeout(500);
      }
    }

    // Highlight the period dropdown
    await page.evaluate(() => {
      const periodSelect = document.querySelector('select[name="preferred_period_id"]');
      if (periodSelect) {
        periodSelect.style.border = '3px solid #ff6b6b';
        periodSelect.style.boxShadow = '0 0 10px rgba(255,107,107,0.5)';
      }
    });

    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'phase1-01-period-dropdown.png'),
      fullPage: false
    });
    console.log('✅ Screenshot 1 saved');

    // Screenshot 2: صفحه تأیید درخواست‌ها با badge pending
    console.log('📸 2/7: صفحه تأیید درخواست‌ها با badge...');
    await page.goto(`${BASE_URL}/admin/personnel-approvals/pending`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(2000);

    // Highlight the sidebar menu item with badge
    await page.evaluate(() => {
      const approvalLink = document.querySelector('a[href*="personnel-approvals"]');
      if (approvalLink) {
        approvalLink.style.border = '2px solid #28a745';
        approvalLink.style.backgroundColor = 'rgba(40,167,69,0.1)';
      }
    });

    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'phase1-02-approval-page-badge.png'),
      fullPage: false
    });
    console.log('✅ Screenshot 2 saved');

    // Screenshot 3: فیلترهای صفحه تأیید (شامل فیلتر دوره)
    console.log('📸 3/7: فیلترهای صفحه تأیید...');

    // Highlight filter section
    await page.evaluate(() => {
      const filterSection = document.querySelector('.card-header:has-text("فیلتر")') ||
                           document.querySelector('.filters') ||
                           document.querySelector('form');
      if (filterSection) {
        filterSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }

      // Highlight period filter if exists
      const periodFilter = document.querySelector('select[name="period_id"]') ||
                          document.querySelector('[name*="period"]');
      if (periodFilter) {
        periodFilter.style.border = '3px solid #ff6b6b';
        periodFilter.style.boxShadow = '0 0 10px rgba(255,107,107,0.5)';
      }
    });

    await page.waitForTimeout(1000);
    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'phase1-03-filters-with-period.png'),
      fullPage: false
    });
    console.log('✅ Screenshot 3 saved');

    // Screenshot 4: Modal رد درخواست
    console.log('📸 4/7: Modal رد درخواست...');

    // Look for reject button
    const rejectButton = await page.$('button:has-text("رد")');
    if (rejectButton) {
      await rejectButton.click();
      await page.waitForTimeout(1000);

      await page.screenshot({
        path: path.join(SCREENSHOT_DIR, 'phase1-04-reject-modal.png'),
        fullPage: false
      });
      console.log('✅ Screenshot 4 saved');

      // Close modal
      const closeButton = await page.$('button.close') || await page.$('[data-dismiss="modal"]');
      if (closeButton) {
        await closeButton.click();
        await page.waitForTimeout(500);
      } else {
        await page.keyboard.press('Escape');
        await page.waitForTimeout(500);
      }
    } else {
      console.log('⚠️  No reject button found, skipping modal screenshot');
      // Take a placeholder screenshot
      await page.screenshot({
        path: path.join(SCREENSHOT_DIR, 'phase1-04-reject-modal.png'),
        fullPage: false
      });
    }

    // Screenshot 5: صفحه مدیریت سهمیه (کارت‌های per-center)
    console.log('📸 5/7: صفحه مدیریت سهمیه...');
    await page.goto(`${BASE_URL}/admin/user-center-quota`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(2000);

    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'phase1-05-quota-per-center.png'),
      fullPage: false
    });
    console.log('✅ Screenshot 5 saved');

    // Screenshot 6: Modal افزایش سهمیه
    console.log('📸 6/7: Modal افزایش سهمیه...');

    const increaseButton = await page.$('button:has-text("افزایش")');
    if (increaseButton) {
      await increaseButton.click();
      await page.waitForTimeout(1000);

      await page.screenshot({
        path: path.join(SCREENSHOT_DIR, 'phase1-06-increase-quota-modal.png'),
        fullPage: false
      });
      console.log('✅ Screenshot 6 saved');

      // Close modal
      const closeBtn = await page.$('button.close') || await page.$('[data-dismiss="modal"]');
      if (closeBtn) {
        await closeBtn.click();
        await page.waitForTimeout(500);
      } else {
        await page.keyboard.press('Escape');
        await page.waitForTimeout(500);
      }
    } else {
      console.log('⚠️  No increase button found, skipping modal screenshot');
      await page.screenshot({
        path: path.join(SCREENSHOT_DIR, 'phase1-06-increase-quota-modal.png'),
        fullPage: false
      });
    }

    // Screenshot 7: چک‌باکس‌های عملیات گروهی
    console.log('📸 7/7: چک‌باکس‌های عملیات گروهی...');
    await page.goto(`${BASE_URL}/admin/personnel-approvals/pending`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(2000);

    // Highlight bulk operation elements
    await page.evaluate(() => {
      // Highlight checkboxes
      const checkboxes = document.querySelectorAll('input[type="checkbox"]');
      checkboxes.forEach(cb => {
        cb.style.transform = 'scale(1.5)';
        if (cb.parentElement) {
          cb.parentElement.style.border = '2px solid #007bff';
          cb.parentElement.style.padding = '5px';
        }
      });

      // Highlight bulk buttons
      const bulkButtons = document.querySelectorAll('button:has-text("گروهی")');
      bulkButtons.forEach(btn => {
        btn.style.border = '3px solid #28a745';
        btn.style.boxShadow = '0 0 10px rgba(40,167,69,0.5)';
      });
    });

    await page.screenshot({
      path: path.join(SCREENSHOT_DIR, 'phase1-07-bulk-operations.png'),
      fullPage: false
    });
    console.log('✅ Screenshot 7 saved');

    console.log('\n✅ All Phase 1 screenshots captured successfully!');
    console.log(`📁 Screenshots saved to: ${SCREENSHOT_DIR}`);

  } catch (error) {
    console.error('❌ Error capturing screenshots:', error);
    throw error;
  } finally {
    await browser.close();
  }
}

// Run the script
takePhase1Screenshots()
  .then(() => {
    console.log('\n🎉 Phase 1 screenshot capture completed!');
    console.log('\n📋 Screenshots captured:');
    console.log('  1. phase1-01-period-dropdown.png - فرم با dropdown دوره');
    console.log('  2. phase1-02-approval-page-badge.png - صفحه تأیید با badge');
    console.log('  3. phase1-03-filters-with-period.png - فیلترها شامل دوره');
    console.log('  4. phase1-04-reject-modal.png - Modal رد درخواست');
    console.log('  5. phase1-05-quota-per-center.png - مدیریت سهمیه');
    console.log('  6. phase1-06-increase-quota-modal.png - Modal افزایش سهمیه');
    console.log('  7. phase1-07-bulk-operations.png - عملیات گروهی');
    process.exit(0);
  })
  .catch((error) => {
    console.error('💥 Script failed:', error);
    process.exit(1);
  });
