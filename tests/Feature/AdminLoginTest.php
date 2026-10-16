<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    public function test_admin_login_page_is_available_to_guests(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Admin sign in');
    }

    public function test_guests_are_redirected_to_admin_login_from_admin_routes(): void
    {
        $this->get(route('admin.subscription-plans.index'))
            ->assertRedirect(route('admin.login'));
    }
}
