<?php

	namespace App\Traits;

	use App\Models\ActivityLog;
	use Carbon\Carbon;
	use DB;
	use Illuminate\Database\Eloquent\Collection;
	use Illuminate\Support\Facades\Auth;

	trait LogsActivity {
		/**
		 * Disable automatic logging for specific events.
		 * Set to false if you don't want auto-logging.
		 */
		protected static bool $logEvents = true;

		/**
		 * Attributes to exclude from logging (sensitive data).
		 */
		protected array $hiddenLogAttributes = [
			'password',
			'remember_token',
			'api_token',
			'secret_key',
			'credit_card_number',
		];

		/**
		 * Boot the trait.
		 */
		protected static function bootLogsActivity(): void {
			// Auto-log on model events if enabled
			if (static::$logEvents) {
				static::created(function ($model) {
					$model->logActivity('created', $model->getLoggableAttributes(), 'created');
				});

				static::updated(function ($model) {
					// Only log if something actually changed
					if ($model->wasChanged()) {
						$model->logActivity('updated', [
							'old'     => $model->getOriginal(),
							'new'     => $model->getLoggableAttributes(),
							'changed' => $model->getChanges(),
						], 'updated');
					}
				});

				static::deleted(function ($model) {
					// Only log soft deletes, not force deletes
					if (!method_exists($model, 'forceDelete') || !$model->isForceDeleting()) {
						$model->logActivity('deleted', $model->getLoggableAttributes(), 'deleted');
					}
				});

				static::restored(function ($model) {
					$model->logActivity('restored', $model->getLoggableAttributes(), 'restored');
				});
			}
		}

		/**
		 * Log an activity.
		 */
		public function logActivity(string $description, array $properties = [], ?string $event = null, ?string $logName = null): ActivityLog {
			// Format the description
			$formattedDescription = $this->getActivityDescription($description);

			// Clean properties
			$cleanedProperties = $this->cleanProperties($properties);

			// Get the causer (user)
			$causer = Auth::user();

			// Create the activity log
			return ActivityLog::create([
				'log_name'     => $logName ?? $this->getLogName(),
				'description'  => $formattedDescription,
				'subject_type' => get_class($this),
				'subject_id'   => $this->getKey(),
				'causer_type'  => $causer ? get_class($causer) : null,
				'causer_id'    => $causer?->id,
				'properties'   => $cleanedProperties,
				'event'        => $event,
				'ip_address'   => request()->ip(),
				'user_agent'   => request()->userAgent(),
			]);
		}

		/**
		 * Get the activity description.
		 */
		protected function getActivityDescription(string $description): string {
			return class_basename($this) . " " . $this->getActivityIdentifier() . ": " . $description;
		}

		/**
		 * Get identifier for activity description.
		 */
		protected function getActivityIdentifier(): string {
			// Try to find the best identifier for this model
			if (isset($this->return_number)) {
				return "#$this->return_number";
			}

			if (isset($this->name)) {
				return "'$this->name'";
			}

			if (isset($this->email)) {
				return "'$this->email'";
			}

			if (isset($this->title)) {
				return "'$this->title'";
			}

			return "#{$this->getKey()}";
		}

		/**
		 * Clean properties before storing (remove sensitive data).
		 */
		protected function cleanProperties(array $properties): array {
			return collect($properties)->map(function ($value) {
				// Remove sensitive keys from nested arrays
				if (is_array($value)) {
					foreach ($this->hiddenLogAttributes as $sensitiveKey) {
						if (isset($value[$sensitiveKey])) {
							unset($value[$sensitiveKey]);
						}
					}
				}

				// Convert objects to arrays
				if (is_object($value) && method_exists($value, 'toArray')) {
					return $value->toArray();
				}

				return $value;
			})
			->reject(fn($value, $key) => in_array($key, $this->hiddenLogAttributes))
			->toArray();
		}

		/**
		 * Get the log name for the model.
		 */
		protected function getLogName(): string {
			// Use custom log name if defined, otherwise use class basename
			return $this->logName ?? class_basename($this);
		}

		/**
		 * Get the attributes that should be logged.
		 */
		protected function getLoggableAttributes(): array {
			// If model defines specific log attributes, use those
			if (property_exists($this, 'logAttributes')) {
				return collect($this->logAttributes)
					->filter(fn($attribute) => isset($this->{$attribute}))
					->mapWithKeys(fn($attribute) => [$attribute => $this->{$attribute}])
					->toArray();
			}

			// Default: log all fillable attributes except hidden ones
			return collect($this->getAttributes())
				->only($this->getFillable())
				->reject(fn($value, $key) => in_array($key, $this->hiddenLogAttributes))
				->toArray();
		}

		/**
		 * Get the latest activity log for this model.
		 */
		public function latestActivity() {
			return $this
				->morphOne(ActivityLog::class, 'subject')
				->latestOfMany();
		}

		/**
		 * Get activities by event type.
		 */
		public function getActivitiesByEvent(string $event): Collection {
			return $this
				->activityLogs()
				->where('event', $event)
				->get();
		}

		/**
		 * Get activity logs for this model.
		 */
		public function activityLogs() {
			return $this->morphMany(ActivityLog::class, 'subject');
		}

		/**
		 * Get activities within date range.
		 */
		public function getActivitiesBetweenDates($startDate, $endDate): Collection {
			return $this
				->activityLogs()
				->whereBetween('created_at', [
					$startDate,
					$endDate
				])
				->get();
		}

		/**
		 * Get activity count.
		 */
		public function getActivityCount(): int {
			return $this->activityLogs()->count();
		}

		/**
		 * Get activity summary by event.
		 */
		public function getActivitySummary(): array {
			return $this
				->activityLogs()
				->select('event', DB::raw('count(*) as count'))
				->groupBy('event')
				->pluck('count', 'event')
				->toArray();
		}

		/**
		 * Log an error activity.
		 */
		public function logError(string $description, array $properties = []): ActivityLog {
			return $this->logActivity($description, $properties, 'error', 'errors');
		}

		/**
		 * Log a warning activity.
		 */
		public function logWarning(string $description, array $properties = []): ActivityLog {
			return $this->logActivity($description, $properties, 'warning', 'warnings');
		}

		/**
		 * Log an info activity.
		 */
		public function logInfo(string $description, array $properties = []): ActivityLog {
			return $this->logActivity($description, $properties, 'info', 'info');
		}

		/**
		 * Disable automatic logging for this instance.
		 */
		public function disableLogging(): self {
			static::$logEvents = false;
			return $this;
		}

		/**
		 * Enable automatic logging for this instance.
		 */
		public function enableLogging(): self {
			static::$logEvents = true;
			return $this;
		}

		public function logDeletionAttempt(string $reason = null, array $additionalProperties = []): ActivityLog {
			$deletion_data = array_merge([
				'attempted_by'      => Auth::id(),
				'attempted_by_name' => auth()->user()?->name,
				'attempted_at'      => Carbon::now(),
				'reason'            => $reason,
				'ip_address'        => request()->ip(),
				'user_agent'        => request()->userAgent(),
			], $additionalProperties);
			return $this->logCustomActivity("Deletion attempt recorded", $deletion_data,'deletion_attempted');
		}

		// Add to LogsActivity trait:

		/**
		 * Log a custom activity without automatic model events.
		 */
		public function logCustomActivity(string $description, array $properties = [], ?string $event = null, ?string $logName = null): ActivityLog {
			return $this->logActivity($description, $properties, $event, $logName);
		}

		// Add to your App\LogsActivity trait:

		/**
		 * Log a successful transfer deletion (AFTER soft deletion)
		 * Use this in the observer's deleted() method
		 */
		public function logDeletionCompleted(?string $reason = null, array $context = []): ActivityLog
		{
			// Note: $this still exists in memory after soft deletion
			$deletionType = $this->isForceDeleting() ? 'force' : 'soft';

			$properties = array_merge([
				'deletion_type' => $deletionType,
				'reason' => $reason,
				'reference_number' => $this->reference_number,
				'transfer_id' => $this->id,
				'deleted_by' => auth()->id(),
				'deleted_by_name' => auth()->user()?->name,
				'deleted_at' => $this->deleted_at ?? now(),
				'final_status' => $this->status_id,
				'final_quantity' => $this->quantity,
				'product_id' => $this->product_id,
				'from_inventory' => $this->from_inventory_id,
				'to_inventory' => $this->to_inventory_id,
				'recoverable' => $deletionType === 'soft',
				'can_be_restored' => $deletionType === 'soft',
				'restoration_deadline_days' => $deletionType === 'soft' ? 30 : 0,
			], $context);

			$description = "Transfer {$this->reference_number} was {$deletionType} deleted";

			// If soft delete, add recovery info
			if ($deletionType === 'soft') {
				$description .= " (recoverable)";
			}

			return $this->logCustomActivity(
				$description,
				$properties,
				'deletion_completed'
			);
		}

		/**
		 * Alias for logDeletionCompleted for consistency
		 */
		public function logDeletionSuccess(?string $reason = null, array $context = []): ActivityLog
		{
			return $this->logDeletionCompleted($reason, $context);
		}
	}
