<!-- Preview Button -->
<button data-url="{{ route('invoices.preview', $customer->id) }}" class="inline-block bg-green-500 text-white hover:bg-green-600 p-1 rounded-md transition duration-300 openOnvoicePreviewModal">
    Preview
</button>

{{-- <!-- Download Button inside Modal -->
<a href="{{ route('invoices.download', $customer->id) }}" class="inline-block bg-blue-500 text-white hover:bg-blue-600 p-1 rounded-full transition duration-300">
    <i class="fas fa-download text-lg"></i> <!-- Download Icon -->
</a> --}}
