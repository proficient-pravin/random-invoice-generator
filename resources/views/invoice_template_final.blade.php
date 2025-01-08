<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; line-height: 1.3; font-size: 14px;">
    <div style="width: 100%; max-width: 100%; margin: auto;">
        <!-- Header -->
        <div style="display: block; height: auto; ">
            <div style="float: right;">
                <img src="{{ public_path('logo.jpeg') }}" style="max-height: 83px; max-width: 300px;" alt="Logo">
            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- Invoice and Address Details -->
        <div style="margin-bottom: 0px;">
            <div style="width: 50%; float: left;">
                <h1 style="padding-left:5px; font-size: 32px; font-weight: normal;">INVOICE</h1>
                <p style="margin-left: 25%; font-weight: normal; line-height: 1;">
                    <!-- A Team Electrical<br> -->
                    Attention: {{ $invoice['po_attention_to'] ?? 'N/A' }}<br>
                    @if($invoice['po_address_line1'])
                        {{ $invoice['po_address_line1'] }}<br>
                    @endif
                    
                    @if($invoice['po_address_line2'])
                        {{ $invoice['po_address_line2'] }}<br>
                    @endif

                    @if($invoice['po_address_line3'])
                        {{ $invoice['po_address_line3'] }}<br>
                    @endif

                    @if($invoice['po_address_line4'])
                        {{ $invoice['po_address_line4'] }}<br>
                    @endif

                    @if($invoice['po_city'])
                        {{ $invoice['po_city'] }}
                    @endif

                    @if($invoice['po_region'])
                        {{ $invoice['po_region'] }}  {{ $invoice['po_zip_code'] }}<br>
                    @endif

                    @if($invoice['po_country'])
                        {{ $invoice['po_country'] }}
                    @endif
                </p>
            </div>
            <div style="width: 50%; float: right;">
                <div style="width: 100%; float: left; line-height: 1; padding-bottom:10px;padding-top:0px;padding-left:18%;">
                    <p>
                        <span style="display: block;">
                            <span style="font-weight: bold; font-size: 14px;">Invoice Date</span>
                            <span style="display: block; font-weight: normal; font-size: 14px;">{{ \Carbon\Carbon::parse($invoice['invoice_date'])->format('M j, Y') }}</span>
                        </span>
                        <span style="display: block; padding-top:10px;">
                            <span style="font-weight: bold; font-size: 14px;">Invoice Number</span>
                            <span style="display: block; font-weight: normal; font-size: 14px;">{{ $invoice['invoice_number'] }}</span>
                        </span>
                    </p>
                </div>
                <div style="padding-top:20px; width: 40%; float: right; font-weight: normal; line-height: 1; font-size: 13px">
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
        <div style="margin-bottom: 10px; line-height: 1; padding-left:18%;">
            <div style="width: 50%; float: right; ">
                <p>
                    <strong style="font-weight: bold; font-size: 14px;">Delivery Address</strong><br>
                    <span style="font-weight: normal;  line-height: 1;">
                    @if($invoice['sa_address_line1'])
                        {{ $invoice['sa_address_line1'] }}<br>
                    @endif

                    @if($invoice['sa_address_line2'])
                        {{ $invoice['sa_address_line2'] }}<br>
                    @endif

                    @if($invoice['sa_address_line3'])
                        {{ $invoice['sa_address_line3'] }}<br>
                    @endif

                    @if($invoice['sa_address_line4'])
                        {{ $invoice['sa_address_line4'] }}<br>
                    @endif

                    @if($invoice['sa_city'])
                        {{ $invoice['sa_city'] }}<br>
                    @endif

                    @if($invoice['sa_region'])
                        {{ $invoice['sa_region'] }} {{ $invoice['sa_zip_code'] }}<br>
                    @endif

                    @if($invoice['sa_country'])
                        {{ $invoice['sa_country'] }}<br>
                    @endif
                    </span>
                </p>
            </div>
            <div style="clear: both;"></div>
        </div>

        <!-- Invoice Items -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr>
                    <th style="width:40%; font-size: 14px; text-align: left; border-bottom: 1px solid #000000; padding: 8px; font-weight: bold;">Description</th>
                    <th style="width=:10%; font-size: 14px; text-align: right; border-bottom: 1px solid #000000; padding: 8px; font-weight: bold;">Quantity</th>
                    <th style="width=15%; font-size: 14px; text-align: right; border-bottom: 1px solid #000000; padding: 8px; font-weight: bold;">Unit Price</th>
                    <th style="width=20%; font-size: 14px; text-align: right; border-bottom: 1px solid #000000; padding: 8px; font-weight: bold;">Tax</th>
                    <th style="width=15%; font-size: 14px; text-align: right; border-bottom: 1px solid #000000; padding: 8px; font-weight: bold;">Amount USD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice['invoice_items'] as $invoice_item)
                <tr>
                    <td style="padding: 8px; font-size: 14px; border-bottom: 1px solid rgb(194, 187, 187);">{{$invoice_item['name']}}</td>
                    <td style="padding: 8px; font-size: 14px; border-bottom: 1px solid rgb(194, 187, 187); text-align: right;">{{$invoice_item['quantity']}}</td>
                    <td style="padding: 8px; font-size: 14px; border-bottom: 1px solid rgb(194, 187, 187); text-align: right;">{{$invoice_item['unit_price']}}</td>
                    <td style="padding: 8px; font-size: 14px; border-bottom: 1px solid rgb(194, 187, 187); text-align: right;">{{$invoice_item['tax_percentage'] }}%</td>
                    <td style="padding: 8px; font-size: 14px; border-bottom: 1px solid rgb(194, 187, 187); text-align: right;">{{$invoice_item['amount'] }}</td>
                </tr>
                @endforeach
                <tr>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px; text-align: right; font-size: 14px;">Subtotal</td>
                    <td style="padding: 8px; text-align: right;">{{ $invoice['subtotal']}}</td>
                </tr>
                <tr>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px; text-align: right; font-size: 14px;">TOTAL TAX</td>
                    <td style="padding: 8px; text-align: right; font-size: 14px;">{{$invoice['total_tax'] }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px;"></td>
                    <td colspan="3" style="padding: 0;">
                        <hr style="margin: 0; border: 0.2px solid #272525;">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px;"></td>
                    <td style="padding: 8px; font-size: 14px; font-weight: bold; text-align: right;">TOTAL USD</td>
                    <td style="padding: 8px; font-size: 14px; font-weight: bold; text-align: right;">{{$invoice['total']}}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div style="margin-top: 30px;">
            <p style="font-weight: bold; font-size: 13px;">Due Date: {{ \Carbon\Carbon::parse($invoice['invoice_date'])->format('M j, Y') }} <br>
                <p>To Reorder Email: sales@print702.com</p>
            </p>

            <p style="margin-top: 20px; font-size: 13px;">
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