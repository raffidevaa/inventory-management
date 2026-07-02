<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowingDetail extends Model
{
    protected $fillable = [
        'borrowing_id',
        'product_id',
        'quantity',
        'condition_before',
        'condition_after',
        'item_status',
    ];

    public function borrowing(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Borrowing::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
