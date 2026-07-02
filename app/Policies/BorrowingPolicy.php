<?php

namespace App\Policies;

use App\Models\Borrowing;
use App\Models\User;

class BorrowingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Borrowing $borrowing): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function update(User $user, Borrowing $borrowing): bool
    {
        return $user->hasAnyRole(['admin', 'staff']);
    }

    public function processReturn(User $user, Borrowing $borrowing): bool
    {
        return $user->hasAnyRole(['admin', 'staff'])
            && $borrowing->status !== 'returned';
    }

    public function delete(User $user, Borrowing $borrowing): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Borrowing $borrowing): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Borrowing $borrowing): bool
    {
        return $user->hasRole('admin');
    }
}
