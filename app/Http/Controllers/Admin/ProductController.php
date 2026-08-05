<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    /** Fixed brand list requested for the Shop's filter sidebar. */
    const BRANDS = ['Apple', 'Samsung', 'Xiaomi', 'Vivo', 'Oppo', 'Realme', 'Motorola', 'Infinix', 'Tecno', 'Poco'];

    public function index(): View
    {
        return view('admin.products.index', [
            'title' => 'Products', 'active' => 'products',
            'products' => Product::with('category', 'images')->orderByDesc('id')->get(),
            'categories' => Category::orderBy('name')->get(),
            'brands' => self::BRANDS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $images = $data['images'] ?? [];
        unset($data['images']);
        $video = $data['video'] ?? null;
        unset($data['video']);
        $data['is_active'] = $request->boolean('is_active');

        $product = Product::create($data);
        $this->syncImages($product, $images);
        if ($video) {
            $this->storeVideo($product, $video);
        }

        return back()->with('success', 'Product added successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);
        $images = $data['images'] ?? [];
        unset($data['images']);
        $video = $data['video'] ?? null;
        unset($data['video']);
        $data['is_active'] = $request->boolean('is_active');

        $product->update($data);

        if (! empty($images)) {
            $this->syncImages($product, $images);
        }

        if ($request->boolean('remove_video') && $product->video_path) {
            Storage::disk('public')->delete($product->video_path);
            $product->update(['video_path' => null]);
        }

        if ($video) {
            $this->storeVideo($product, $video);
        }

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            return back()->with('error', 'Cannot delete a product that already has orders. Mark it inactive instead.');
        }

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        if ($product->video_path) {
            Storage::disk('public')->delete($product->video_path);
        }
        $product->delete();

        return back()->with('success', 'Product removed successfully.');
    }

    /**
     * Replaces the product's image set (max 4) and keeps image_path in
     * sync as the first one, since older views/order records reference
     * that single column directly.
     */
    protected function syncImages(Product $product, array $images): void
    {
        foreach ($product->images as $existing) {
            Storage::disk('public')->delete($existing->path);
        }
        $product->images()->delete();

        $paths = [];
        foreach (array_slice($images, 0, 4) as $i => $file) {
            $path = $file->store('products', 'public');
            $paths[] = $path;
            $product->images()->create(['path' => $path, 'sort_order' => $i]);
        }

        if ($paths) {
            if ($product->image_path && ! in_array($product->image_path, $paths, true)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->update(['image_path' => $paths[0]]);
        }
    }

    /**
     * Replaces the product's uploaded video file. An uploaded file takes
     * display priority over video_url (see Product::hasVideo() usage in the
     * customer-facing show page) -- it isn't cleared here so admins can
     * still fall back to it later by removing the upload.
     */
    protected function storeVideo(Product $product, $file): void
    {
        if ($product->video_path) {
            Storage::disk('public')->delete($product->video_path);
        }
        $path = $file->store('products/videos', 'public');
        $product->update(['video_path' => $path]);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'down_payment' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'storage' => ['nullable', 'string', 'max:50'],
            'ram' => ['nullable', 'string', 'max:50'],
            'network_type' => ['nullable', 'in:3G,4G,5G,4G_5G'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['image', 'max:4096'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm,video/x-msvideo', 'max:20480'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
