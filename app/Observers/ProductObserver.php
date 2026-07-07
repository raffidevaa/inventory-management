<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\DB;

class ProductObserver
{
    public function updated(Product $product): void
    {
        if (! $product->wasChanged('stock_available')) {
            return;
        }

        if ($product->stock <= 0) {
            return;
        }

        $ratio = $product->stock_available / $product->stock;

        if ($ratio > config('inventory.low_stock_threshold')) {
            return;
        }

        $recipients = User::whereHas('role', fn ($q) => $q->whereIn('name', ['admin', 'staff']))->get();

        foreach ($recipients as $user) {
            $alreadyNotified = $this->hasUnreadNotificationForProduct($user, $product->id);

            if (! $alreadyNotified) {
                $user->notify(new LowStockNotification($product));
            }
        }
    }

    private function hasUnreadNotificationForProduct(User $user, int $productId): bool
    {
        $query = $user->unreadNotifications();

        if (DB::connection()->getDriverName() === 'pgsql') {
            return $query->whereRaw("(data::jsonb)->>'product_id' = ?", [(string) $productId])->exists();
        }

        return $query->where('data->product_id', (string) $productId)->exists();
    }
}
