<?php

	use App\Enums\WarehouseType;
	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 */
		public function up(): void {
			Schema::create('warehouses', function (Blueprint $table) {
				$table->increments('id');
				$table->string('code')->unique();
				$table->string('name');
				$table->string('type')->default(WarehouseType::GENERAL->value);
				$table->string('description')->nullable();
				$table->text('address')->nullable();
				$table->string('city')->nullable();
				$table->string('state')->nullable();
				$table->string('country')->nullable();
				$table->string('postal_code')->nullable();
				$table->string('phone')->nullable();
				$table->string('email')->nullable();
				$table->unsignedInteger('manager_id')->nullable();
				$table->unsignedTinyInteger('zones')->default(1);
				$table->unsignedTinyInteger('aisles')->default(1);
				$table->unsignedTinyInteger('racks')->default(0);
				$table->unsignedTinyInteger('shelves')->default(0);
				$table->unsignedTinyInteger('bins')->default(0);
				$table->foreign('manager_id')->references('id')->on('users')->nullOnDelete();
				$table->boolean('is_primary')->default(false);
				$table->timestamps();
				$table->softDeletes();

				$table->comment('Primary entity defining physical storage facilities. Stores organizational details for each warehouse, acting as the root entity for all inventory and location records.');
			});

			Schema::create('warehouse_locations', function (Blueprint $table) {
				$table->increments('id');

				$table->unsignedInteger('warehouse_id')->nullable();
				$table->foreign('warehouse_id', 'warehouse_locations_warehouse_foreign')
					->references('id')
					->on('warehouses')
					->cascadeOnDelete();

				$table->string('code')->unique();
				$table->string('name');
				$table->string('zone')->nullable();
				$table->string('aisle')->nullable();
				$table->unsignedTinyInteger('rack')->nullable();
				$table->unsignedTinyInteger('shelf')->nullable();
				$table->unsignedTinyInteger('bin')->nullable();
				// Συνολική χωρητικότητα σε τετραγωνικά μέτρα
				$table->float('capacity', 12, 2)->default(0)->after('bins')->unsigned();
				// Τρέχουσα χρήση (μπορεί να ενημερώνεται αυτόματα ή χειροκίνητα)
				$table->float('current_capacity', 12, 2)->default(0)->after('capacity')->unsigned();

				$table->text('description')->nullable();

				$table->timestamps();
				$table->softDeletes();

				$table->unique([
					'warehouse_id',
					'zone',
					'aisle',
					'rack',
					'shelf',
					'bin'
				], 'warehouse_locations_unique');

				$table->comment('Defines the hierarchical storage map (Zone, Aisle, Rack, Shelf, Bin). Records every unique, available storage slot within a warehouse and acts as the location master data for inventory placement.');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void {
			Schema::dropIfExists('warehouse_locations');
			Schema::dropIfExists('warehouses');
		}
	};
