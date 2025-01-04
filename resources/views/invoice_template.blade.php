<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice['invoice_number'] }}</title>
    <style>
        /* Add your invoice styles here */
    </style>
</head>
<body>
    <div class="invoice-header">
        <img src="{{ public_path('logo.png') }}" alt="Logo" />
        <h2>Invoice #{{ $invoice['invoice_number'] }}</h2>
        <p>Date: {{ $invoice['invoice_date'] }}</p>
    </div>
    <div class="customer-details">
        <h3>Customer: {{ $invoice['customer_name'] }}</h3>
        <p>Email: {{ $invoice['customer_email'] }}</p>
        <p>Phone: {{ $invoice['customer_phone'] }}</p>
    </div>
    <div class="product-details">
        <p>Product: {{ $invoice['product_name'] }}</p>
        <p>Price: ${{ number_format($invoice['product_price'], 2) }}</p>
        <p>Quantity: {{ $invoice['quantity'] }}</p>
    </div>
    <div class="invoice-summary">
        <p>Amount: ${{ number_format($invoice['amount'], 2) }}</p>
        <p>Tax: ${{ number_format($invoice['tax'], 2) }}</p>
        <p><strong>Total: ${{ number_format($invoice['total'], 2) }}</strong></p>
    </div>
</body>
</html>
