<?php

namespace Tests\Feature;

use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowingTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function makeUser(string $role): User
    {
        return User::factory()->withRole($role)->create();
    }

    private function makeProduct(array $attrs = []): Product
    {
        $category = Category::factory()->state(['name' => fake()->unique()->words(2, true)])->create();

        return Product::factory()->create(array_merge([
            'category_id'     => $category->id,
            'stock'           => 10,
            'stock_available' => 10,
            'condition'       => 'good',
        ], $attrs));
    }

    private function makeBorrowing(User $user, Product $product, int $qty = 1, array $attrs = []): Borrowing
    {
        $borrowing = Borrowing::create(array_merge([
            'borrower_name' => 'Test Borrower',
            'user_id'       => $user->id,
            'borrow_date'   => now()->toDateString(),
            'due_date'      => now()->addWeek()->toDateString(),
            'status'        => 'borrowed',
        ], $attrs));

        $borrowing->borrowingDetails()->create([
            'product_id'       => $product->id,
            'quantity'         => $qty,
            'condition_before' => $product->condition,
            'item_status'      => 'borrowed',
        ]);

        $product->decrement('stock_available', $qty);

        return $borrowing;
    }

    private function borrowPayload(Product $product, array $overrides = []): array
    {
        return array_merge([
            'borrower_name' => 'Budi Santoso',
            'borrow_date'   => now()->toDateString(),
            'due_date'      => now()->addWeek()->toDateString(),
            'items'         => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ], $overrides);
    }

    // ─── Index ────────────────────────────────────────────────────────────────

    public function test_all_authenticated_users_can_view_borrowing_list(): void
    {
        foreach (['admin', 'staff', 'manager'] as $role) {
            $response = $this->actingAs($this->makeUser($role))->get(route('borrowings.index'));
            $response->assertStatus(200);
        }
    }

    public function test_guest_cannot_view_borrowing_list(): void
    {
        $this->get(route('borrowings.index'))->assertRedirect(route('login'));
    }

    // ─── Create (store) ───────────────────────────────────────────────────────

    public function test_staff_can_create_borrowing(): void
    {
        $staff   = $this->makeUser('staff');
        $product = $this->makeProduct();

        $response = $this->actingAs($staff)->post(route('borrowings.store'), $this->borrowPayload($product));

        $response->assertRedirect();
        $this->assertDatabaseHas('borrowings', ['borrower_name' => 'Budi Santoso', 'status' => 'borrowed']);
        $this->assertDatabaseHas('borrowing_details', ['product_id' => $product->id, 'quantity' => 2]);
        $this->assertEquals(8, $product->fresh()->stock_available);
    }

    public function test_admin_can_create_borrowing(): void
    {
        $admin   = $this->makeUser('admin');
        $product = $this->makeProduct();

        $response = $this->actingAs($admin)->post(route('borrowings.store'), $this->borrowPayload($product));

        $response->assertRedirect();
        $this->assertDatabaseHas('borrowings', ['borrower_name' => 'Budi Santoso']);
    }

    public function test_manager_cannot_create_borrowing(): void
    {
        $manager = $this->makeUser('manager');
        $product = $this->makeProduct();

        $response = $this->actingAs($manager)->post(route('borrowings.store'), $this->borrowPayload($product));

        $response->assertStatus(403);
        $this->assertDatabaseMissing('borrowings', ['borrower_name' => 'Budi Santoso']);
    }

    // ─── Business rules ───────────────────────────────────────────────────────

    public function test_cannot_borrow_heavily_damaged_product(): void
    {
        $staff   = $this->makeUser('staff');
        $product = $this->makeProduct(['condition' => 'heavily_damaged']);

        $response = $this->actingAs($staff)->post(route('borrowings.store'), $this->borrowPayload($product));

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('borrowings', ['borrower_name' => 'Budi Santoso']);
    }

    public function test_cannot_borrow_with_insufficient_stock(): void
    {
        $staff   = $this->makeUser('staff');
        $product = $this->makeProduct(['stock' => 5, 'stock_available' => 1]);

        $response = $this->actingAs($staff)->post(route('borrowings.store'), $this->borrowPayload($product, [
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
        ]));

        $response->assertSessionHasErrors();
        $this->assertEquals(1, $product->fresh()->stock_available);
    }

    public function test_cannot_borrow_with_missing_required_fields(): void
    {
        $staff = $this->makeUser('staff');

        $response = $this->actingAs($staff)->post(route('borrowings.store'), []);

        $response->assertSessionHasErrors(['borrower_name', 'borrow_date', 'due_date', 'items']);
    }

    public function test_cannot_borrow_when_due_date_before_borrow_date(): void
    {
        $staff   = $this->makeUser('staff');
        $product = $this->makeProduct();

        $response = $this->actingAs($staff)->post(route('borrowings.store'), $this->borrowPayload($product, [
            'borrow_date' => now()->toDateString(),
            'due_date'    => now()->subDay()->toDateString(),
        ]));

        $response->assertSessionHasErrors('due_date');
    }

    // ─── Return (processReturn) ───────────────────────────────────────────────

    public function test_staff_can_process_return(): void
    {
        $staff     = $this->makeUser('staff');
        $product   = $this->makeProduct();
        $borrowing = $this->makeBorrowing($staff, $product, 3);
        $detail    = $borrowing->borrowingDetails()->first();

        $response = $this->actingAs($staff)->patch(route('borrowings.return', $borrowing), [
            'items' => [
                ['borrowing_detail_id' => $detail->id, 'condition_after' => 'good'],
            ],
        ]);

        $response->assertRedirect(route('borrowings.show', $borrowing));
        $this->assertDatabaseHas('borrowing_details', ['id' => $detail->id, 'item_status' => 'returned', 'condition_after' => 'good']);
        $this->assertEquals(10, $product->fresh()->stock_available);
    }

    public function test_full_return_sets_borrowing_status_to_returned(): void
    {
        $staff     = $this->makeUser('staff');
        $product   = $this->makeProduct();
        $borrowing = $this->makeBorrowing($staff, $product, 2);
        $detail    = $borrowing->borrowingDetails()->first();

        $this->actingAs($staff)->patch(route('borrowings.return', $borrowing), [
            'items' => [['borrowing_detail_id' => $detail->id, 'condition_after' => 'good']],
        ]);

        $fresh = $borrowing->fresh();
        $this->assertEquals('returned', $fresh->status);
        $this->assertEquals(now()->toDateString(), $fresh->return_date->toDateString());
    }

    public function test_partial_return_keeps_borrowing_status_borrowed(): void
    {
        $staff      = $this->makeUser('staff');
        $product1   = $this->makeProduct();
        $product2   = $this->makeProduct(['stock' => 5, 'stock_available' => 5]);
        $borrowing  = $this->makeBorrowing($staff, $product1, 1);

        // Add a second item manually
        $detail2 = $borrowing->borrowingDetails()->create([
            'product_id'       => $product2->id,
            'quantity'         => 1,
            'condition_before' => 'good',
            'item_status'      => 'borrowed',
        ]);
        $product2->decrement('stock_available', 1);

        $detail1 = $borrowing->borrowingDetails()->where('product_id', $product1->id)->first();

        // Return only the first item
        $this->actingAs($staff)->patch(route('borrowings.return', $borrowing), [
            'items' => [['borrowing_detail_id' => $detail1->id, 'condition_after' => 'good']],
        ]);

        $this->assertDatabaseHas('borrowings', ['id' => $borrowing->id, 'status' => 'borrowed']);
        $this->assertDatabaseHas('borrowing_details', ['id' => $detail2->id, 'item_status' => 'borrowed']);
    }

    public function test_manager_cannot_process_return(): void
    {
        $staff     = $this->makeUser('staff');
        $manager   = $this->makeUser('manager');
        $product   = $this->makeProduct();
        $borrowing = $this->makeBorrowing($staff, $product, 1);
        $detail    = $borrowing->borrowingDetails()->first();

        $response = $this->actingAs($manager)->patch(route('borrowings.return', $borrowing), [
            'items' => [['borrowing_detail_id' => $detail->id, 'condition_after' => 'good']],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('borrowing_details', ['id' => $detail->id, 'item_status' => 'borrowed']);
    }

    public function test_cannot_return_already_returned_borrowing(): void
    {
        $staff     = $this->makeUser('staff');
        $product   = $this->makeProduct();
        $borrowing = $this->makeBorrowing($staff, $product, 1, ['status' => 'returned']);
        $detail    = $borrowing->borrowingDetails()->first();

        $response = $this->actingAs($staff)->patch(route('borrowings.return', $borrowing), [
            'items' => [['borrowing_detail_id' => $detail->id, 'condition_after' => 'good']],
        ]);

        $response->assertStatus(403);
    }

    // ─── Show ────────────────────────────────────────────────────────────────

    public function test_all_users_can_view_borrowing_detail(): void
    {
        $staff     = $this->makeUser('staff');
        $product   = $this->makeProduct();
        $borrowing = $this->makeBorrowing($staff, $product);

        foreach (['admin', 'staff', 'manager'] as $role) {
            $user     = $this->makeUser($role);
            $response = $this->actingAs($user)->get(route('borrowings.show', $borrowing));
            $response->assertStatus(200);
            $response->assertSee('Test Borrower');
        }
    }
}
