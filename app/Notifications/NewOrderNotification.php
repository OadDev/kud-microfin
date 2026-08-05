<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(protected Order $order)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing('customer.user', 'items.product');

        $mail = (new MailMessage)
            ->subject("New Order {$this->order->order_no} — BluePeak Fintech")
            ->greeting('New order placed')
            ->line("Order **{$this->order->order_no}** was just placed by {$this->order->customer->user->name}.")
            ->line('Amount: ₹'.number_format((float) $this->order->total_amount, 2))
            ->line('Payment Method: '.($this->order->payment_method === 'emi_financing' ? 'EMI Financing' : strtoupper($this->order->payment_method)));

        foreach ($this->order->items as $item) {
            $mail->line('- '.($item->product->name ?? 'Product')." × {$item->quantity}");
        }

        return $mail->action('View Order', route('admin.orders.show', $this->order))
            ->line('Thank you for using BluePeak Fintech.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->order->loadMissing('customer.user');

        return [
            'order_id' => $this->order->id,
            'order_no' => $this->order->order_no,
            'customer_name' => $this->order->customer->user->name,
            'total_amount' => (float) $this->order->total_amount,
            'payment_method' => $this->order->payment_method,
        ];
    }
}
