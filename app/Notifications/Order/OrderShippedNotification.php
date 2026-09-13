<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderShippedNotification extends Notification implements ShouldQueue
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
            ->subject("Pesanan Dikirim - {$order->order_number}")
            ->greeting("Halo {$order->shipping_first_name},")
            ->line('Kabar baik! Pesananmu sudah dikirim dan sedang dalam perjalanan.')
            ->line("Nomor Pesanan: {$order->order_number}");

        if ($order->courier_agent) {
            $mail->line("Kurir: {$order->courier_agent}" . ($order->courier_agent_service ? " ({$order->courier_agent_service})" : ''));
        }

        // Sesuaikan nama kolom resi dengan yang ada di tabel orders kamu
        if (!empty($order->courier_resi_number)) {
            $mail->line("No. Resi: {$order->courier_resi_number}");
        }

        return $mail
            ->action('Lacak Pesanan', frontend_url("/account/orders/{$order->uuid}"))
            ->line('Terima kasih telah berbelanja di Minuman Murah!');
    }
}