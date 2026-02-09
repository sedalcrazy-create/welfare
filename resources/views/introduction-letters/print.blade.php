<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معرفی‌نامه {{ $introductionLetter->letter_code }}</title>
    <style>
        @font-face {
            font-family: 'Vazirmatn';
            src: url('{{ asset('assets/fonts/Vazirmatn-Regular.woff2') }}') format('woff2');
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Vazirmatn';
            src: url('{{ asset('assets/fonts/Vazirmatn-Bold.woff2') }}') format('woff2');
            font-weight: bold;
            font-style: normal;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            direction: rtl;
            background: #ffffff;
            padding: 20px;
            font-size: 14px;
            line-height: 1.8;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 2px solid #1e40af;
            border-radius: 10px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            width: 100px;
            height: auto;
            margin-bottom: 15px;
        }

        .org-name {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .doc-title {
            font-size: 24px;
            font-weight: bold;
            color: #064e3b;
            margin: 20px 0;
            text-align: center;
        }

        .letter-code {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            background: #dbeafe;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            color: #1e40af;
        }

        .content {
            margin: 30px 0;
            text-align: justify;
        }

        .info-table {
            width: 100%;
            margin: 25px 0;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td {
            padding: 12px 15px;
            text-align: right;
            border: 1px solid #ddd;
        }

        .info-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            width: 35%;
        }

        .info-table td {
            background-color: #ffffff;
        }

        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #000;
            margin-top: 60px;
            padding-top: 10px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #1e40af;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }

        .qr-code {
            float: left;
            margin: 0 15px 15px 0;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }

            .container {
                border: none;
                padding: 30px;
            }

            .no-print {
                display: none !important;
            }
        }

        .print-button {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 12px 24px;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Vazirmatn', sans-serif;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .print-button:hover {
            background: #1e3a8a;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #10b981;
        }

        .status-used {
            background: #f3f4f6;
            color: #374151;
            border: 2px solid #9ca3af;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid #ef4444;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        🖨️ چاپ / ذخیره PDF
    </button>

    <div class="container">
        {{-- Header --}}
        <div class="header">
            <img src="{{ asset('logo.png') }}" alt="بانک ملی ایران" class="logo">
            <div class="org-name">بانک ملی ایران</div>
            <div>واحد امور رفاه کارکنان</div>
        </div>

        {{-- Title --}}
        <div class="doc-title">
            معرفی‌نامه استفاده از مراکز رفاهی
        </div>

        {{-- Letter Code --}}
        <div class="letter-code">
            شماره معرفی‌نامه: {{ $introductionLetter->letter_code }}
            <br>
            @if($introductionLetter->status === 'active')
                <span class="status-badge status-active">✓ فعال</span>
            @elseif($introductionLetter->status === 'used')
                <span class="status-badge status-used">✓ استفاده شده</span>
            @elseif($introductionLetter->status === 'cancelled')
                <span class="status-badge status-cancelled">✗ لغو شده</span>
            @endif
        </div>

        {{-- Content --}}
        <div class="content">
            <p>
                احتراماً، به استناد ضوابط و مقررات استفاده از مراکز رفاهی بانک ملی ایران،
                همکار محترم <strong>{{ $introductionLetter->personnel->full_name }}</strong>
                دارای کد ملی <strong>{{ $introductionLetter->personnel->national_code }}</strong>
                به همراه خانواده محترم ایشان (جمعاً <strong>{{ $introductionLetter->family_count }} نفر</strong>)
                جهت استفاده از تسهیلات و امکانات مرکز رفاهی <strong>{{ $introductionLetter->center->name }}</strong>
                واقع در <strong>{{ $introductionLetter->center->city }}</strong> معرفی می‌گردند.
            </p>
        </div>

        {{-- Information Table --}}
        <table class="info-table">
            <tr>
                <th>نام و نام خانوادگی:</th>
                <td>{{ $introductionLetter->personnel->full_name }}</td>
            </tr>
            <tr>
                <th>کد ملی:</th>
                <td>{{ $introductionLetter->personnel->national_code }}</td>
            </tr>
            <tr>
                <th>شماره تماس:</th>
                <td>{{ $introductionLetter->personnel->phone ?? '-' }}</td>
            </tr>
            <tr>
                <th>تعداد افراد خانواده:</th>
                <td>{{ $introductionLetter->family_count }} نفر</td>
            </tr>
            <tr>
                <th>مرکز رفاهی:</th>
                <td>{{ $introductionLetter->center->name }} - {{ $introductionLetter->center->city }}</td>
            </tr>
            <tr>
                <th>مدت اقامت:</th>
                <td>{{ $introductionLetter->center->stay_duration ?? '-' }} شب</td>
            </tr>
            <tr>
                <th>تاریخ صدور:</th>
                <td>{{ jdate($introductionLetter->issued_at)->format('l j F Y') }}</td>
            </tr>
            <tr>
                <th>صادرکننده:</th>
                <td>{{ $introductionLetter->issuedBy->name }}</td>
            </tr>
        </table>

        @if($introductionLetter->notes)
            <div style="margin: 25px 0; padding: 15px; background: #fef3c7; border-right: 4px solid #f59e0b; border-radius: 6px;">
                <strong>توضیحات:</strong>
                <p style="margin-top: 8px;">{{ $introductionLetter->notes }}</p>
            </div>
        @endif

        {{-- Additional Notes --}}
        <div class="content" style="margin-top: 30px; font-size: 13px; background: #f9fafb; padding: 15px; border-radius: 8px;">
            <strong>نکات مهم:</strong>
            <ul style="margin-right: 20px; margin-top: 10px;">
                <li>لطفاً قبل از مراجعه، با مرکز رفاهی تماس گرفته و رزرو خود را قطعی نمایید.</li>
                <li>همراه داشتن این معرفی‌نامه و کارت شناسایی معتبر الزامی است.</li>
                <li>رعایت ضوابط و مقررات مرکز رفاهی الزامی می‌باشد.</li>
                <li>در صورت لغو رزرو، لطفاً حداقل 48 ساعت قبل به مرکز اطلاع دهید.</li>
            </ul>
        </div>

        {{-- Signature Section --}}
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    امضاء مسئول صادرکننده
                    <br>
                    {{ $introductionLetter->issuedBy->name }}
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    مهر و امضاء مرکز رفاهی
                    <br>
                    ({{ $introductionLetter->center->name }})
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>
                این معرفی‌نامه توسط سامانه جامع مدیریت مراکز رفاهی بانک ملی ایران صادر شده است.
            </p>
            <p style="margin-top: 5px;">
                تاریخ چاپ: {{ jdate(now())->format('Y/m/d H:i') }}
            </p>
            <p style="margin-top: 5px; font-size: 11px;">
                کد معرفی‌نامه: {{ $introductionLetter->letter_code }}
            </p>
        </div>
    </div>

    <script>
        // Auto-print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
