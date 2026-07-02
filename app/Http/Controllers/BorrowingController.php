<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Borrowing::class);

        $borrowings = Borrowing::with(['user', 'borrowingDetails.product'])
            ->when(request('status'), fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('borrowings.index', compact('borrowings'));
    }

    public function create()
    {
        $this->authorize('create', Borrowing::class);

        return view('borrowings.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Borrowing::class);

        abort(501, 'Not yet implemented.');
    }

    public function show(Borrowing $borrowing)
    {
        $this->authorize('view', $borrowing);

        $borrowing->load(['user', 'borrowingDetails.product']);

        return view('borrowings.show', compact('borrowing'));
    }

    public function edit(Borrowing $borrowing)
    {
        $this->authorize('update', $borrowing);

        return view('borrowings.edit', compact('borrowing'));
    }

    public function update(Request $request, Borrowing $borrowing)
    {
        $this->authorize('update', $borrowing);

        abort(501, 'Not yet implemented.');
    }

    public function destroy(Borrowing $borrowing)
    {
        $this->authorize('delete', $borrowing);

        abort(501, 'Not yet implemented.');
    }

    public function processReturn(Borrowing $borrowing)
    {
        $this->authorize('processReturn', $borrowing);

        abort(501, 'Not yet implemented.');
    }
}
