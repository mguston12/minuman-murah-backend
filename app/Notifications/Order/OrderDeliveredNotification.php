<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderDeliveredNotification extends Notification implements ShouldQueue
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
            ->subject("Pesanan Telah Sampai - {$order->order_number}")
            ->greeting("Halo {$order->shipping_first_name},")
            ->line('Pesananmu telah sampai di alamat tujuan.')
            ->line("Nomor Pesanan: {$order->order_number}")
            ->line('Jika barang sudah kamu terima dengan baik, silakan konfirmasi penerimaan di akunmu.')
            ->action('Konfirmasi Diterima', frontend_url("/account/orders/{$order->uuid}"))
            ->line('Jika dalam beberapa hari tidak ada konfirmasi, pesanan akan otomatis diselesaikan sistem.');
    }
}