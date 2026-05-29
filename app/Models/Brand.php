<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class Brand extends Model {
		public function product(): BelongsTo {
			return $this->belongsTo(Product::class, 'id', 'brand_id');
		}
	}
