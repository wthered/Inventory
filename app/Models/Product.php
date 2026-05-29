<?php

	namespace App\Models;

	use App\Traits\Products\ProductAttributes;
	use App\Traits\Products\ProductRelations;
	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\SoftDeletes;
	use Illuminate\Support\Carbon;

	/**
	 * App\Models\Product
	 *
	 * @property int $id Primary key: Unique product identifier
	 * @property string $sku Stock Keeping Unit: unique product code
	 * @property string|null $barcode Optional barcode number for scanning
	 * @property string $name Product name
	 * @property string $slug Unique URL-friendly identifier
	 * @property string|null $description Detailed product description
	 * @property int|null $category_id Foreign key referencing categories table
	 * @property int|null $brand_id Foreign key referencing brands table
	 * @property float $cost_price Purchase or manufacturing cost
	 * @property float $selling_price Regular selling price
	 * @property float|null $discount_price Optional discounted price
	 * @property string $unit Unit of measurement: pcs, kg, liter, etc.
	 * @property int $min_stock_level Minimum stock threshold before alert
	 * @property int|null $max_stock_level Maximum allowed stock limit
	 * @property int $reorder_point Quantity at which to reorder
	 * @property int $current_stock Current available quantity in inventory
	 * @property bool $track_inventory Enable inventory tracking
	 * @property bool $is_active Product active status
	 * @property array|null $specifications Additional product attributes in JSON format
	 * @property Carbon|null $created_at
	 * @property Carbon|null $updated_at
	 * @property Carbon|null $deleted_at
	 *
	 */
	class Product extends Model {
		use HasFactory, SoftDeletes, ProductRelations, ProductAttributes;

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<string>
		 */
		protected $fillable = [
			'sku',
			'barcode',
			'name',
			'slug',
			'description',
			'category_id',
			'brand_id',
			'cost_price',
			'selling_price',
			'discount_price',
			'unit',
			'min_stock_level',
			'max_stock_level',
			'reorder_point',
			'current_stock',
			'track_inventory',
			'is_active',
			'specifications',
		];

		/**
		 * The attributes that should be cast to native types.
		 *
		 * @var array<string, string>
		 */
		protected $casts = [
			'cost_price'      => 'float',
			'selling_price'   => 'float',
			'discount_price'  => 'float',
			'min_stock_level' => 'integer',
			'max_stock_level' => 'integer',
			'reorder_point'   => 'integer',
			'current_stock'   => 'integer',
			'track_inventory' => 'boolean',
			'is_active'       => 'boolean',
			'specifications'  => 'array',
			'created_at'      => 'datetime',
			'updated_at'      => 'datetime',
			'deleted_at'      => 'datetime',
		];
	}
