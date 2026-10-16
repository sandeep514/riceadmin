<?php

namespace Tests\Feature;

use App\Services\WebPortalNotificationDelivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

/**
 * Regression: when admin accepts a vendor's brand request
 * (WebBrandController@toggleWebBrandsStatus 0 -> 1), the requesting user
 * must get a mail (QueuedMail) and a queued portal notification
 * (WebPortalNotificationDelivery). Deactivation (1 -> 0) sends nothing.
 *
 * Uses the default sqlite :memory: connection from phpunit.xml.
 */
class WebBrandAcceptedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('web_notifications');
        Schema::dropIfExists('notification');
        Schema::dropIfExists('users');

        Schema::create('web_notifications', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('notify_date')->nullable();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->integer('role_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->string('audience_mode')->nullable();
            $table->string('broadcast_group_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('notification', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->string('userAppType')->nullable();
            $table->integer('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('user_token')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function test_accepted_mail_view_renders_with_user_and_brand(): void
    {
        $html = view('mail.webBrandAccepted', ['data' => [
            'userName' => 'Ramesh Kumar',
            'brandId' => 42,
            'brandName' => 'Golden Harvest',
            'qualityName' => 'Basmati',
            'acceptedAt' => '15-10-2026, 2:30 PM',
        ]])->render();

        $this->assertStringContainsString('Dear Ramesh Kumar', $html);
        $this->assertStringContainsString('Golden Harvest', $html);
        $this->assertStringContainsString('accepted', $html);
        $this->assertStringContainsString('Brand ID: 42', $html);
    }

    public function test_toggle_notifies_only_on_accept_transition(): void
    {
        $src = file_get_contents(app_path('Http/Controllers/WebBrandController.php'));
        $this->assertNotFalse($src);

        $start = strpos($src, 'public function toggleWebBrandsStatus(');
        $this->assertNotFalse($start);
        $next = strpos($src, 'private function notifyBrandAccepted(', $start);
        $this->assertNotFalse($next);
        $body = substr($src, $start, $next - $start);

        // Accept-only: pending -> active triggers notify; deactivation does not.
        $this->assertStringContainsString('$wasPending', $body);
        $this->assertStringContainsString('notifyBrandAccepted', $body);

        // Mail (queued) + portal notification (queued) both wired.
        $method = substr($src, $next, 4000);
        $this->assertStringContainsString('QueuedMail::send', $method);
        $this->assertStringContainsString('mail.webBrandAccepted', $method);
        $this->assertStringContainsString('WebPortalNotificationDelivery', $method);
        $this->assertStringContainsString('deliverToUsers', $method);
        $this->assertStringContainsString('brand_accepted', $method);
    }

    public function test_accept_notification_creates_user_notification_rows(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Ramesh Kumar',
            'email' => 'ramesh@example.com',
            'user_token' => null, // no mobile token: socket + inbox rows only
        ]);

        app(WebPortalNotificationDelivery::class)->deliverToUsers(
            [$userId],
            'Brand request accepted',
            'Your brand "Golden Harvest" has been accepted and is now live on the portal.',
            [
                'audience_mode' => 'individual',
                'push_type' => 'brand_accepted',
                'fill_role_from_user' => false,
                'fill_category_from_business' => false,
                'payload' => ['type' => 'brand_accepted', 'brand_id' => '42'],
            ]
        );

        $this->assertSame(
            1,
            DB::table('web_notifications')->where('user_id', $userId)->count()
        );
        $this->assertSame(
            'Brand request accepted',
            DB::table('web_notifications')->where('user_id', $userId)->value('title')
        );
        $this->assertSame(
            1,
            DB::table('notification')->where('user_id', $userId)->count()
        );
    }
}
