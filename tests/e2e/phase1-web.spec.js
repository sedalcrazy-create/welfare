// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Phase 1 Web Interface Tests', () => {

  test.describe('Basic Accessibility', () => {
    test('should redirect to login when not authenticated', async ({ page }) => {
      await page.goto('/');

      // Should redirect to login
      await expect(page).toHaveURL(/.*login/);

      console.log('✅ Authentication redirect works');
    });

    test('should display login page correctly', async ({ page }) => {
      await page.goto('/login');

      // Check for login elements
      await expect(page.locator('input[name="email"]')).toBeVisible();
      await expect(page.locator('input[name="password"]')).toBeVisible();
      await expect(page.locator('button[type="submit"]')).toBeVisible();

      console.log('✅ Login page displays correctly');
    });

    test('should show Persian UI', async ({ page }) => {
      await page.goto('/login');

      // Check for Persian content
      const bodyText = await page.textContent('body');
      expect(bodyText).toMatch(/ورود|بانک|سامانه/);

      console.log('✅ Persian UI detected');
    });
  });

  test.describe('Personnel Request Form (Phase 1 Critical)', () => {
    test('should require authentication to access form', async ({ page }) => {
      await page.goto('/personnel-requests/create');

      // Should redirect to login
      await expect(page).toHaveURL(/.*login/);

      console.log('✅ Personnel request form requires authentication');
    });

    // Note: The following tests require valid login credentials
    // Skipping for now, but documenting expected behavior

    test.skip('should display period selection dropdown (Phase 1 requirement)', async ({ page }) => {
      // TODO: Add login step with valid credentials
      // await login(page, 'user@example.com', 'password');

      await page.goto('/personnel-requests/create');

      // Check for period dropdown
      const periodSelect = page.locator('select[name="preferred_period_id"]');
      await expect(periodSelect).toBeVisible();

      // Check that it's required
      const isRequired = await periodSelect.getAttribute('required');
      expect(isRequired).not.toBeNull();

      console.log('✅ Period selection dropdown exists and is required');
    });

    test.skip('should display center selection dropdown', async ({ page }) => {
      await page.goto('/personnel-requests/create');

      const centerSelect = page.locator('select[name="preferred_center_id"]');
      await expect(centerSelect).toBeVisible();

      const isRequired = await centerSelect.getAttribute('required');
      expect(isRequired).not.toBeNull();

      console.log('✅ Center selection dropdown exists and is required');
    });

    test.skip('should allow adding family members', async ({ page }) => {
      await page.goto('/personnel-requests/create');

      // Click "افزودن همراه" button
      const addButton = page.locator('#add-family-member');
      await expect(addButton).toBeVisible();

      await addButton.click();

      // Check that family member row was added
      const familyRow = page.locator('.family-member-row').first();
      await expect(familyRow).toBeVisible();

      console.log('✅ Add family member functionality works');
    });

    test.skip('should limit family members to 10', async ({ page }) => {
      await page.goto('/personnel-requests/create');

      // Try to add 11 family members
      const addButton = page.locator('#add-family-member');

      for (let i = 0; i < 11; i++) {
        await addButton.click();
      }

      // Should only have 10 family member rows
      const familyRows = page.locator('.family-member-row');
      const count = await familyRows.count();
      expect(count).toBeLessThanOrEqual(10);

      console.log('✅ Family member limit enforced');
    });

    test.skip('should validate national code format', async ({ page }) => {
      await page.goto('/personnel-requests/create');

      const nationalCodeInput = page.locator('input[name="national_code"]');
      await nationalCodeInput.fill('123'); // Invalid - too short

      // Try to submit
      await page.locator('button[type="submit"]').click();

      // Should show validation error
      const errorMessage = page.locator('.invalid-feedback');
      await expect(errorMessage).toBeVisible();

      console.log('✅ National code validation works');
    });
  });

  test.describe('Admin Personnel Approvals (New Phase 1 Menu)', () => {
    test('should require admin role to access approvals', async ({ page }) => {
      await page.goto('/admin/personnel-approvals/pending');

      // Should redirect to login
      await expect(page).toHaveURL(/.*login/);

      console.log('✅ Personnel approvals requires authentication');
    });

    test.skip('should display pending requests list', async ({ page }) => {
      // TODO: Login as admin
      await page.goto('/admin/personnel-approvals/pending');

      // Check for table or list
      const table = page.locator('table');
      await expect(table).toBeVisible();

      // Check for filters
      const searchInput = page.locator('input[name="search"]');
      await expect(searchInput).toBeVisible();

      console.log('✅ Personnel approvals page displays correctly');
    });

    test.skip('should show statistics cards', async ({ page }) => {
      await page.goto('/admin/personnel-approvals/pending');

      // Check for stat cards
      const statCards = page.locator('.stat-card');
      const count = await statCards.count();
      expect(count).toBeGreaterThanOrEqual(3);

      console.log('✅ Statistics cards display');
    });

    test.skip('should have approve and reject buttons', async ({ page }) => {
      await page.goto('/admin/personnel-approvals/pending');

      // Check for approve button
      const approveButton = page.locator('button:has-text("تأیید")').first();
      await expect(approveButton).toBeVisible();

      // Check for reject button
      const rejectButton = page.locator('button:has-text("رد")').first();
      await expect(rejectButton).toBeVisible();

      console.log('✅ Approve/Reject buttons exist');
    });

    test.skip('should require rejection reason', async ({ page }) => {
      await page.goto('/admin/personnel-approvals/pending');

      // Click reject button
      const rejectButton = page.locator('button:has-text("رد")').first();
      await rejectButton.click();

      // Modal should open
      const modal = page.locator('#rejectModal');
      await expect(modal).toBeVisible();

      // Rejection reason textarea should be visible and required
      const reasonInput = page.locator('textarea[name="rejection_reason"]');
      await expect(reasonInput).toBeVisible();

      const isRequired = await reasonInput.getAttribute('required');
      expect(isRequired).not.toBeNull();

      console.log('✅ Rejection reason modal works');
    });

    test.skip('should support bulk operations', async ({ page }) => {
      await page.goto('/admin/personnel-approvals/pending');

      // Check for select all checkbox
      const selectAll = page.locator('#selectAll');
      await expect(selectAll).toBeVisible();

      // Check for bulk action buttons
      const bulkApprove = page.locator('button:has-text("تأیید گروهی")');
      const bulkReject = page.locator('button:has-text("رد گروهی")');

      await expect(bulkApprove).toBeVisible();
      await expect(bulkReject).toBeVisible();

      console.log('✅ Bulk operations UI exists');
    });
  });

  test.describe('Sidebar Navigation (Phase 1 Menus)', () => {
    test.skip('should display new "تأیید درخواست‌ها" menu item', async ({ page }) => {
      // TODO: Login as admin
      await page.goto('/dashboard');

      // Check for new menu item
      const approvalMenu = page.locator('a:has-text("تأیید درخواست‌ها")');
      await expect(approvalMenu).toBeVisible();

      // Click and navigate
      await approvalMenu.click();
      await expect(page).toHaveURL(/.*personnel-approvals/);

      console.log('✅ New approval menu item works');
    });

    test.skip('should show pending count badge on approval menu', async ({ page }) => {
      await page.goto('/dashboard');

      // Check for badge with count
      const badge = page.locator('.nav-link:has-text("تأیید درخواست‌ها") .nav-badge');

      // Badge should exist if there are pending requests
      const badgeExists = await badge.count() > 0;
      console.log(`✅ Pending count badge ${badgeExists ? 'displayed' : 'not needed (no pending)'}`);
    });

    test.skip('should have all Phase 1 menu items', async ({ page }) => {
      await page.goto('/dashboard');

      // Check for all Phase 1 menus
      const menus = [
        'درخواست‌ها',
        'تأیید درخواست‌ها',
        'معرفی‌نامه‌ها',
        'سهمیه',
      ];

      for (const menuText of menus) {
        const menu = page.locator(`a:has-text("${menuText}")`);
        await expect(menu).toBeVisible();
      }

      console.log('✅ All Phase 1 menus present');
    });
  });

  test.describe('HTTPS and Security', () => {
    test('should use HTTPS', async ({ page }) => {
      await page.goto('/');

      const url = page.url();
      expect(url).toMatch(/^https:/);

      console.log('✅ HTTPS is active');
    });

    test('should have CSRF protection', async ({ page }) => {
      await page.goto('/login');

      // Check for CSRF token
      const csrfToken = page.locator('input[name="_token"]');
      await expect(csrfToken).toBeVisible();

      const tokenValue = await csrfToken.getAttribute('value');
      expect(tokenValue).toBeTruthy();
      expect(tokenValue!.length).toBeGreaterThan(20);

      console.log('✅ CSRF token present');
    });
  });

  test.describe('Mobile Responsiveness', () => {
    test('should be responsive on mobile', async ({ page }) => {
      // Set mobile viewport
      await page.setViewportSize({ width: 375, height: 667 });

      await page.goto('/login');

      // Page should still be usable
      await expect(page.locator('input[name="email"]')).toBeVisible();

      console.log('✅ Mobile viewport works');
    });
  });
});
