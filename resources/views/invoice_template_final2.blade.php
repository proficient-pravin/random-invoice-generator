<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice</title>
</head>
<body style="font-family: DejaVu Sans, sans-serif; margin: 0; padding: 0; line-height: 1.3; font-size: 14px;">
    <div style="width: 100%; max-width: 100%; margin: auto;">
        <!-- Header -->
        <div style="display: block; margin-bottom: 20px;">
            <div style="float: right;">
                <img src="{{ public_path('logo.jpeg') }}" style="max-height: 80px;" alt="Logo">
            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- Invoice and Address Details -->
        <div style="margin-bottom: 0px;">
            <div style="width: 50%; float: left;">
                <h1 style="font-size: 26px; font-weight: normal; margin: 0; line-height: 1.2;">INVOICE</h1>
                <p style="margin-left: 20%; font-weight: normal;  line-height: 1;">
                    A Team Electrical<br>
                    Attention: Dan Caspi<br>
                    304 S Jones Blvd<br>
                    Suite 689<br>
                    LAS VEGAS NV 89107<br>
                    USA
                </p>
            </div>
            <div style="width: 50%; float: right;">
                <div style="width: 100%; float: left; line-height: 1; padding-bottom:10px;">
                    <p>
                        <span style="display: block;">
                            <span style="font-weight: bold; font-size: 12px; letter-spacing: 1px;">Invoice Date</span>
                            <span style="display: block; font-weight: normal; font-size: 12px;">Jan 6, 2025</span>
                        </span>
                        <span style="display: block;">
                            <span style="font-weight: bold; font-size: 12px; letter-spacing: 1px;">Invoice Number</span>
                            <span style="display: block; font-weight: normal; font-size: 12px;">62135</span>
                        </span>
                    </p>
                </div>
                <div style="width: 50%; float: right; font-weight: normal; line-height: 1;">
                    702 Print &amp; Marketing LLC<br>
                    5525 S Decatur Blvd # 106<br>
                    Las Vegas 89118 USA<br>
                    Phone: (702) 945-0936<br>
                    Visit us online: www.print702.com
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- Delivery Address -->
        <div style="margin-bottom: 10px; line-height: 1;">
            <div style="width: 50%; float: right;">
                <p>
                    <strong style="font-weight: bold;">Delivery Address</strong><br>
                    <span style="font-weight: normal;  line-height: 1;">
                        304 S Jones Blvd<br>
                        LAS VEGAS NV 89107<br>
                        US
                    </span>
                </p>
            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- Invoice Items -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr>
                    <th style="text-align: left; border-bottom: 1px solid #000000; padding: 8px; font-weight: 1000;">Description</th>
                    <th style="text-align: left; border-bottom: 1px solid #000000; padding: 8px; font-weight: 1000;">Quantity</th>
                    <th style="text-align: left; border-bottom: 1px solid #000000; padding: 8px; font-weight: 1000;">Unit Price</th>
                    <th style="text-align: left; border-bottom: 1px solid #000000; padding: 8px; font-weight: 1000;">Tax</th>
                    <th style="text-align: right; border-bottom: 1px solid #000000; padding: 8px; font-weight: 1000;">Amount USD</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">Marketing Package Level 1</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">1.00</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">$6,500.00</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">8.375%</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187); text-align: right;">$6,500.00</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">One Time Marketing Start Up Fee</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">1.00</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">$4,000.00</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">8.375%</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187); text-align: right;">$4,000.00</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">SOCIALMEDIAPLA Google $2000 Ad Spend</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">1.00</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">$0.00</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187);">8.375%</td>
                    <td style="padding: 8px; border-bottom: 1px solid rgb(194, 187, 187); text-align: right;">$0.00</td>
                </tr>
                <tr>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px;">Subtotal</td>
                    <td style="padding: 8px; text-align: right;">$10,500.00</td>
                </tr>
                <tr>
                    <td style="padding: 2px;"></td>
                    <td style="padding: 2px;"></td>
                    <td style="padding: 2px;"></td>
                    <td style="padding: 2px;">TOTAL TAX</td>
                    <td style="padding: 2px; text-align: right;">$335.00</td>
                </tr>
                <tr>
                    <td colspan="5" style="padding: 0;">
                        <hr style="margin: 0; border: 1px solid #ccc;">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 2px;"></td>
                    <td style="padding: 2px;"></td>
                    <td style="padding: 2px;"></td>
                    <td style="padding: 2px; font-size: 18px; font-weight: 1000;">TOTAL USD</td>
                    <td style="padding: 2px; font-size: 18px; font-weight: 1000; text-align: right;">$10,835.00</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div style="margin-top: 30px; font-size: 13px; line-height: 1.5;">
            <p style="font-weight: 1000;">Due Date: Jan 6, 2025</p>
            <p>To Reorder Email: sales@print702.com</p>

            <p style="margin-top: 20px;">
                FULL PAYMENT IS REQUIRED AT TIME OF ORDER.<br>
                There is a 3.5% fee on all Card payments made over the phone/card not present.<br><br>
                ALL SALES ARE CONSIDERED FINAL. IF A REFUND IS REQUESTED WITHIN THE FIRST 24 HOURS WE WILL PROVIDE A 50% REFUND ONLY. ANYTHING AFTER THE 24 HOUR PERIOD THERE WILL BE NO REFUND GIVEN AND ONLY A STORE CREDIT WILL BE ISSUED ON A CASE BY CASE BASIS.<br><br>
                CARD HOLDER AGREES TO CHARGES TO THEIR CREDIT AND/OR DEBIT CARD AND FULLY AGREE NOT TO PURSUE A CHARGEBACK.
            </p>

            <div style="margin-top: 20px; font-size: 12px; color: #555;">
                Registered Office: Attention: Accounting, 5525 S Decatur Blvd #106, Las Vegas, NV, 89118, United States.
            </div>
        </div>
    </div>
</body>
</html>