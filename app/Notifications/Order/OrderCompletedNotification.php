<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderCompletedNotification extends Notification implements ShouldQueue
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
            ->subject("Pesanan Selesai - {$order->order_number}")
            ->greeting("Halo {$order->shipping_first_name},")
            ->line('Terima kasih telah mengonfirmasi penerimaan pesananmu.')
            ->line("Nomor Pesanan: {$order->order_number}")
            ->line('Jangan lupa beri ulasan untuk produk yang kamu beli, ulasanmu sangat membantu pembeli lain.')
            ->action('Beri Ulasan', config('app.frontend_url') . ("/account/orders/{$order->order_number}"))
            ->line('Sampai jumpa di pesanan berikutnya!');
    }
}