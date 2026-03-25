<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalImage;
use App\Models\GlobalImageCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GlobalImageController extends Controller
{
    /**
     * List categories
     */
    public function index()
    {
        $categories = GlobalImageCategory::withCount('images')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.global-images.index', compact('categories'));
    }

    /**
     * Show create category form
     */
    public function createCategory()
    {
        return view('admin.global-images.category-form');
    }

    /**
     * Store new category
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->name);
        $maxOrder = GlobalImageCategory::max('sort_order') ?? 0;

        GlobalImageCategory::create([
            'name' => $request->name,
            'slug' => $slug,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.global-images.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Show edit category form
     */
    public function editCategory($id)
    {
        $category = GlobalImageCategory::findOrFail($id);
        return view('admin.global-images.category-form', compact('category'));
    }

    /**
     * Update category
     */
    public function updateCategory(Request $request, $id)
    {
        $category = GlobalImageCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.global-images.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Delete category
     */
    public function deleteCategory($id)
    {
        $category = GlobalImageCategory::findOrFail($id);

        foreach ($category->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        }

        $category->delete();

        return redirect()->route('admin.global-images.index')
            ->with('success', 'Category and its images deleted successfully!');
    }

    /**
     * Show category images (manage images in a category)
     */
    public function showCategory($id)
    {
        $category = GlobalImageCategory::with('images')->findOrFail($id);
        return view('admin.global-images.images', compact('category'));
    }

    /**
     * Upload images to category
     */
    public function uploadImages(Request $request, $id)
    {
        $category = GlobalImageCategory::findOrFail($id);

        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:10240',
        ]);

        $basePath = 'global-image-library/' . $category->slug;

        if (!Storage::disk('public')->exists($basePath)) {
            Storage::disk('public')->makeDirectory($basePath);
        }

        $uploaded = 0;
        $maxOrder = GlobalImage::where('category_id', $category->id)->max('sort_order') ?? 0;

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs($basePath, $filename, 'public');

                GlobalImage::create([
                    'category_id' => $category->id,
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'sort_order' => ++$maxOrder,
                ]);
                $uploaded++;
            }
        }

        return redirect()->route('admin.global-images.show', $category->id)
            ->with('success', $uploaded . ' image(s) uploaded successfully!');
    }

    /**
     * Delete image
     */
    public function deleteImage(Request $request, $categoryId, $imageId)
    {
        $image = GlobalImage::where('category_id', $categoryId)->findOrFail($imageId);

        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        return redirect()->route('admin.global-images.show', $categoryId)
            ->with('success', 'Image deleted successfully!');
    }
}
