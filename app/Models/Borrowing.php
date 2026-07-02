<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $fillable = [
        'borrower_name',
        'user_id',
        'borrow_date',
        'due_date',
        'return_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'borrow_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function borrowingDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BorrowingDetail::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'borrowed' && now()->startOfDay()->gt($this->due_date);
    }
}
