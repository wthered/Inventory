<?php

	namespace App\Http\Controllers\Inventory;

	use App\Http\Controllers\Controller;
	use App\Http\Requests\Products\Images\ProductImageStoreRequest;
	use App\Models\Product;
	use Illuminate\Http\JsonResponse;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Str;

	class ProductImageController extends Controller {
		public function store(ProductImageStoreRequest $request, Product $product): JsonResponse {
			$input = $request->validated();
//			dd($input);

			foreach ($request->file('images') as $file) {
				$filename = 'product-' . $product->id . '-' . Str::random(6) . '.' . $file->getClientOriginalExtension();
				$path = $file->storeAs('images', $filename, 'public');

				$product->images()->create([
					'image_location' => Storage::url($path)
				]);
			}

			// Rebuild the preview HTML using the same Blade partial you already have
			$code = $product->images()->get()->map(function ($img) {
				return view('partials.image_item', ['image' => $img->image_location])->render();
			})->implode('');

			return response()->json([
				'success' => !empty($path),
				'code'    => $code,
			]);
		}

		/**
		 * In Create/Update Product pages, we may want to decouple image from product
		 */
		public function delete(Request $request, Product $product): JsonResponse {
			$imageToRemove = $request->input('image');

			$product->images()->where('image_location', $imageToRemove)->delete();

			$code = $product->images()->get()->map(function ($image) {
				return view('partials.image_item', ['image' => $image->image_location])->render();
			})->implode('');

			return response()->json([
				'code'    => $code,
				'success' => !empty($code)
			]);
		}
	}
