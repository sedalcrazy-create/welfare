#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Screenshot capture script for Welfare System User Guide
Uses Playwright to capture screenshots from live application
"""

import os
import sys
from pathlib import Path
from playwright.sync_api import sync_playwright
import time

# Configuration
BASE_URL = 'https://ria.jafamhis.ir/welfare'
SCREENSHOT_DIR = Path(__file__).parent.parent / 'public' / 'screenshots'
ADMIN_EMAIL = 'admin@bankmelli.ir'
ADMIN_PASSWORD = 'password'

# Ensure screenshot directory exists
SCREENSHOT_DIR.mkdir(parents=True, exist_ok=True)

def take_screenshots():
    """Main function to capture all screenshots"""
    print('🚀 Starting screenshot capture...')

    with sync_playwright() as p:
        # Launch browser
        browser = p.chromium.launch(
            headless=True,
            args=['--ignore-certificate-errors']
        )

        context = browser.new_context(
            viewport={'width': 1920, 'height': 1080},
            locale='fa-IR',
            ignore_https_errors=True
        )

        page = context.new_page()

        try:
            # 1. Login Page
            print('📸 1/10: Capturing login page...')
            page.goto(BASE_URL, wait_until='networkidle')
            time.sleep(1)
            page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-1-login.png'))

            # Perform login
            print('🔐 Logging in...')
            page.fill('input[name="email"]', ADMIN_EMAIL)
            page.fill('input[name="password"]', ADMIN_PASSWORD)
            page.click('button[type="submit"]')
            page.wait_for_load_state('networkidle')
            time.sleep(2)

            # 2. Dashboard
            print('📸 2/10: Capturing dashboard...')
            page.goto(f'{BASE_URL}/dashboard', wait_until='networkidle')
            time.sleep(2)
            page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-2-dashboard.png'))

            # 3. Personnel Requests List
            print('📸 3/10: Capturing requests list...')
            page.goto(f'{BASE_URL}/personnel-requests', wait_until='networkidle')
            time.sleep(2)
            page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-3-requests-list.png'))

            # 4. Request Form - Supervisor Section
            print('📸 4/10: Capturing request form (supervisor section)...')
            page.goto(f'{BASE_URL}/personnel-requests/create', wait_until='networkidle')
            time.sleep(2)

            # Fill in supervisor data
            page.fill('input[name="employee_code"]', '12345')
            page.fill('input[name="full_name"]', 'علی احمدی')
            page.fill('input[name="national_code"]', '1234567890')
            page.fill('input[name="phone"]', '09123456789')
            time.sleep(1)

            page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-4-request-form-supervisor.png'))

            # 5. Request Form - Family Section
            print('📸 5/10: Capturing request form (family section)...')

            # Try to add a family member
            try:
                add_button = page.locator('button:has-text("افزودن همراه")')
                if add_button.count() > 0:
                    add_button.click()
                    time.sleep(0.5)

                    # Fill first family member
                    page.fill('input[name="family_members[0][full_name]"]', 'فاطمه محمدی')
                    page.select_option('select[name="family_members[0][relation]"]', 'همسر')
                    page.fill('input[name="family_members[0][national_code]"]', '9876543210')
                    page.select_option('select[name="family_members[0][gender]"]', 'female')
                    time.sleep(0.5)
            except Exception as e:
                print(f'⚠️  Could not add family member: {e}')

            # Scroll to family section
            try:
                page.evaluate("""() => {
                    const element = document.querySelector('.card-header');
                    if (element && element.textContent.includes('همراهان')) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }""")
                time.sleep(1)
            except:
                pass

            page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-5-request-form-family.png'))

            # 6. Request Details
            print('📸 6/10: Capturing request details...')
            page.goto(f'{BASE_URL}/personnel-requests', wait_until='networkidle')
            time.sleep(1)

            try:
                view_button = page.locator('a:has-text("مشاهده")').first
                if view_button.count() > 0:
                    view_button.click()
                    page.wait_for_load_state('networkidle')
                    time.sleep(2)
                    page.screenshot(
                        path=str(SCREENSHOT_DIR / 'screenshot-6-request-details.png'),
                        full_page=True
                    )
                else:
                    print('⚠️  No requests found')
                    page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-6-request-details.png'))
            except Exception as e:
                print(f'⚠️  Could not capture request details: {e}')
                page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-6-request-details.png'))

            # 7. Introduction Letter Form
            print('📸 7/10: Capturing letter form...')
            page.goto(f'{BASE_URL}/introduction-letters/create', wait_until='networkidle')
            time.sleep(2)
            page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-7-letter-form.png'))

            # 8. Issued Letter
            print('📸 8/10: Capturing issued letter...')
            page.goto(f'{BASE_URL}/introduction-letters', wait_until='networkidle')
            time.sleep(1)

            try:
                letter_view = page.locator('a:has-text("مشاهده")').first
                if letter_view.count() > 0:
                    letter_view.click()
                    page.wait_for_load_state('networkidle')
                    time.sleep(2)
                    page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-8-issued-letter.png'))
                else:
                    print('⚠️  No letters found')
                    page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-8-issued-letter.png'))
            except Exception as e:
                print(f'⚠️  Could not capture letter: {e}')
                page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-8-issued-letter.png'))

            # 9. Quota Management
            print('📸 9/10: Capturing quota management...')
            page.goto(f'{BASE_URL}/admin/user-center-quota', wait_until='networkidle')
            time.sleep(2)
            page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-9-quota-management.png'))

            # 10. Registration Control
            print('📸 10/10: Capturing registration control...')
            page.goto(f'{BASE_URL}/admin/registration-control', wait_until='networkidle')
            time.sleep(2)
            page.screenshot(path=str(SCREENSHOT_DIR / 'screenshot-10-registration-control.png'))

            print('✅ All screenshots captured successfully!')

        except Exception as e:
            print(f'❌ Error capturing screenshots: {e}')
            import traceback
            traceback.print_exc()
            return False
        finally:
            browser.close()

    return True

if __name__ == '__main__':
    success = take_screenshots()
    if success:
        print('🎉 Screenshot capture completed!')
        sys.exit(0)
    else:
        print('💥 Screenshot capture failed!')
        sys.exit(1)
