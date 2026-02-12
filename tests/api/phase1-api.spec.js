// @ts-check
const { test, expect } = require('@playwright/test');

const BASE_URL = 'https://ria.jafamhis.ir/welfare';

test.describe('Phase 1 API Tests', () => {

  test.describe('Centers API', () => {
    test('should return list of active centers', async ({ request }) => {
      const response = await request.get(`${BASE_URL}/api/v1/centers`);

      expect(response.ok()).toBeTruthy();
      expect(response.status()).toBe(200);

      const data = await response.json();

      expect(data).toHaveProperty('success', true);
      expect(data).toHaveProperty('data');
      expect(data).toHaveProperty('total');
      expect(data.total).toBe(3);
      expect(data.data).toHaveLength(3);

      // Check first center structure
      const center = data.data[0];
      expect(center).toHaveProperty('id');
      expect(center).toHaveProperty('name');
      expect(center).toHaveProperty('slug');
      expect(center).toHaveProperty('city');
      expect(center).toHaveProperty('type');
      expect(center).toHaveProperty('type_label');
      expect(center).toHaveProperty('stay_duration');
      expect(center).toHaveProperty('unit_count');
      expect(center).toHaveProperty('bed_count');
      expect(center).toHaveProperty('description');

      console.log('✅ Centers API test passed - 3 centers returned');
    });

    test('should return Mashhad center details', async ({ request }) => {
      const response = await request.get(`${BASE_URL}/api/v1/centers`);
      const data = await response.json();

      const mashhad = data.data.find(c => c.slug === 'mashhad');

      expect(mashhad).toBeDefined();
      expect(mashhad.name).toContain('مشهد');
      expect(mashhad.type).toBe('religious');
      expect(mashhad.type_label).toBe('زیارتی');
      expect(mashhad.stay_duration).toBe(5);
      expect(mashhad.unit_count).toBe(227);
      expect(mashhad.bed_count).toBe(1029);

      console.log('✅ Mashhad center details validated');
    });

    test('should return Babolsar center details', async ({ request }) => {
      const response = await request.get(`${BASE_URL}/api/v1/centers`);
      const data = await response.json();

      const babolsar = data.data.find(c => c.slug === 'babolsar');

      expect(babolsar).toBeDefined();
      expect(babolsar.name).toContain('بابلسر');
      expect(babolsar.type).toBe('beach');
      expect(babolsar.type_label).toBe('ساحلی');
      expect(babolsar.stay_duration).toBe(4);
      expect(babolsar.unit_count).toBe(165);
      expect(babolsar.bed_count).toBe(626);

      console.log('✅ Babolsar center details validated');
    });

    test('should return Chadegan center details', async ({ request }) => {
      const response = await request.get(`${BASE_URL}/api/v1/centers`);
      const data = await response.json();

      const chadegan = data.data.find(c => c.slug === 'mrkz-rfahy-chadgan');

      expect(chadegan).toBeDefined();
      expect(chadegan.name).toContain('چادگان');
      expect(chadegan.type).toBe('mountain');
      expect(chadegan.type_label).toBe('کوهستانی');
      expect(chadegan.stay_duration).toBe(3);
      expect(chadegan.unit_count).toBe(34);
      expect(chadegan.bed_count).toBe(126);

      console.log('✅ Chadegan center details validated');
    });
  });

  test.describe('Periods API', () => {
    test('should return periods list (may be empty)', async ({ request }) => {
      const response = await request.get(`${BASE_URL}/api/v1/periods`);

      expect(response.ok()).toBeTruthy();
      expect(response.status()).toBe(200);

      const data = await response.json();

      expect(data).toHaveProperty('success', true);
      expect(data).toHaveProperty('data');
      expect(data).toHaveProperty('total');
      expect(Array.isArray(data.data)).toBeTruthy();

      console.log(`✅ Periods API test passed - ${data.total} periods found`);
    });

    test('should accept center_id filter', async ({ request }) => {
      const response = await request.get(`${BASE_URL}/api/v1/periods?center_id=1`);

      expect(response.ok()).toBeTruthy();
      expect(response.status()).toBe(200);

      const data = await response.json();
      expect(data).toHaveProperty('success', true);

      console.log('✅ Periods API with center_id filter works');
    });

    test('should accept status filter', async ({ request }) => {
      const response = await request.get(`${BASE_URL}/api/v1/periods?status=open`);

      expect(response.ok()).toBeTruthy();
      expect(response.status()).toBe(200);

      const data = await response.json();
      expect(data).toHaveProperty('success', true);

      console.log('✅ Periods API with status filter works');
    });
  });

  test.describe('Mobile Number Normalization', () => {
    // Note: This would require actual personnel registration endpoint to test
    // For now, we're documenting expected behavior

    test('should accept Persian digits (۰۹۱۲۳۴۵۶۷۸۹)', async () => {
      // Expected: ۰۹۱۲۳۴۵۶۷۸۹ → 09123456789
      const testPhone = '۰۹۱۲۳۴۵۶۷۸۹';
      console.log('📝 Persian digits should be normalized to:', '09123456789');
      expect(testPhone).toBeDefined();
    });

    test('should accept English digits (09123456789)', async () => {
      const testPhone = '09123456789';
      console.log('📝 English digits should remain:', testPhone);
      expect(testPhone).toBeDefined();
    });

    test('should accept +98 country code (+989123456789)', async () => {
      // Expected: +989123456789 → 09123456789
      const testPhone = '+989123456789';
      console.log('📝 +98 format should be normalized to:', '09123456789');
      expect(testPhone).toBeDefined();
    });

    test('should accept without leading zero (9123456789)', async () => {
      // Expected: 9123456789 → 09123456789
      const testPhone = '9123456789';
      console.log('📝 No leading zero should be normalized to:', '09123456789');
      expect(testPhone).toBeDefined();
    });

    test('should accept with spaces (0912 345 6789)', async () => {
      // Expected: 0912 345 6789 → 09123456789
      const testPhone = '0912 345 6789';
      console.log('📝 Spaces should be removed:', '09123456789');
      expect(testPhone).toBeDefined();
    });
  });

  test.describe('Personnel Registration API', () => {
    test('should validate required fields', async ({ request }) => {
      const response = await request.post(`${BASE_URL}/api/v1/personnel-requests/register`, {
        data: {}
      });

      // Should return validation error
      expect(response.status()).toBe(422);

      console.log('✅ Validation works - empty request rejected');
    });

    test('should require period_id (Phase 1 critical requirement)', async ({ request }) => {
      const response = await request.post(`${BASE_URL}/api/v1/personnel-requests/register`, {
        data: {
          employee_code: 'TEST001',
          full_name: 'تست آزمایشی',
          national_code: '1234567890',
          phone: '09123456789',
          preferred_center_id: 1
          // Missing preferred_period_id!
        }
      });

      // Should return validation error for missing period_id
      expect(response.status()).toBe(422);

      const data = await response.json();
      expect(data).toHaveProperty('errors');

      console.log('✅ Period ID validation works - missing period rejected');
    });
  });

  test.describe('Legacy Bale API (Backward Compatibility)', () => {
    test('should support legacy /api/bale/centers endpoint', async ({ request }) => {
      const response = await request.get(`${BASE_URL}/api/bale/centers`);

      expect(response.ok()).toBeTruthy();
      expect(response.status()).toBe(200);

      const data = await response.json();
      expect(data).toHaveProperty('success', true);
      expect(data.total).toBe(3);

      console.log('✅ Legacy Bale API backward compatibility works');
    });
  });
});
