<?php

namespace App\Http\Controllers;

use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class GalleryController extends Controller
{
    /**
     * Admin — list all gallery images.
     */
    public function index()
    {
        $images = GalleryImage::orderBy('sort_order')->orderByDesc('created_at')->get();
        return view('gallery.admin-index', compact('images'));
    }

    /**
     * Admin — upload new image(s).
     */
    public function store(Request $request)
    {
        $request->validate([
            'images'   => 'required',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            'title'    => 'nullable|string|max:200',
            'category' => 'required|string|in:installation,replacement,sliding_door,commercial,other',
        ]);

        $uploadDir = public_path('uploads/gallery');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $count = 0;
        foreach ($request->file('images') as $file) {
            $filename = time() . '_' . $count . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);

            GalleryImage::create([
                'title'       => $request->input('title') ?: $file->getClientOriginalName(),
                'description' => $request->input('description'),
                'category'    => $request->input('category'),
                'image_path'  => 'uploads/gallery/' . $filename,
                'sort_order'  => 0,
                'is_active'   => true,
                'uploaded_by' => Auth::id(),
            ]);
            $count++;
        }

        return redirect()->route('admin.gallery.index')
            ->with('success', $count . ' image(s) uploaded successfully.');
    }

    /**
     * Admin — update image details.
     */
    public function update(Request $request, $id)
    {
        $image = GalleryImage::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'nullable|string|max:200',
            'description' => 'nullable|string|max:500',
            'category'    => 'required|string|in:installation,replacement,sliding_door,commercial,other',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'nullable|boolean',
        ]);

        $image->update([
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'category'    => $validated['category'],
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => $request->has('is_active'),
        ]);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Image updated.');
    }

    /**
     * Admin — delete image.
     */
    public function destroy($id)
    {
        $image = GalleryImage::findOrFail($id);

        // Delete file from disk
        $filePath = public_path($image->image_path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $image->delete();

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Image deleted.');
    }
}
