<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminSessionIsolationTest extends TestCase
{
    public function test_provider_style_session_cannot_open_admin_dashboard(): void
    {
        $response = $this->withSession([
            'provider_id' => 42,
            'admin_id' => 1,
            'role' => 'provider',
            'name' => 'Provider User',
        ])->get('/admin/dashboard');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_role_is_not_treated_as_logged_in_admin_guest_redirect(): void
    {
        $response = $this->withSession([
            'admin_id' => 1,
            'role' => 'provider',
            'name' => 'Provider User',
        ])->get('/admin/login');

        $response->assertOk();
    }
}
