<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Resolves web vs mobile client platform for dual api_token / mobile_api_token login.
 */
class ClientPlatform
{
    public const WEB = 'web';
    public const MOBILE = 'mobile';

    /**
     * @return self::WEB|self::MOBILE
     */
    public static function fromRequest(Request $request): string
    {
        $raw = $request->input('platform')
            ?? $request->input('client')
            ?? $request->input('device_type')
            ?? $request->header('X-Client-Platform')
            ?? $request->header('X-Platform')
            ?? '';

        $normalized = strtolower(trim((string) $raw));
        if ($normalized === '') {
            return self::WEB;
        }

        if (in_array($normalized, ['mobile', 'app', 'android', 'ios', 'iphone', 'ipad'], true)) {
            return self::MOBILE;
        }

        if (in_array($normalized, ['web', 'browser', 'spa', 'portal'], true)) {
            return self::WEB;
        }

        return self::WEB;
    }

    public static function tokenColumn(string $platform): string
    {
        return $platform === self::MOBILE ? 'mobile_api_token' : 'api_token';
    }
}
