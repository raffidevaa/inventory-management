<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function makeUser(string $role): User
    {
        return User::factory()->withRole($role)->create();
    }

    private function makeCategory(): Category
    {
        return Category::factory()->state(['name' => fake()->unique()->words(2, true)])->create();
    }

    private function makeProduct(array $attrs = []): Product
    {
        $category = $this->makeCategory();
        return Product::factory()->create(array_merge(['category_id' => $category->id], $attrs));
    }

    // ─── Index (list) ─────────────────────────────────────────────────────────

    public function test_admin_can_view_product_list(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
    }

    public function test_manager_can_view_product_list(): void
    {
        $manager = $this->makeUser('manager');

        $response = $this->actingAs($manager)->get(route('products.index'));

        $response->assertStatus(200);
    }

    public function test_staff_can_view_product_list(): void
    {
        $staff = $this->makeUser('staff');

        $response = $this->actingAs($staff)->get(route('products.index'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_product_list(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertRedirect(route('login'));
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function test_staff_can_view_create_form(): void
    {
        $staff = $this->makeUser('staff');

        $response = $this->actingAs($staff)->get(route('products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('products.create');
    }

    public function test_manager_cannot_view_create_form(): void
    {
        $manager = $this->makeUser('manager');

        $response = $this->actingAs($manager)->get(route('products.create'));

        $response->assertStatus(403);
    }

    public function test_staff_can_create_product(): void
    {
        $staff = $this->makeUser('staff');
        $category = $this->makeCategory();

        $response = $this->actingAs($staff)->post(route('products.store'), [
            'code'        => 'ITM-TEST-001',
            'name'        => 'Test Laptop',
            'category_id' => $category->id,
            'stock'       => 5,
            'condition'   => 'good',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', [
            'code'            => 'ITM-TEST-001',
            'name'            => 'Test Laptop',
            'stock'           => 5,
            'stock_available' => 5,
        ]);
    }

    public function test_admin_can_create_product(): void
    {
        $admin = $this->makeUser('admin');
        $category = $this->makeCategory();

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'code'        => 'ITM-ADM-001',
            'name'        => 'Admin Product',
            'category_id' => $category->id,
            'stock'       => 10,
            'condition'   => 'good',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['code' => 'ITM-ADM-001']);
    }

    public function test_manager_cannot_create_product(): void
    {
        $manager = $this->makeUser('manager');
        $category = $this->makeCategory();

        $response = $this->actingAs($manager)->post(route('products.store'), [
            'code'        => 'ITM-MGR-001',
            'name'        => 'Manager Product',
            'category_id' => $category->id,
            'stock'       => 3,
            'condition'   => 'good',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('products', ['code' => 'ITM-MGR-001']);
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    public function test_cannot_create_product_with_duplicate_code(): void
    {
        $staff = $this->makeUser('staff');
        $category = $this->makeCategory();

        Product::factory()->create(['code' => 'DUPE-001', 'category_id' => $category->id]);

        $response = $this->actingAs($staff)->post(route('products.store'), [
            'code'        => 'DUPE-001',
            'name'        => 'Another Product',
            'category_id' => $category->id,
            'stock'       => 1,
            'condition'   => 'good',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_cannot_create_product_with_invalid_condition(): void
    {
        $staff = $this->makeUser('staff');
        $category = $this->makeCategory();

        $response = $this->actingAs($staff)->post(route('products.store'), [
            'code'        => 'ITM-BAD-001',
            'name'        => 'Bad Condition',
            'category_id' => $category->id,
            'stock'       => 1,
            'condition'   => 'broken',
        ]);

        $response->assertSessionHasErrors('condition');
    }

    public function test_cannot_create_product_with_missing_required_fields(): void
    {
        $staff = $this->makeUser('staff');

        $response = $this->actingAs($staff)->post(route('products.store'), []);

        $response->assertSessionHasErrors(['code', 'name', 'category_id', 'stock', 'condition']);
    }

    public function test_cannot_create_product_with_nonexistent_category(): void
    {
        $staff = $this->makeUser('staff');

        $response = $this->actingAs($staff)->post(route('products.store'), [
            'code'        => 'ITM-CAT-001',
            'name'        => 'Orphan Product',
            'category_id' => 9999,
            'stock'       => 1,
            'condition'   => 'good',
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    // ─── Show ────────────────────────────────────────────────────────────────

    public function test_all_authenticated_users_can_view_product_detail(): void
    {
        $product = $this->makeProduct();

        foreach (['admin', 'staff', 'manager'] as $role) {
            $user = $this->makeUser($role);
            $response = $this->actingAs($user)->get(route('products.show', $product));
            $response->assertStatus(200);
            $response->assertSee($product->code);
        }
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function test_staff_can_update_product(): void
    {
        $staff = $this->makeUser('staff');
        $product = $this->makeProduct(['name' => 'Old Name', 'stock' => 5, 'stock_available' => 5]);

        $response = $this->actingAs($staff)->put(route('products.update', $product), [
            'code'        => $product->code,
            'name'        => 'Updated Name',
            'category_id' => $product->category_id,
            'stock'       => 10,
            'condition'   => 'lightly_damaged',
        ]);

        $response->assertRedirect(route('products.show', $product));
        $this->assertDatabaseHas('products', [
            'id'        => $product->id,
            'name'      => 'Updated Name',
            'stock'     => 10,
            'condition' => 'lightly_damaged',
        ]);
    }

    public function test_manager_cannot_update_product(): void
    {
        $manager = $this->makeUser('manager');
        $product = $this->makeProduct(['name' => 'Original']);

        $response = $this->actingAs($manager)->put(route('products.update', $product), [
            'code'        => $product->code,
            'name'        => 'Hacked Name',
            'category_id' => $product->category_id,
            'stock'       => 1,
            'condition'   => 'good',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('products', ['id' => $product->id, 'name' => 'Hacked Name']);
    }

    public function test_update_keeps_same_code_without_unique_error(): void
    {
        $staff = $this->makeUser('staff');
        $product = $this->makeProduct(['code' => 'SAME-001']);

        $response = $this->actingAs($staff)->put(route('products.update', $product), [
            'code'        => 'SAME-001',
            'name'        => 'Same Code Updated',
            'category_id' => $product->category_id,
            'stock'       => $product->stock,
            'condition'   => $product->condition,
        ]);

        $response->assertRedirect(route('products.show', $product));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Same Code Updated']);
    }

    // ─── Delete (soft delete) ─────────────────────────────────────────────────

    public function test_staff_can_soft_delete_product(): void
    {
        $staff = $this->makeUser('staff');
        $product = $this->makeProduct();

        $response = $this->actingAs($staff)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_manager_cannot_delete_product(): void
    {
        $manager = $this->makeUser('manager');
        $product = $this->makeProduct();

        $response = $this->actingAs($manager)->delete(route('products.destroy', $product));

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    }

    public function test_deleted_product_not_visible_in_index(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(['name' => 'Soon Deleted']);

        $product->delete();

        $response = $this->actingAs($admin)->get(route('products.index'));

        $response->assertDontSee('Soon Deleted');
    }

    public function test_deleted_product_show_returns_404(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct();
        $product->delete();

        $response = $this->actingAs($admin)->get(route('products.show', $product));

        $response->assertStatus(404);
    }

    // ─── Business rules ───────────────────────────────────────────────────────

    public function test_heavily_damaged_product_is_not_available(): void
    {
        $product = $this->makeProduct(['condition' => 'heavily_damaged', 'stock' => 5, 'stock_available' => 5]);

        $this->assertFalse($product->isAvailable());
    }

    public function test_product_with_zero_available_stock_is_not_available(): void
    {
        $product = $this->makeProduct(['condition' => 'good', 'stock' => 5, 'stock_available' => 0]);

        $this->assertFalse($product->isAvailable());
    }

    public function test_good_product_with_stock_is_available(): void
    {
        $product = $this->makeProduct(['condition' => 'good', 'stock' => 5, 'stock_available' => 3]);

        $this->assertTrue($product->isAvailable());
    }

    public function test_search_filters_by_name(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeProduct(['name' => 'SRCH_MATCH_ZYXWV_001']);
        $this->makeProduct(['name' => 'SRCH_NOMATCH_ZYXWV_002']);

        $response = $this->actingAs($admin)->get(route('products.index', ['search' => 'SRCH_MATCH']));

        $response->assertViewHas('products', fn($p) => $p->total() === 1);
        $response->assertSee('SRCH_MATCH_ZYXWV_001');
        $response->assertDontSee('SRCH_NOMATCH_ZYXWV_002');
    }
}
