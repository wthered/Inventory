<?php

	namespace App\DataTransferObjects;

	use App\Enums\ProductUnit;
	use App\Models\Brand;
	use App\Models\Category;
	use App\Models\Product;
	use Illuminate\Pagination\LengthAwarePaginator;
	use Illuminate\Support\Collection;

	class ProductDTO {
		public Product               $product;
		public int                   $id;
		public string                $sku;
		public ?string               $barcode;
		public string                $name;
		public string                $slug;
		public ?string               $description;
		public ?Category             $category;
		public ?Category             $parent;
		public float                 $cost_price;
		public float                 $selling_price;
		public ?float                $discount_price;
		public ProductUnit           $unit;
		public int                   $min_stock_level;
		public ?int                  $max_stock_level;
		public ?int                  $current_stock;
		public int                   $reorder_point;
		public bool                  $track_inventory;
		public bool                  $is_active;
		public ?array                $specifications;
		public ?Collection           $images;
		public ?Brand                $brand;
		public ?Collection           $suppliers;
		public ?Collection           $warehouses;
		public ?Collection           $locations;
		public ?LengthAwarePaginator $inventories;
		public ?Collection           $transactions;
		public ?Collection           $history;

		public function __construct(int $product) {
			$this->product = Product::query()->findOrFail($product);
			$this->id = $product;
			$this->sku = $this->product['sku'];
			$this->barcode = $this->product['barcode'] ?? null;
			$this->name = $this->product['name'];
			$this->slug = $this->product['slug'];
			$this->description = $this->product['description'] ?? null;

			$this->category = $this->product->category()->first();
			$this->parent = !is_null($this->category->parent_id) ? Category::query()->findOrFail($this->category->parent_id) : null;
			$this->cost_price = $this->product['cost_price'];
			$this->selling_price = $this->product['selling_price'];
			$this->discount_price = $this->product['discount_price'] ?? null;
			$this->unit = ProductUnit::from($this->product['unit']);
			$this->min_stock_level = $this->product['min_stock_level'];
			$this->max_stock_level = $this->product['max_stock_level'] ?? null;
			$this->current_stock = $this->product['current_stock'];
			$this->reorder_point = $this->product['reorder_point'];
			$this->track_inventory = $this->product['track_inventory'];
			$this->is_active = $this->product['is_active'];

			$this->specifications = json_decode($this->product['specifications'], true);
			$this->images = $this->product->images()->get();
			$this->brand = $this->product->brand()->first();
			$this->suppliers = $this->product->suppliers()->get();
			$this->warehouses = $this->product->warehouses()->orderBy('warehouses.name')->get();
			$this->locations = $this->product->locations()->get();
			$this->inventories = $this->product->inventories()->whereHas('location')->where('quantity', '>', 0)->orderBy('warehouse_id')->paginate(25);
			$this->transactions = $this->product->transactions()->orderBy('updated_at', 'desc')->get();
			$this->history = $this->product->history()->get();
		}

		/**
		 * Create a new product in the database and return a DTO instance.
		 *
		 * @param  array  $data
		 *
		 * @return self|null
		 */
		public static function create(array $data): ?self {
			$product = Product::query()->create($data);

			if (!$product) {
				return null;
			}

//			dd(new self($product));
			return new self($product);
		}

		/**
		 * Update an existing product and return a DTO instance.
		 *
		 * @param  Product  $product
		 * @param  array    $data
		 *
		 * @return self
		 */
		public static function update(Product $product, array $data): self {
			// $data['specifications'] is an array. Eloquent automatically encodes it as JSON on update.
			$product->update($data);

			return new self($product->id);
		}
	}
