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
        if ($normalized !== '') {
            if (in_array($normalized, ['mobile', 'app', 'android', 'ios', 'iphone', 'ipad'], true)) {
                return self::MOBILE;
            }

            if (in_array($normalized, ['web', 'browser', 'spa', 'portal'], true)) {
                return self::WEB;
            }

            return self::WEB;
        }

        // No explicit platform: infer from User-Agent so phone browsers rotate/auth
        // against mobile_api_token (same-platform kick works without client changes).
        return self::isMobileUserAgent((string) $request->userAgent())
            ? self::MOBILE
            : self::WEB;
    }

    public static function isMobileUserAgent(string $userAgent): bool
    {
        $ua = strtolower($userAgent);
        if ($ua === '') {
            return false;
        }

        foreach (['iphone', 'ipod', 'ipad', 'android', 'mobile', 'webos', 'blackberry', 'iemobile', 'opera mini'] as $needle) {
            if (str_contains($ua, $needle)) {
                return true;
            }
        }

        return false;
    }

    public static function tokenColumn(string $platform): string
    {
        return $platform === self::MOBILE ? 'mobile_api_token' : 'api_token';
    }
}
