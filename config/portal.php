<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portal / mobile API token — allowed user_from values
    |--------------------------------------------------------------------------
    |
    | Native apps (Play Store / App Store) and the web portal may use the same
    | user row. Auth uses two columns:
    | - api_token          → web session (rotated on web login)
    | - mobile_api_token   → mobile session (rotated on mobile login)
    | Send platform=mobile (or X-Client-Platform) on OTP verify / login so the
    | correct column is updated. See docs/DUAL_PLATFORM_LOGIN.md.
    |
    */
    'api_token_user_from' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('PORTAL_API_TOKEN_USER_FROM', 'web,mobile,app,android,ios'))
    ))),

    /*
    | Allow users with NULL or empty user_from to authenticate with api_token.
    | Set to false to require an explicit user_from match (stricter).
    */
    'api_token_allow_null_user_from' => filter_var(
        env('PORTAL_API_TOKEN_ALLOW_NULL_USER_FROM', true),
        FILTER_VALIDATE_BOOLEAN
    ),

];
