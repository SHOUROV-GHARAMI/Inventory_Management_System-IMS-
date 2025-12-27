<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    
    public function upload(Request $request, $productId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $product = Product::findOrFail($productId);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $path = $request->file('image')->store('products', 'public');
        
        $product->image = $path;
        $product->save();

        AuditLog::logAction('updated', $product, ['image' => $product->getOriginal('image')], ['image' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => [
                'image' => $path,
                'url' => Storage::disk('public')->url($path)
            ]
        ]);
    }

    public function uploadMultiple(Request $request, $productId)
    {
        $request->validate([
            'images' => 'required|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $product = Product::findOrFail($productId);
        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('products', 'public');
            $uploadedImages[] = $path;
        }

        $existingImages = $product->images ?? [];
        $allImages = array_merge($existingImages, $uploadedImages);
        
        $product->images = $allImages;
        $product->save();

        AuditLog::logAction('updated', $product, ['images' => $existingImages], ['images' => $allImages]);

        return response()->json([
            'success' => true,
            'message' => count($uploadedImages) . ' images uploaded successfully',
            'data' => [
                'images' => $allImages,
                'urls' => array_map(fn($path) => Storage::disk('public')->url($path), $allImages)
            ]
        ]);
    }

    public function delete($productId)
    {
        $product = Product::findOrFail($productId);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
            
            AuditLog::logAction('updated', $product, ['image' => $product->image], ['image' => null]);
            
            $product->image = null;
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image found'
        ], 404);
    }

    public function deleteFromGallery(Request $request, $productId)
    {
        $request->validate([
            'image' => 'required|string'
        ]);

        $product = Product::findOrFail($productId);
        $images = $product->images ?? [];

        if (in_array($request->image, $images)) {
            Storage::disk('public')->delete($request->image);
            
            $newImages = array_values(array_diff($images, [$request->image]));
            
            AuditLog::logAction('updated', $product, ['images' => $images], ['images' => $newImages]);
            
            $product->images = $newImages;
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
                'data' => [
                    'images' => $newImages
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Image not found'
        ], 404);
    }
}
