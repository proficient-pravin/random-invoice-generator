<!-- resources/views/invoices/actions.blade.php -->
<a href="{{ route('invoices.download', $customer->id) }}" class="inline-block bg-blue-500 text-white hover:bg-blue-600 p-1 rounded-full transition duration-300">
    <!-- Download Icon -->
    <i class="fas fa-download text-lg"></i> <!-- Font Awesome download icon with smaller size adjustment -->
</a>
