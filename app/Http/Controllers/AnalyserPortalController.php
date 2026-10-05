<?php

namespace App\Http\Controllers;

use App\AnalyserAccount;
use App\Support\ClientPlatform;
use App\Support\QueuedMail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnalyserPortalController extends Controller
{
    /**
     * Request an email OTP for an analyser account (no password login).
     */
    public function requestOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = $this->findAnalyserUser($request->email);

        if ($user && $this->accountFor($user)->isActive()) {
            $otp = random_int(100000, 999999);
            $user->update(['otp' => $otp]);

            QueuedMail::send(
                'mail.analyserOtp',
                [
                    'userName' => $user->name,
                    'otp' => $otp,
                    'loginUrl' => config('analyser.login_url', 'abc'),
                ],
                $user->email,
                'Your SNTC Analyser Login OTP',
                'info@sntcgroup.com',
                'SNTC Team - India',
                $user->name
            );
        }

        // Generic response to avoid email enumeration.
        return response()->json([
            'status' => true,
            'message' => 'If this email is registered, an OTP has been sent.',
        ], 200);
    }

    /**
     * Verify email + OTP and issue a portal API token.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $user = $this->findAnalyserUser($request->email);

        if (! $user || (int) $user->otp !== (int) $request->otp) {
            return response()->json(['status' => false, 'message' => 'Invalid email or OTP.'], 401);
        }

        if ($blockedMessage = $user->authAccessBlockedMessage()) {
            return response()->json(['status' => false, 'message' => $blockedMessage], 403);
        }

        $account = $this->accountFor($user);
        if (! $account->isActive()) {
            return response()->json(['status' => false, 'message' => 'Your analyser access period is not active.'], 403);
        }

        $token = $this->generateWebToken($user->id);
        $user->update(['otp' => null]);

        auth('web')->login($user);
        $request->session()->save();

        return response()->json([
            'status' => true,
            'message' => 'Logged in successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'api_token' => $token,
                'access' => [
                    'start_date' => $account->start_date ? $account->start_date->format('Y-m-d') : null,
                    'end_date' => $account->end_date ? $account->end_date->format('Y-m-d') : null,
                    'has_historical_access' => (bool) $account->has_historical_access,
                    'has_today_access' => (bool) $account->has_today_access,
                    'download_limit' => $account->download_limit,
                    'downloads_used' => (int) $account->downloads_used,
                    'downloads_remaining' => $account->downloadsRemaining(),
                ],
            ],
        ], 200);
    }

    private function findAnalyserUser(string $email): ?User
    {
        $roleId = AnalyserAccount::analyserRoleId();
        if (! $roleId) {
            return null;
        }

        return User::where(['email' => $email, 'role' => $roleId])
            ->where('status', 1)
            ->first();
    }

    private function accountFor(User $user): AnalyserAccount
    {
        return AnalyserAccount::where('user_id', $user->id)->firstOrNew(['user_id' => $user->id]);
    }

    private function generateWebToken(int $userId): string
    {
        $column = ClientPlatform::tokenColumn(ClientPlatform::WEB);

        do {
            $token = hash('sha256', Str::random(80) . microtime(true) . $userId);
        } while (
            User::where('api_token', $token)->exists()
            || User::where('mobile_api_token', $token)->exists()
        );

        User::where('id', $userId)->update([$column => $token]);

        return $token;
    }
}
