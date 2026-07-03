<?php

namespace Tests\Unit;

use App\Models\Borrowing;
use Carbon\Carbon;
use Tests\TestCase;

class BorrowingTest extends TestCase
{
    public function test_overdue_when_status_borrowed_and_past_due_date(): void
    {
        $borrowing = new Borrowing([
            'status'   => 'borrowed',
            'due_date' => Carbon::yesterday(),
        ]);

        $this->assertTrue($borrowing->isOverdue());
    }

    public function test_not_overdue_when_due_date_is_today(): void
    {
        $borrowing = new Borrowing([
            'status'   => 'borrowed',
            'due_date' => Carbon::today(),
        ]);

        $this->assertFalse($borrowing->isOverdue());
    }

    public function test_not_overdue_when_due_date_is_future(): void
    {
        $borrowing = new Borrowing([
            'status'   => 'borrowed',
            'due_date' => Carbon::tomorrow(),
        ]);

        $this->assertFalse($borrowing->isOverdue());
    }

    public function test_not_overdue_when_status_returned_even_if_past_due(): void
    {
        $borrowing = new Borrowing([
            'status'   => 'returned',
            'due_date' => Carbon::yesterday(),
        ]);

        $this->assertFalse($borrowing->isOverdue());
    }

    public function test_not_overdue_when_status_overdue_label_but_logic_checks_borrowed(): void
    {
        // status='overdue' is a DB label set externally; isOverdue() checks status='borrowed'
        $borrowing = new Borrowing([
            'status'   => 'overdue',
            'due_date' => Carbon::yesterday(),
        ]);

        $this->assertFalse($borrowing->isOverdue());
    }
}
