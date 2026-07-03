<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->withRole($role)->create();
    }

    private function makeCategory(array $attrs = []): Category
    {
        return Category::factory()->create($attrs);
    }

    // ─── Guest ────────────────────────────────────────────────────────────────

    public function test_guest_is_redirected_to_login(): void
    {
        $category = $this->makeCategory();

        $this->get(route('categories.index'))->assertRedirect(route('login'));
        $this->get(route('categories.create'))->assertRedirect(route('login'));
        $this->get(route('categories.show', $category))->assertRedirect(route('login'));
        $this->get(route('categories.edit', $category))->assertRedirect(route('login'));
    }

    // ─── Admin ────────────────────────────────────────────────────────────────

    public function test_admin_can_view_category_index(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get(route('categories.index'))->assertStatus(200);
    }

    public function test_admin_can_create_category(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)->post(route('categories.store'), [
            'name' => 'Elektronik',
            'description' => 'Perangkat elektronik',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Elektronik']);
    }

    public function test_admin_can_view_category_detail(): void
    {
        $admin = $this->makeUser('admin');
        $category = $this->makeCategory(['name' => 'Furniture']);

        $this->actingAs($admin)->get(route('categories.show', $category))->assertStatus(200)->assertSee('Furniture');
    }

    public function test_admin_can_update_category(): void
    {
        $admin = $this->makeUser('admin');
        $category = $this->makeCategory(['name' => 'Lama']);

        $response = $this->actingAs($admin)->put(route('categories.update', $category), [
            'name' => 'Baru',
            'description' => null,
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Baru']);
    }

    public function test_admin_can_delete_category(): void
    {
        $admin = $this->makeUser('admin');
        $category = $this->makeCategory();

        $response = $this->actingAs($admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    // ─── Staff (forbidden) ────────────────────────────────────────────────────

    public function test_staff_cannot_access_category_index(): void
    {
        $staff = $this->makeUser('staff');

        $this->actingAs($staff)->get(route('categories.index'))->assertStatus(403);
    }

    public function test_staff_cannot_create_category(): void
    {
        $staff = $this->makeUser('staff');

        $this->actingAs($staff)->post(route('categories.store'), ['name' => 'Test'])->assertStatus(403);
    }

    public function test_staff_cannot_update_category(): void
    {
        $staff = $this->makeUser('staff');
        $category = $this->makeCategory();

        $this->actingAs($staff)->put(route('categories.update', $category), ['name' => 'Changed'])->assertStatus(403);
    }

    public function test_staff_cannot_delete_category(): void
    {
        $staff = $this->makeUser('staff');
        $category = $this->makeCategory();

        $this->actingAs($staff)->delete(route('categories.destroy', $category))->assertStatus(403);
    }

    // ─── Manager (forbidden) ──────────────────────────────────────────────────

    public function test_manager_cannot_access_category_index(): void
    {
        $manager = $this->makeUser('manager');

        $this->actingAs($manager)->get(route('categories.index'))->assertStatus(403);
    }

    public function test_manager_cannot_create_category(): void
    {
        $manager = $this->makeUser('manager');

        $this->actingAs($manager)->post(route('categories.store'), ['name' => 'Test'])->assertStatus(403);
    }

    // ─── Validation ──────────────────────────────────────────────────────────

    public function test_category_name_must_be_unique(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeCategory(['name' => 'Duplikat']);

        $response = $this->actingAs($admin)->post(route('categories.store'), ['name' => 'Duplikat']);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_name_is_required(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post(route('categories.store'), ['name' => ''])->assertSessionHasErrors('name');
    }
}
