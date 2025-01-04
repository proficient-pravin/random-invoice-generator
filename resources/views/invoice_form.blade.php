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
    <div class="container mt-5">
        <h2 class="text-center mb-4">Generate Invoices</h2>
        <form method="POST" action="{{ route('generate.invoices') }}">
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
                    <input type="number" class="form-control" name="start_invoice_number" required>
                </div>

                <!-- Number of Invoices -->
                <div class="form-group col-md-6">
                    <label for="num_invoices">Number of Invoices</label>
                    <input type="number" class="form-control" name="num_invoices" required>
                </div>
            </div>

            <div class="form-group">
                <!-- Total Amount -->
                <label for="total_amount">Total Amount</label>
                <input type="number" class="form-control" name="total_amount" required>
            </div>

            <div class="form-group text-center">
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Generate Invoices</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
