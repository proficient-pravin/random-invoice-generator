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
    <div class="container mt-3">
        <!-- Logo Section -->
        <div class="d-flex justify-content-end">
            <img src="{{ asset('702logo.png') }}" style="max-height: 83px; max-width: 300px;" alt="Logo">
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
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

        <!-- Invoice Generation Section -->
        <div class="card mt-5">
            <div class="card-header text-center">
                <h4>Generate Invoices</h4>
            </div>
            <div class="card-body">
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
                            <input type="number" class="form-control" name="start_invoice_number" value="{{ $startInvoiceNumber }}" required>
                        </div>

                        <!-- Number of Invoices -->
                        <div class="form-group col-md-6">
                            <label for="num_invoices">Number of Invoices</label>
                            <input type="number" class="form-control" name="num_invoices">
                        </div>
                    </div>

                    <div class="form-row">
                        <!-- Tax Percentage -->
                        <div class="form-group col-md-6">
                            <label for="tax_percentage">Tax Percentage</label>
                            <input type="number" step="0.01" class="form-control" name="tax_percentage" required>
                        </div>

                        <!-- Total Amount -->
                        <div class="form-group col-md-6">
                            <label for="total_amount">Total Amount</label>
                            <input type="number" step="0.01" class="form-control" name="total_amount" required>
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
        </div>

        <!-- CSV Update Section -->
        <div class="card mt-5">
            <div class="card-header text-center">
                <h4>Update Product and Customer CSV</h4>
            </div>
            <div class="card-body">
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
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
