<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; line-height: 1.3; font-size: 16px; font-weight: 500;">
    <div style="width: 100%; max-width: 900px; margin: auto; padding: 20px;">
        <!-- Header -->
        <div style="display: block; margin-bottom: 20px;">
            <img src="https://in.xero.com/api/0rgOqbfdBOeHTbstgwxjrr96vYxtmSXawauqDOdT/getLogo" alt="Logo" style="max-height: 80px;">
        </div>

        <!-- Invoice and Address Details -->
        <div>
            <div>
                <h1 style="font-size: 32px; font-weight: bold; margin: 0; line-height: 1.2;">INVOICE</h1>
                <p>A Team Electrical<br>
                    Attention: Dan Caspi<br>
                    304 S Jones Blvd<br>
                    Suite 689<br>
                    LAS VEGAS NV 89107<br>
                    USA</p>
            </div>
            <div>
                <p>
                    <strong style="display: block;">Invoice Date:</strong> {{ $invoice['invoice_date'] }}<br>
                    <strong style="display: block;">Invoice Number:</strong> {{ $invoice['invoice_number'] }}<br>
                </p>
            </div>
            <div>
                <p>702 Print &amp; Marketing LLC<br>
                    5525 S Decatur Blvd # 106<br>
                    Las Vegas 89118 USA<br>
                    Phone: (702) 945-0936<br>
                    Visit us online: <a href="http://www.print702.com">www.print702.com</a></p>
            </div>
        </div>

        <!-- Invoice Items -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left; font-weight: bold; border-bottom: 1px solid #000000;">Description</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left; font-weight: bold; border-bottom: 1px solid #000000;">Quantity</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left; font-weight: bold; border-bottom: 1px solid #000000;">Unit Price</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left; font-weight: bold; border-bottom: 1px solid #000000;">Tax</th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold; border-bottom: 1px solid #000000;">Amount USD</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice['invoice_items'] as $item)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $item['name'] }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $item['quantity'] }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $item['unit_price'] }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $item['tax'] }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ $item['amount'] }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="4" style="border: 1px solid #ddd; padding: 8px;">Subtotal</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ $invoice['subtotal'] }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="border: 1px solid #ddd; padding: 8px;">TOTAL TAX</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">{{ $invoice['total_tax'] }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="border: none;">
                        <hr style="margin: 0; border: 1px solid #ccc;">
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="font-size: 18px; font-weight: bold; padding: 8px;">TOTAL USD</td>
                    <td style="font-size: 18px; font-weight: bold; text-align: right; padding: 8px;">{{ $invoice['total'] }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div style="margin-top: 30px; font-size: 13px; line-height: 1.5;">
            <p style="font-weight: bold; margin-bottom: 10px;">Due Date: {{ $invoice['invoice_date'] }}</p>
            <p>To Reorder Email: <a href="mailto:sales@print702.com">sales@print702.com</a></p>
            <p style="margin-bottom: 10px;">
                FULL PAYMENT IS REQUIRED AT TIME OF ORDER.<br>
                There is a 3.5% fee on all Card payments made over the phone.<br><br>
                ALL SALES ARE CONSIDERED FINAL.
            </p>
        </div>
    </div>
</body>
</html>
