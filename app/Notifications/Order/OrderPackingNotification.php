<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderPackingNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject("Pesanan Sedang Dikemas - {$order->order_number}")
            ->greeting("Halo {$order->shipping_first_name},")
            ->line('Pesananmu sedang kami kemas dengan hati-hati sebelum dikirim.')
            ->line("Nomor Pesanan: {$order->order_number}")
            ->action('Lihat Pesanan', url("/account/orders/{$order->uuid}"))
            ->line('Kami akan memberi kabar lagi begitu pesanan dikirim.');
    }
}