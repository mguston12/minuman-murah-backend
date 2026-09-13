<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Order $order)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $order = $this->order;

        $mail = (new MailMessage)
            ->subject("Pesanan Diterima - {$order->order_number}")
            ->greeting("Halo {$order->shipping_first_name},")
            ->line('Terima kasih! Pesananmu telah kami terima dan sedang menunggu pembayaran.')
            ->line("Nomor Pesanan: {$order->order_number}");

        if (!$order->relationLoaded('orderItems')) {
            $order->load('orderItems');
        }

        foreach ($order->orderItems as $item) {
            $mail->line("- {$item->product_name} (x{$item->qty}) - Rp " . number_format($item->subtotal, 0, ',', '.'));
        }

        return $mail
            ->line('Total Pembayaran: Rp ' . number_format($order->total_amount, 0, ',', '.'))
            ->action('Selesaikan Pembayaran', config('app.frontend_url') . "/account/orders/{$order->id}")
            ->line('Segera selesaikan pembayaran sebelum batas waktu berakhir.');
    }
}