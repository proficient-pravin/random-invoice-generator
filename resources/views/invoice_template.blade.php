<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice['invoice_number'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .invoice-header img {
            width: 150px;
            height: auto;
        }

        .invoice-header h2 {
            font-size: 24px;
            color: #4CAF50;
            margin: 0;
        }

        .invoice-header p {
            font-size: 14px;
            color: #777;
            margin: 0;
        }

        .customer-details, .product-details, .invoice-summary {
            margin-bottom: 20px;
        }

        .customer-details h3, .product-details p, .invoice-summary p {
            font-size: 16px;
            margin: 5px 0;
        }

        .customer-details p, .product-details p {
            font-size: 14px;
            color: #555;
        }

        .invoice-summary {
            font-size: 18px;
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        .invoice-summary p {
            font-size: 16px;
        }

        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #4CAF50;
            margin-top: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #4CAF50;
            color: white;
        }

        .table td {
            background-color: #f9f9f9;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #777;
        }

    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <img src="https://brandslogos.com/wp-content/uploads/images/stripe-logo.png" alt="Logo" />
            <div>
                <h2>Invoice #{{ $invoice['invoice_number'] }}</h2>
                <p>Date: {{ $invoice['invoice_date'] }}</p>
            </div>
        </div>
        
        <div class="customer-details">
            <h3>Customer Details:</h3>
            <p><strong>Name:</strong> {{ $invoice['customer_name'] }}</p>
            <p><strong>Email:</strong> {{ $invoice['customer_email'] }}</p>
            <p><strong>Phone:</strong> {{ $invoice['customer_phone'] }}</p>
        </div>

        <div class="product-details">
            <h3>Product Details:</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $invoice['product_name'] }}</td>
                        <td>${{ number_format($invoice['product_price'], 2) }}</td>
                        <td>{{ $invoice['quantity'] }}</td>
                        <td>${{ number_format($invoice['amount'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="invoice-summary">
            <p><strong>Subtotal:</strong> ${{ number_format($invoice['amount'], 2) }}</p>
            <p><strong>Tax ({{ $invoice['tax_rate'] }}%):</strong> ${{ number_format($invoice['tax'], 2) }}</p>
            <div class="total-amount">
                <p><strong>Total:</strong> ${{ number_format($invoice['total'], 2) }}</p>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>
