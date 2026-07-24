<?php

	namespace App\Http\Controllers\Inventory;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\Products\Images\ProductImageDeleteRequest;
	use App\Http\Requests\Products\Images\ProductImageStoreRequest;
	use App\Models\Product;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Str;

	class ProductImageController extends Controller {
		public function store(ProductImageStoreRequest $request, Product $product): JsonResponse {
			$validated = $request->validated();
			$uploadedPaths = [];

			foreach ($validated['images'] as $file) {
				$filename = 'product-'.$product->id.'-'.Str::random(6).'.'.$file->getClientOriginalExtension();
				$path = $file->storeAs('images', $filename, 'public');

				$product->images()->create([
					'image_location' => Storage::url($path)
				]);

				$uploadedPaths[] = $path;
			}

			// Rebuild the preview HTML
			$code = $product->images()->get()->map(function ($img) {
				return view('partials.image_item', ['image' => $img->image_location])->render();
			})->implode('');

			return response()->json([
				'success' => !empty($uploadedPaths),
				'code'    => $code,
			]);
		}

		/**
		 * Detach and delete an image record from the product,
		 * removing local files from disk if applicable.
		 */
		public function destroy(ProductImageDeleteRequest $request, Product $product): JsonResponse {
			$imageToRemove = $request->validated('image');

			// Έλεγχος αν το προϊόν έχει μόνο μία εικόνα συνολικά
			if ($product->images()->count() <= 1) {
				return response()->json([
					'success' => false,
					'message' => 'Δεν μπορείτε να διαγράψετε τη μοναδική εικόνα του προϊόντος.'
				], 422);
			}

			$images = $product->images()->where('image_location', $imageToRemove)->get();

			if ($images->isEmpty()) {
				return response()->json([
					'success' => false,
					'message' => 'Η εικόνα δεν βρέθηκε.'
				], 404);
			}

			$wasDefault = false;

			foreach ($images as $img) {
				if ($img->is_default) {
					$wasDefault = true;
				}

				if (!Str::startsWith($img->image_location, ['http://', 'https://'])) {
					$relativeStoragePath = Str::after($img->image_location, '/storage/');

					if (Storage::disk('public')->exists($relativeStoragePath)) {
						Storage::disk('public')->delete($relativeStoragePath);
					}
				}
			}

			// Διαγραφή από τη βάση
			$product->images()->where('image_location', $imageToRemove)->delete();

			// Ορισμός νέας default εικόνας
			if ($wasDefault) {
				$nextImage = $product->images()->first();
				if ($nextImage) {
					$product->images()
					        ->where('image_location', $nextImage->image_location)
					        ->update(['is_default' => 1]);
				}
			}

			// Rebuild partial Blade view
			$code = $product->images()->get()->map(function ($image) {
				return view('partials.image_item', ['image' => $image->image_location])->render();
			})->implode('');

			return response()->json([
				'code'    => $code,
				'success' => true
			]);
		}
	}
