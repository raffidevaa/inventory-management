<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Product $product) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $percentage = round(($this->product->stock_available / $this->product->stock) * 100);

        return (new MailMessage)
            ->subject("Peringatan: Stok Menipis — {$this->product->name}")
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line("Stok produk **{$this->product->name}** sudah menipis.")
            ->line("Sisa stok tersedia: **{$this->product->stock_available}** dari **{$this->product->stock}** ({$percentage}%).")
            ->action('Lihat Produk', route('products.show', $this->product))
            ->line('Segera lakukan pengadaan untuk menghindari kehabisan stok.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => (string) $this->product->id,
            'product_name' => $this->product->name,
            'product_code' => $this->product->code,
            'stock_available' => $this->product->stock_available,
            'stock' => $this->product->stock,
            'ratio' => round($this->product->stock_available / $this->product->stock, 4),
        ];
    }
}
