<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Invoices</title>
    <!-- Add Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div style="display: block; text-align: center; height: auto;">
        <img src="{{ asset('702logo.png') }}" style="max-height: 83px; max-width: 300px;" alt="Logo">
    </div>
    <div class=" container mt-5">
        @if (session('success'))
            <div class="alert alert-success mt-4">
                {{ session('success') }}
            </div>
        @endif
        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger mt-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Generate Invoices</h2>
        <form method="POST" action="{{ route('generate.invoices') }}" id="invoiceForm">
            @csrf
            <div class="form-row">
                <!-- Start Date -->
                <div class="form-group col-md-6">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" name="start_date" required>
                </div>

                <!-- End Date -->
                <div class="form-group col-md-6">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" name="end_date" required>
                </div>
            </div>

            <div class="form-row">
                <!-- Start Invoice Number -->
                <div class="form-group col-md-6">
                    <label for="start_invoice_number">Start Invoice Number</label>
                    <input type="number" class="form-control" name="start_invoice_number"
                        value="{{ $startInvoiceNumber }}" required>
                </div>

                <!-- Number of Invoices -->
                <div class="form-group col-md-6">
                    <label for="num_invoices">Number of Invoices</label>
                    <input type="number" class="form-control" name="num_invoices">
                </div>
                <div class="form-group col-md-6">
                    <!-- Total Amount -->
                    <label for="tax_percentage">Tax Percentage</label>
                    <input type="float" class="form-control" name="tax_percentage" required>
                </div>
                <div class="form-group col-md-6">
                    <!-- Total Amount -->
                    <label for="total_amount">Total Amount</label>
                    <input type="float" class="form-control" name="total_amount" required>
                </div>
            </div>

            <div class="form-group text-center">
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Generate Invoices</button>
            </div>
        </form>

        <!-- Loader -->
        <div id="loader" class="text-center mt-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>


    </div>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Update Product and Customer CSV</h2>

        <form method="POST" action="{{ route('update.csv') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-row">
                <!-- Upload Product CSV -->
                <div class="form-group col-md-6">
                    <label for="product_csv">Upload Product CSV</label>
                    <input type="file" class="form-control" name="product_csv" accept=".csv">
                </div>

                <!-- Upload Customer CSV -->
                <div class="form-group col-md-6">
                    <label for="customer_csv">Upload Customer CSV</label>
                    <input type="file" class="form-control" name="customer_csv" accept=".csv">
                </div>
            </div>

            <div class="form-group text-center">
                <!-- Submit Button -->
                <button type="submit" class="btn btn-success">Update CSV Files</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
