<?php

	namespace App\Notifications\Transfers;

	use App\Models\Transfer;
	use App\Models\User;
	use Carbon\Carbon;
	use DateTime;
	use Illuminate\Bus\Queueable;
	use Illuminate\Notifications\Messages\MailMessage;
	use Illuminate\Notifications\Notification;

	class TransferCompletedNotification extends Notification {
		use Queueable;

		/**
		 * The transfer instance.
		 */
		public Transfer $transfer;

		/**
		 * The user who completed the transfer (optional).
		 */
		public ?User $completedBy;

		/**
		 * Completion notes (optional).
		 */
		public ?string $notes;

		/**
		 * Create a new notification instance.
		 */
		public function __construct(Transfer $transfer, ?User $completedBy = null, ?string $notes = null) {
			$this->transfer    = $transfer;
			$this->completedBy = $completedBy;
			$this->notes       = $notes;
		}

		/**
		 * Get the notification's delivery channels.
		 */
		public function via(object $notifiable): array {
			// TODO: Configure based on user preferences
			return [
				'mail',
				'database'
			];
		}

		/**
		 * Get the mail representation of the notification.
		 */
		public function toMail(object $notifiable): MailMessage {
			$mail = (new MailMessage())
				->subject("Transfer Completed: {$this->transfer->reference_number}")
				->greeting("Hello ".$notifiable->name)
				->line("A transfer has been marked as completed.")
				->line("**Transfer Reference:** {$this->transfer->reference_number}")
				->line("**Product:** [Product Name Here]")
				->line("**Quantity:** ".$this->transfer->quantity)
				->line("**From:** [Source Location Here]")
				->line("**To:** [Destination Location Here]")
				->line("**Status:** Completed")
				->line("**Completed At:** " . Carbon::now()->format('Y-m-d H:i:s'));

			if ($this->completedBy) {
				$mail->line("**Completed By:** {$this->completedBy->name}");
			}

			if ($this->notes) {
				$mail->line("**Notes:** {$this->notes}");
			}

			return $mail
				->action('View Transfer', route('transfers.show', $this->transfer))
				->line('The items have been successfully transferred between locations.');
		}

		/**
		 * Get the array representation for database storage.
		 */
		public function toArray(object $notifiable): array {
			return [
				'transfer_id'       => $this->transfer->id,
				'reference_number'  => $this->transfer->reference_number,
				'completed_by_id'   => $this->completedBy?->id,
				'completed_by_name' => $this->completedBy?->name,
				'completed_at'      => Carbon::now()->toIso8601String(),
				'notes'             => $this->notes,
				'message'           => "Transfer {$this->transfer->reference_number} has been completed",
				'action_url'        => route('transfers.show', $this->transfer),
				'type'              => 'transfer_completed',
				'metadata'          => [
					'product_id'  => $this->transfer->product_id,
					'quantity'    => $this->transfer->quantity,
					'source'      => $this->transfer->source_warehouse_id,
					'destination' => $this->transfer->target_warehouse_id,
				],
			];
		}

		/**
		 * Get the notification's broadcast representation.
		 */
		public function toBroadcast(object $notifiable): array {
			return [
				'transfer_id'      => $this->transfer->id,
				'reference_number' => $this->transfer->reference_number,
				'message'          => "Transfer {$this->transfer->reference_number} has been completed",
				'timestamp'        => now()->toISOString(),
				'url'              => route('transfers.show', $this->transfer),
			];
		}

		/**
		 * Determine which queues should be used for each notification channel.
		 */
		public function viaQueues(): array {
			return [
				'mail'      => 'mail-queue',
				'database'  => 'database-queue',
				'broadcast' => 'broadcast-queue',
			];
		}

		/**
		 * Additional customization for Slack (optional).
		 */
		public function toSlack(object $notifiable): array {
			// TODO: Implement if you want Slack notifications
			return [
				'text'        => "Transfer Completed: ".$this->transfer->reference_number,
				'attachments' => [
					[
						'title'  => 'Transfer Details',
						'fields' => [
							[
								'title' => 'Product',
								'value' => '[Product Name]',
								'short' => true
							],
							[
								'title' => 'Quantity',
								'value' => $this->transfer->quantity,
								'short' => true
							],
							[
								'title' => 'From',
								'value' => '[Source]',
								'short' => true
							],
							[
								'title' => 'To',
								'value' => '[Destination]',
								'short' => true
							],
						],
					],
				],
			];
		}

		/**
		 * Determine the time to wait before retrying if notification fails.
		 */
		public function retryUntil(): DateTime {
			return now()->addHours(12);
		}
	}
