<?php

	namespace App\Notifications\Transfers;

	use App\Models\Transfer;
	use App\Models\User;
	use Carbon\Carbon;
	use Illuminate\Bus\Queueable;
	use Illuminate\Notifications\Messages\MailMessage;
	use Illuminate\Notifications\Notification;
	use Illuminate\Support\Facades\Auth;

	class TransferApprovedNotification extends Notification {
		use Queueable;

		use Queueable;

		/**
		 * The transfer instance.
		 */
		public Transfer $transfer;

		/**
		 * The user who approved the transfer.
		 */
		public User $approver;

		/**
		 * Approval notes (optional).
		 */
		public ?string $notes;

		/**
		 * Create a new notification instance.
		 */
		public function __construct(Transfer $transfer, User $approver, ?string $notes = null) {
			$this->transfer = $transfer;
			$this->approver = $approver;
			$this->notes    = $notes;
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
			// Options: 'mail', 'database', 'broadcast', 'nexmo', 'slack', etc.
		}

		/**
		 * Get the mail representation of the notification.
		 */
		public function toMail(object $notifiable): MailMessage {
			return (new MailMessage())
				->subject("Transfer Approved: {$this->transfer->reference_number}")
				->greeting("Hello {$notifiable->name},")
				->line("A transfer has been approved.")
				->line("**Transfer Reference:** {$this->transfer->reference_number}")
				->line("**Approved By:** {$this->approver->name}")
				->line("**Product:** [Product Name Here]")
				->line("**Quantity:** {$this->transfer->quantity}")
				->line("**From:** [Source Location Here]")
				->line("**To:** [Destination Location Here]")
				->when($this->notes, function ($message) {
					return $message->line("**Notes:** {$this->notes}");
				})
				->action('View Transfer', route('transfers.show', $this->transfer))
				->line('Thank you for using our application!');
		}

		/**
		 * Get the array representation for database storage.
		 */
		public function toArray(object $notifiable): array {
			return [
				'transfer_id'      => $this->transfer->id,
				'reference_number' => $this->transfer->reference_number,
				'approver_id'      => $this->approver->id,
				'approver_name'    => $this->approver->name,
				'approved_at'      => Carbon::now()->toISOString(),
				'notes'            => $this->notes,
				'message'          => "Transfer ".$this->transfer->reference_number." has been approved by ".$this->approver->name,
				'action_url'       => route('transfers.show', $this->transfer),
				'type'             => 'transfer_approved',
			];
		}

		/**
		 * Get the notification's broadcast representation.
		 */
		public function toBroadcast(object $notifiable): array {
			return [
				'transfer_id'      => $this->transfer->id,
				'reference_number' => $this->transfer->reference_number,
				'approver'         => $this->approver->name,
				'message'          => "Transfer ".$this->transfer->reference_number." has been approved",
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
		 * Determine if the notification should be sent.
		 */
		public function shouldSend(object $notifiable, string $channel): bool {
			// TODO: Add logic to determine if notification should be sent
			// Example: Check user preferences, business rules, etc.
			return true;
		}
	}
