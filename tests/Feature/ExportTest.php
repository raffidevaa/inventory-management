<?php

namespace Tests\Feature;

use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->withRole($role)->create();
    }

    private function makeProduct(array $attrs = []): Product
    {
        $category = Category::factory()->state(['name' => fake()->unique()->words(2, true)])->create();

        return Product::factory()->create(array_merge(['category_id' => $category->id], $attrs));
    }

    private function makeBorrowing(User $user, Product $product, int $qty = 1): Borrowing
    {
        $borrowing = Borrowing::create([
            'borrower_name' => 'Test Borrower',
            'user_id' => $user->id,
            'borrow_date' => now()->subDay(),
            'due_date' => now()->addWeek(),
            'status' => 'borrowed',
        ]);

        BorrowingDetail::create([
            'borrowing_id' => $borrowing->id,
            'product_id' => $product->id,
            'quantity' => $qty,
            'condition_before' => 'good',
            'item_status' => 'borrowed',
        ]);

        return $borrowing;
    }

    // ─── Unauthenticated ──────────────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_exports(): void
    {
        $this->get(route('exports.products.pdf'))->assertRedirect(route('login'));
        $this->get(route('exports.products.excel'))->assertRedirect(route('login'));
        $this->get(route('exports.borrowings.pdf'))->assertRedirect(route('login'));
        $this->get(route('exports.borrowings.excel'))->assertRedirect(route('login'));
    }

    // ─── Products PDF ─────────────────────────────────────────────────────────

    public function test_admin_can_download_products_pdf(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeProduct();

        $response = $this->actingAs($admin)->get(route('exports.products.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inventory-report', $response->headers->get('content-disposition'));
    }

    public function test_staff_can_download_products_pdf(): void
    {
        $staff = $this->makeUser('staff');
        $this->makeProduct();

        $response = $this->actingAs($staff)->get(route('exports.products.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_manager_can_download_products_pdf(): void
    {
        $manager = $this->makeUser('manager');
        $this->makeProduct();

        $response = $this->actingAs($manager)->get(route('exports.products.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_products_pdf_respects_search_filter(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeProduct(['name' => 'Unique Laptop ABC']);
        $this->makeProduct(['name' => 'Projector XYZ']);

        $response = $this->actingAs($admin)->get(route('exports.products.pdf', ['search' => 'Unique Laptop']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_products_pdf_respects_category_filter(): void
    {
        $admin = $this->makeUser('admin');
        $category = Category::factory()->create(['name' => 'Electronics Test']);
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->get(route('exports.products.pdf', ['category' => $category->id]));

        $response->assertStatus(200);
    }

    // ─── Products Excel ───────────────────────────────────────────────────────

    public function test_admin_can_download_products_excel(): void
    {
        Excel::fake();
        $admin = $this->makeUser('admin');
        $this->makeProduct();

        $response = $this->actingAs($admin)->get(route('exports.products.excel'));

        $response->assertStatus(200);
        Excel::assertDownloaded('inventory-report-'.now()->format('Y-m-d').'.xlsx');
    }

    public function test_staff_can_download_products_excel(): void
    {
        Excel::fake();
        $staff = $this->makeUser('staff');

        $response = $this->actingAs($staff)->get(route('exports.products.excel'));

        $response->assertStatus(200);
        Excel::assertDownloaded('inventory-report-'.now()->format('Y-m-d').'.xlsx');
    }

    public function test_manager_can_download_products_excel(): void
    {
        Excel::fake();
        $manager = $this->makeUser('manager');

        $response = $this->actingAs($manager)->get(route('exports.products.excel'));

        $response->assertStatus(200);
        Excel::assertDownloaded('inventory-report-'.now()->format('Y-m-d').'.xlsx');
    }

    // ─── Borrowings PDF ───────────────────────────────────────────────────────

    public function test_admin_can_download_borrowings_pdf(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct();
        $this->makeBorrowing($admin, $product);

        $response = $this->actingAs($admin)->get(route('exports.borrowings.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('borrowings-report', $response->headers->get('content-disposition'));
    }

    public function test_borrowings_pdf_respects_status_filter(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct();
        $this->makeBorrowing($admin, $product);

        $response = $this->actingAs($admin)->get(route('exports.borrowings.pdf', ['status' => 'borrowed']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_manager_can_download_borrowings_pdf(): void
    {
        $manager = $this->makeUser('manager');
        $product = $this->makeProduct();
        $this->makeBorrowing($manager, $product);

        $response = $this->actingAs($manager)->get(route('exports.borrowings.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // ─── Borrowings Excel ─────────────────────────────────────────────────────

    public function test_admin_can_download_borrowings_excel(): void
    {
        Excel::fake();
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct();
        $this->makeBorrowing($admin, $product);

        $response = $this->actingAs($admin)->get(route('exports.borrowings.excel'));

        $response->assertStatus(200);
        Excel::assertDownloaded('borrowings-report-'.now()->format('Y-m-d').'.xlsx');
    }

    public function test_manager_can_download_borrowings_excel(): void
    {
        Excel::fake();
        $manager = $this->makeUser('manager');

        $response = $this->actingAs($manager)->get(route('exports.borrowings.excel'));

        $response->assertStatus(200);
        Excel::assertDownloaded('borrowings-report-'.now()->format('Y-m-d').'.xlsx');
    }

    // ─── Borrowing Slip PDF ───────────────────────────────────────────────────

    public function test_admin_can_download_borrowing_slip(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct();
        $borrowing = $this->makeBorrowing($admin, $product);

        $response = $this->actingAs($admin)->get(route('exports.borrowings.slip', $borrowing));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('borrowing-slip-'.$borrowing->id, $response->headers->get('content-disposition'));
    }

    public function test_staff_can_download_borrowing_slip(): void
    {
        $staff = $this->makeUser('staff');
        $product = $this->makeProduct();
        $borrowing = $this->makeBorrowing($staff, $product);

        $response = $this->actingAs($staff)->get(route('exports.borrowings.slip', $borrowing));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_manager_can_download_borrowing_slip(): void
    {
        $manager = $this->makeUser('manager');
        $product = $this->makeProduct();
        $borrowing = $this->makeBorrowing($manager, $product);

        $response = $this->actingAs($manager)->get(route('exports.borrowings.slip', $borrowing));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
