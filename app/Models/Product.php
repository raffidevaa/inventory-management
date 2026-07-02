<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'stock',
        'stock_available',
        'location',
        'condition',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'stock_available' => 'integer',
        ];
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function borrowingDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BorrowingDetail::class);
    }

    public function isAvailable(): bool
    {
        return $this->stock_available > 0 && $this->condition !== 'heavily_damaged';
    }
}
