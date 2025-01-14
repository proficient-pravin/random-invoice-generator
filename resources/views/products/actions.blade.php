<!-- resources/views/products/actions.blade.php -->
<a href="{{ route('products.edit', $product->id) }}" class="inline-block bg-blue-500 text-white hover:bg-blue-700 font-semibold py-2 px-4 rounded-md transition duration-300 ease-in-out">
    Edit
</a>

<form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="inline-block bg-red-500 text-white hover:bg-red-700 font-semibold py-2 px-4 rounded-md transition duration-300 ease-in-out" onclick="return confirm('Are you sure?')">
        Delete
    </button>
</form>
