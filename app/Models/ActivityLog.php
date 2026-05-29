<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\MorphTo;

	class ActivityLog extends Model {
		/**
		 * The table associated with the model.
		 *
		 * @var string
		 */
		protected $table = 'activity_logs';

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array
		 */
		protected $fillable = [
			'log_name',
			'description',
			'subject_type',
			'subject_id',
			'causer_type',
			'causer_id',
			'properties',
			'event',
			'ip_address',
			'user_agent',
		];

		/**
		 * The attributes that should be cast.
		 *
		 * @var array
		 */
		protected $casts = [
			'properties' => 'array',
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
		];

		/**
		 * Get the subject of the activity.
		 */
		public function subject(): MorphTo {
			return $this->morphTo();
		}

		/**
		 * Get the user that caused the activity.
		 */
		public function causer(): MorphTo {
			return $this->morphTo();
		}
	}
