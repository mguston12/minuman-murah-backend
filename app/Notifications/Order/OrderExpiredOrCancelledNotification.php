<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderExpiredOrCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param Order $order
     * @param string $reason 'expired' | 'cancelled'
     */
    public function __construct(protected Order $order, protected string $reason = 'cancelled')
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $order = $this->order;

        $subject = $this->reason === 'expired'
            ? "Pembayaran Kedaluwarsa - {$order->order_number}"
            : "Pesanan Dibatalkan - {$order->order_number}";

        $message = $this->reason === 'expired'
            ? 'Waktu pembayaran untuk pesananmu telah habis, sehingga pesanan otomatis dibatalkan.'
            : 'Pesananmu telah dibatalkan.';

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Halo {$order->shipping_first_name},")
            ->line($message)
            ->line("Nomor Pesanan: {$order->order_number}")
            ->line('Jika kamu masih ingin membeli produk ini, silakan buat pesanan baru.')
            ->action('Belanja Lagi', frontend_url('/products'))
            ->line('Kalau ini bukan tindakanmu atau kamu punya pertanyaan, silakan hubungi tim support kami.');
    }
}