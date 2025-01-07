<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.3;
            font-size: 16px;
            font-weight: 500;
        }

        .container {
            width: 95%;
            max-width: 900px;
            margin: auto;
            /* padding: 20px; */
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .header img {
            max-height: 80px;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            margin: 0;
            line-height: 1.2;
            align-self: flex-start;
        }

        .section {
            margin-bottom: 20px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .details-grid div {
            font-size: 14px;
        }

        .details-grid strong {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }


        /* Remove all borders for the last three rows */
        tr:nth-last-child(-n+4) td,
        tr:nth-last-child(-n+4)  {
            border: none;
        }

        tr:not(:nth-last-child(-n+4)) td,
        tr:not(:nth-last-child(-n+4)) th {
            border-top: none;
            border-left: none;
            border-right: none;
            border-bottom: 1px solid rgb(194, 187, 187);
            /* Set the desired border color and thickness */
        }

        th {
            text-align: left;
            border-top: none;
            border-left: none;
            border-right: none;
            /* background-color: #f4f4f4; */
            font-weight: bold;
            border-bottom: 1px solid #000000;
            font-weight: 1000;
            /* Set the desired border color and thickness */
        }

        th:last-child {
            text-align: right;
            /* Align the first header cell to the left */
        }

        td.align-right {
            text-align: right;
        }

        .totals {
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
            text-align: right;
        }

        .totals div {
            margin-bottom: 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 13px;
            line-height: 1.5;
        }

        .footer .due-date {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .footer .payment-info {
            margin-bottom: 10px;
        }

        .footer .credit-card-icons img {
            height: 24px;
            margin-right: 5px;
        }

        .footer .pay-online {
            font-weight: bold;
            color: #007bff;
            text-decoration: none;
            margin-bottom: 15px;
            display: block;
        }

        .footer .registered-office {
            font-size: 12px;
            color: #555;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1></h1>
            <img src="https://in.xero.com/api/0rgOqbfdBOeHTbstgwxjrr96vYxtmSXawauqDOdT/getLogo" alt="Logo">
        </div>

        <!-- Invoice and Address Details -->
        <div class="details-grid">
            <div>
                <h1 class="invoice-title">INVOICE</h1>
                <p style="margin-left: 20%;">A Team Electrical<br>
                    Attention: Dan Caspi<br>
                    304 S Jones Blvd<br>
                    Suite 689<br>
                    LAS VEGAS NV 89107<br>
                    USA</p>
            </div>
            <div class="container flex" style="display: flex; justify-content: space-between;">
                <div style="flex: 1;">
                    <p><strong style="font-weight: 1000;">Invoice Date</strong> Jan 6, 2025<br>
                        <strong style="font-weight: 1000;">Invoice Number</strong> 62135<br>
                    </p>
                </div>
                <div style="flex: 1; text-align: left;">
                    702 Print &amp; Marketing LLC<br>
                    5525 S Decatur Blvd # 106<br>
                    Las Vegas 89118 USA<br>
                    Phone: (702) 945-0936<br>
                    Visit us online: <a style="color: #000000;  text-decoration: none;" href="http://www.print702.com">www.print702.com</a>
                    </p>
                </div>
            </div>
        </div>
        <div class="details-grid">
            <div>
            </div>
            <div class="container flex" style="display: flex; justify-content: space-between;">
                <div style="flex: 1;">
                    <p><strong style="font-weight: 1000;">Delivery Address</strong>
                        304 S Jones Blvd<br>
                        LAS VEGAS NV 89107<br>
                        US</p>
                </div>
                <div style="flex: 1; text-align: right;">
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Tax</th>
                    <th>Amount USD</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Marketing Package Level 1</td>
                    <td>1.00</td>
                    <td>$6,500.00</td>
                    <td>8.375%</td>
                    <td class="align-right">$6,500.00</td>
                </tr>
                <tr>
                    <td>One Time Marketing Start Up Fee</td>
                    <td>1.00</td>
                    <td>$4,000.00</td>
                    <td>8.375%</td>
                    <td class="align-right">$4,000.00</td>
                </tr>
                <tr>
                    <td>SOCIALMEDIAPLA Google $2000 Ad Spend</td>
                    <td>1.00</td>
                    <td>$0.00</td>
                    <td>8.375%</td>
                    <td class="align-right">$0.00</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>Subtotal</td>
                    <td class="align-right">$10,500.00</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="padding:2px;">TOTAL TAX</td>
                    <td class="align-right" style="padding:2px;">$335.00</td>
                </tr>
                <tr>
                    <td colspan="5" style="border: none;">
                        <hr style="margin: 0; border: 1px solid #ccc;">
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-size: 18px; font-weight: bold;padding:2px; font-weight: 1000;">TOTAL USD</td>
                    <td class="align-right" style="font-size: 18px; font-weight: bold;padding:2px; font-weight: 1000;">$10,835.00</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <!-- <div class="totals" style="font-weight: normal;">
            <div><p style="font-size: 15px; font-weight: normal;">Subtotal: $10,500.00</p></div>
            <div style="font-weight: normal;">TOTAL TAX: $335.00</div>
            <hr style="margin-left:50%">
            <div style="font-size: 18px; font-weight: bold;">TOTAL USD: $10,835.00</div>
        </div> -->

        <!-- Footer -->
        <!-- Footer Section -->
        <div class="footer">
            <!-- Due Date and Reorder Email -->
            <p class="due-date" style="font-weight: 1000;">Due Date: Jan 6, 2025</p><br>
                To Reorder Email: <a style="color: #000000;  text-decoration: none;" href="mailto:sales@print702.com">sales@print702.com</a>

            <!-- Payment and Refund Policy -->
            <p class="payment-info">
                FULL PAYMENT IS REQUIRED AT TIME OF ORDER.<br>
                There is a 3.5% fee on all Card payments made over the phone/card not present.<br><br>
                ALL SALES ARE CONSIDERED FINAL. IF A REFUND IS REQUESTED WITHIN THE FIRST 24 HOURS WE WILL PROVIDE A 50% REFUND ONLY. ANYTHING AFTER THE 24 HOUR PERIOD THERE WILL BE NO REFUND GIVEN AND ONLY A STORE CREDIT WILL BE ISSUED ON A CASE BY CASE BASIS.<br><br>
                CARD HOLDER AGREES TO CHARGES TO THEIR CREDIT AND/OR DEBIT CARD AND FULLY AGREE NOT TO PURSUE A CHARGEBACK.
            </p>

            <!-- Credit Card Icons -->
            <!-- <div class="credit-card-icons">
                <div style="text-align: center; margin-top: 20px;">
                    <p style="font-size: 14px; font-family: Arial, sans-serif; margin-bottom: 10px;">Supported cards include Visa, Mastercard, American Express, and Discover.</p>
                    <div style="display: inline-flex; align-items: center; gap: 10px;">
                        <div style="width: 60px; height: 40px;">
                            <svg viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg" role="presentation" focusable="false" style="width: 100%; height: auto;">
                                <g>
                                    <rect stroke="#DDD" fill="#FFF" x=".25" y=".25" width="23.5" height="15.5" rx="2"></rect>
                                    <path d="M2.788 5.914A7.201 7.201 0 0 0 1 5.237l.028-.125h2.737c.371.013.672.125.77.519l.595 2.836.182.854 1.666-4.21h1.799l-2.674 6.167H4.304L2.788 5.914Zm7.312 5.37H8.399l1.064-6.172h1.7L10.1 11.284Zm6.167-6.021-.232 1.333-.153-.066a3.054 3.054 0 0 0-1.268-.236c-.671 0-.972.269-.98.531 0 .29.365.48.96.762.98.44 1.435.979 1.428 1.681-.014 1.28-1.176 2.108-2.96 2.108-.764-.007-1.5-.158-1.898-.328l.238-1.386.224.099c.553.23.917.328 1.596.328.49 0 1.015-.19 1.022-.604 0-.27-.224-.466-.882-.769-.644-.295-1.505-.788-1.491-1.674C11.878 5.84 13.06 5 14.74 5c.658 0 1.19.138 1.526.263Zm2.26 3.834h1.415c-.07-.308-.392-1.786-.392-1.786l-.12-.531c-.083.23-.23.604-.223.59l-.68 1.727Zm2.1-3.985L22 11.284h-1.575s-.154-.71-.203-.926h-2.184l-.357.926h-1.785l2.527-5.66c.175-.4.483-.512.889-.512h1.316Z" fill="#1434CB"></path>
                                </g>
                            </svg>
                        </div>
                        <div style="width: 60px; height: 40px;">
                            <svg viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg" role="presentation" focusable="false" style="width: 100%; height: auto;">
                                <rect fill="#252525" height="16" rx="2" width="24"></rect>
                                <circle cx="9" cy="8" fill="#eb001b" r="5"></circle>
                                <circle cx="15" cy="8" fill="#f79e1b" r="5"></circle>
                                <path d="M12 4c1.214.912 2 2.364 2 4s-.786 3.088-2 4c-1.214-.912-2-2.364-2-4s.786-3.088 2-4z" fill="#ff5f00"></path>
                            </svg>
                        </div>
                        <div style="width: 60px; height: 40px;">
                            <svg viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg" role="presentation" focusable="false" style="width: 100%; height: 100%;">
                                <rect fill="#016fd0" height="16" rx="2" width="24"></rect>
                                <path d="M13.764 13.394V7.692l10.148.01v1.574l-1.173 1.254 1.173 1.265v1.608h-1.873l-.995-1.098-.988 1.102z" fill="#fffffe"></path>
                            </svg>
                        </div>

                        <div style="width: 60px; height: 40px;">
                            <svg viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg" role="presentation" focusable="false" style="width: 100%; height: 100%;">
                                <path d="M21.997 15.75H22c.955.008 1.74-.773 1.751-1.746V2.006a1.789 1.789 0 0 0-.52-1.25A1.72 1.72 0 0 0 21.997.25H2.001A1.718 1.718 0 0 0 .77.757c-.33.33-.517.779-.521 1.247v11.99c.004.47.191.92.52 1.25.329.328.771.51 1.233.506h19.994Zm0 .5h-.002.002Z" stroke="#ddd" fill="#fff"></path>
                                <path d="M12.612 16h9.385A1.986 1.986 0 0 0 24 14.03v-2.358A38.74 38.74 0 0 1 12.612 16Z" fill="#F27712"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- Pay Online Link -->
            <!-- <a href="#" class="pay-online">View and pay online now</a> -->
            <!-- Registered Office -->
            <div class="registered-office">
                Registered Office: Attention: Accounting, 5525 S Decatur Blvd #106, Las Vegas, NV, 89118, United States.
            </div>
        </div>
    </div>
</body>
</html>