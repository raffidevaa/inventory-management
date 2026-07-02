<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private function makeUserWithRole(string $roleName): User
    {
        $user = new User();
        $user->setRelation('role', new Role(['name' => $roleName]));

        return $user;
    }

    private function makeUserWithoutRole(): User
    {
        $user = new User();
        $user->setRelation('role', null);

        return $user;
    }

    // ─── hasRole ──────────────────────────────────────────────────────────────

    public function test_has_role_returns_true_for_matching_role(): void
    {
        $user = $this->makeUserWithRole('admin');

        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_has_role_returns_false_for_wrong_role(): void
    {
        $user = $this->makeUserWithRole('staff');

        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_has_role_returns_false_when_no_role(): void
    {
        $user = $this->makeUserWithoutRole();

        $this->assertFalse($user->hasRole('admin'));
    }

    // ─── hasAnyRole ───────────────────────────────────────────────────────────

    public function test_has_any_role_returns_true_when_one_matches(): void
    {
        $user = $this->makeUserWithRole('staff');

        $this->assertTrue($user->hasAnyRole(['admin', 'staff']));
    }

    public function test_has_any_role_returns_false_when_none_match(): void
    {
        $user = $this->makeUserWithRole('manager');

        $this->assertFalse($user->hasAnyRole(['admin', 'staff']));
    }

    public function test_has_any_role_returns_false_when_no_role(): void
    {
        $user = $this->makeUserWithoutRole();

        $this->assertFalse($user->hasAnyRole(['admin', 'staff', 'manager']));
    }
}
