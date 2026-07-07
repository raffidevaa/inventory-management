<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->withRole($role)->create();
    }

    private function makeProduct(array $attrs = []): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create(array_merge(['category_id' => $category->id], $attrs));
    }

    // ─── Observer trigger ─────────────────────────────────────────────────────

    public function test_notification_sent_when_stock_available_drops_below_threshold(): void
    {
        Notification::fake();

        $admin = $this->makeUser('admin');
        $staff = $this->makeUser('staff');

        $product = $this->makeProduct(['stock' => 20, 'stock_available' => 10]);

        // Drop to 20% — below 25% threshold
        $product->update(['stock_available' => 4]);

        Notification::assertSentTo($admin, LowStockNotification::class);
        Notification::assertSentTo($staff, LowStockNotification::class);
    }

    public function test_notification_not_sent_when_stock_available_is_above_threshold(): void
    {
        Notification::fake();

        $this->makeUser('admin');

        $product = $this->makeProduct(['stock' => 20, 'stock_available' => 10]);

        // Update to 50% — still above 25% threshold
        $product->update(['stock_available' => 10]);

        Notification::assertNothingSent();
    }

    public function test_notification_not_sent_when_unrelated_field_changes(): void
    {
        Notification::fake();

        $this->makeUser('admin');

        $product = $this->makeProduct(['stock' => 20, 'stock_available' => 4]);

        // Only update location — not stock_available
        $product->update(['location' => 'Gedung B, Lantai 2']);

        Notification::assertNothingSent();
    }

    public function test_notification_not_sent_when_stock_is_zero(): void
    {
        Notification::fake();

        $this->makeUser('admin');

        $product = $this->makeProduct(['stock' => 0, 'stock_available' => 0]);

        $product->update(['stock_available' => 0]);

        Notification::assertNothingSent();
    }

    public function test_notification_not_duplicated_when_already_unread_for_same_product(): void
    {
        $admin = $this->makeUser('admin');

        $product = $this->makeProduct(['stock' => 20, 'stock_available' => 10]);

        // First trigger
        $product->update(['stock_available' => 4]);
        $this->assertCount(1, $admin->fresh()->unreadNotifications);

        // Second trigger — should NOT create duplicate
        $product->update(['stock_available' => 3]);
        $this->assertCount(1, $admin->fresh()->unreadNotifications);
    }

    public function test_notification_sent_again_after_previous_one_is_read(): void
    {
        $admin = $this->makeUser('admin');

        $product = $this->makeProduct(['stock' => 20, 'stock_available' => 10]);

        // First trigger
        $product->update(['stock_available' => 4]);
        $this->assertCount(1, $admin->fresh()->unreadNotifications);

        // Mark as read
        $admin->unreadNotifications->markAsRead();
        $this->assertCount(0, $admin->fresh()->unreadNotifications);

        // Second trigger — now allowed
        $product->update(['stock_available' => 2]);
        $this->assertCount(1, $admin->fresh()->unreadNotifications);
    }

    public function test_regular_user_does_not_receive_notification(): void
    {
        Notification::fake();

        $regularUser = User::factory()->create(); // no role

        $this->makeUser('admin'); // admin should receive

        $product = $this->makeProduct(['stock' => 20, 'stock_available' => 10]);
        $product->update(['stock_available' => 4]);

        Notification::assertNotSentTo($regularUser, LowStockNotification::class);
    }

    // ─── NotificationController ───────────────────────────────────────────────

    public function test_authenticated_user_can_view_notifications_page(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertViewIs('notifications.index');
    }

    public function test_guest_cannot_view_notifications_page(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(['stock' => 20, 'stock_available' => 10]);
        $product->update(['stock_available' => 4]);

        $notification = $admin->fresh()->unreadNotifications->first();
        $this->assertNotNull($notification);

        $response = $this->actingAs($admin)
            ->post(route('notifications.read', $notification->id));

        $response->assertRedirect();
        $this->assertCount(0, $admin->fresh()->unreadNotifications);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $admin = $this->makeUser('admin');

        $product1 = $this->makeProduct(['stock' => 20, 'stock_available' => 10]);
        $product2 = $this->makeProduct(['stock' => 20, 'stock_available' => 10]);

        $product1->update(['stock_available' => 4]);
        $product2->update(['stock_available' => 3]);

        $this->assertCount(2, $admin->fresh()->unreadNotifications);

        $response = $this->actingAs($admin)
            ->post(route('notifications.read-all'));

        $response->assertRedirect();
        $this->assertCount(0, $admin->fresh()->unreadNotifications);
    }

    // ─── Notification data integrity ──────────────────────────────────────────

    public function test_notification_stores_correct_product_data(): void
    {
        $admin = $this->makeUser('admin');

        $product = $this->makeProduct(['stock' => 20, 'stock_available' => 10]);
        $product->update(['stock_available' => 4]);

        $notification = $admin->fresh()->unreadNotifications->first();

        $this->assertEquals($product->id, $notification->data['product_id']);
        $this->assertEquals($product->name, $notification->data['product_name']);
        $this->assertEquals(4, $notification->data['stock_available']);
        $this->assertEquals(20, $notification->data['stock']);
        $this->assertEquals(0.2, $notification->data['ratio']);
    }
}
