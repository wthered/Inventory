<?php

	namespace App\Notifications\Transfers;

	use App\Models\Transfer;
	use App\Models\User;
	use Illuminate\Bus\Queueable;
	use Illuminate\Contracts\Queue\ShouldQueue;
	use Illuminate\Notifications\Messages\MailMessage;
	use Illuminate\Notifications\Notification;
	use Illuminate\Support\Facades\Auth;

	class TransferCancelledNotification extends Notification implements ShouldQueue {
		use Queueable;

		public Transfer $transfer;
		public ?User    $cancelledBy;
		public ?string  $reason;

		public function __construct(Transfer $transfer, ?User $cancelledBy, ?string $reason = null) {
			$this->transfer    = $transfer;
			$this->cancelledBy = $cancelledBy;
			$this->reason      = $reason;
		}

		public function via(object $notifiable): array {
			return [
				'mail',
				'database'
			];
		}

		public function toMail(object $notifiable): MailMessage {
			return (new MailMessage())
				->subject("Transfer Cancelled: {$this->transfer->reference_number}")
				->greeting("Hello {$notifiable->name},")
				->line("A transfer has been cancelled.")
				->line("**Transfer Reference:** {$this->transfer->reference_number}")
				->when($this->cancelledBy, fn($msg) => $msg->line("**Cancelled By:** {$this->cancelledBy->name}"))
				->when($this->reason, fn($msg) => $msg->line("**Reason:** {$this->reason}"))
				->action('View Transfer', route('transfers.show', $this->transfer))
				->line('Thank you for using our application!');
		}

		public function toArray(object $notifiable): array {
			return [
				'transfer_id'       => $this->transfer->id,
				'reference_number'  => $this->transfer->reference_number,
				'cancelled_by_id'   => $this->cancelledBy?->id,
				'cancelled_by_name' => $this->cancelledBy?->name,
				'reason'            => $this->reason,
				'cancelled_at'      => now()->toISOString(),
				'message'           => "Transfer {$this->transfer->reference_number} has been cancelled",
				'action_url'        => route('transfers.show', $this->transfer),
				'type'              => 'transfer_cancelled',
			];
		}
	}
