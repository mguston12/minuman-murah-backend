<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderPaidNotification extends Notification implements ShouldQueue
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
            ->subject("Pembayaran Diterima - {$order->order_number}")
            ->greeting("Halo {$order->shipping_first_name},")
            ->line('Pembayaran untuk pesananmu sudah kami terima dan akan segera dikemas.')
            ->line("Nomor Pesanan: {$order->order_number}")
            ->line('Total Dibayar: Rp ' . number_format($order->total_amount, 0, ',', '.'))
            ->action('Lihat Pesanan', config('app.frontend_url') . ("/account/orders/{$order->uuid}"))
            ->line('Terima kasih telah berbelanja di Minuman Murah!');
    }
}