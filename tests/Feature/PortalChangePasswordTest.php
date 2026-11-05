<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PortalChangePasswordTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('password')->nullable();
            $t->string('mobile')->nullable();
            $t->tinyInteger('userType')->nullable();
            $t->string('user_from')->nullable();
            $t->string('api_token')->nullable();
            $t->string('mobile_api_token')->nullable();
            $t->tinyInteger('status')->default(1);
            $t->tinyInteger('is_active_by_admin')->default(1);
            $t->tinyInteger('is_deactivated')->default(0);
            $t->timestamps();
        });
    }

    public function test_change_password_with_bearer_token(): void
    {
        $user = User::create([
            'name' => 'Portal User',
            'email' => 'portal@example.com',
            'password' => Hash::make('oldpass1'),
            'userType' => 2,
            'user_from' => 'web',
            'api_token' => 'test-token-change-password',
            'status' => 1,
            'is_active_by_admin' => 1,
            'is_deactivated' => 0,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer test-token-change-password')
            ->postJson('/api/portal/change/password', [
                'current_password' => 'oldpass1',
                'password' => 'newpass1',
                'password_confirmation' => 'newpass1',
            ]);

        $response->assertOk()
            ->assertJson([
                'status' => true,
                'message' => 'Password changed successfully.',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpass1', $user->password));
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        User::create([
            'name' => 'Portal User',
            'email' => 'portal2@example.com',
            'password' => Hash::make('oldpass1'),
            'userType' => 2,
            'user_from' => 'web',
            'api_token' => 'test-token-wrong-current',
            'status' => 1,
            'is_active_by_admin' => 1,
            'is_deactivated' => 0,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer test-token-wrong-current')
            ->postJson('/api/portal/change/password', [
                'current_password' => 'wrong',
                'password' => 'newpass1',
                'password_confirmation' => 'newpass1',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Current password is incorrect.',
            ]);
    }
}
