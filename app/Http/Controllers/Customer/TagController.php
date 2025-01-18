<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TagController extends Controller
{
   /**
     * Display a listing of the tags.
     */
    public function index()
    {
        if (request()->ajax()) {
            $tags = Tag::query();
            return DataTables::of($tags)
                ->addColumn('actions', function ($tag) {
                    return view('tags.actions', compact('tag'));
                })
                ->addColumn('bg_color', function ($tag) {
                    $tag->name = '';
                    return view('customers.tag', compact('tag'));
                })
                ->make(true);
        }

        return view('tags.index');
    }

    /**
     * Show the form for creating a new tag.
     */
    public function create()
    {
        return view('tags.create');
    }

    /**
     * Store a newly created tag in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            'bg_color' => 'required|string|max:255',
        ]);

        Tag::create($validated);

        return redirect()->route('tags.index')->with('success', 'Tag created successfully!');
    }

    /**
     * Show the form for editing the specified tag.
     *
     * @param \App\Models\Tag $tag
     */
    public function edit(Tag $tag)
    {
        return view('tags.edit', compact('tag'));
    }

    /**
     * Update the specified tag in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Tag $tag
     */
    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
            'bg_color' => 'required|string|max:255',
        ]);

        $tag->update($validated);

        return redirect()->route('tags.index')->with('success', 'Tag updated successfully!');
    }

    /**
     * Remove the specified tag from storage.
     *
     * @param \App\Models\Tag $tag
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('tags.index')->with('success', 'Tag deleted successfully!');
    }
}
