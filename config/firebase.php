<?php

declare(strict_types=1);

return [
    /*
     * ------------------------------------------------------------------------
     * Default Firebase project
     * ------------------------------------------------------------------------
     */

    'default' => env('FIREBASE_PROJECT', 'app'),

    /*
     * ------------------------------------------------------------------------
     * Firebase project configurations
     * ------------------------------------------------------------------------
     */

    'projects' => [
        'app' => [

            /*
             * ------------------------------------------------------------------------
             * Credentials / Service Account
             * ------------------------------------------------------------------------
             *
             * In order to access a Firebase project and its related services using a
             * server SDK, requests must be authenticated. For server-to-server
             * communication this is done with a Service Account.
             *
             * If you don't already have generated a Service Account, you can do so by
             * following the instructions from the official documentation pages at
             *
             * https://firebase.google.com/docs/admin/setup#initialize_the_sdk
             *
             * Once you have downloaded the Service Account JSON file, you can use it
             * to configure the package.
             *
             * If you don't provide credentials, the Firebase Admin SDK will try to
             * auto-discover them
             *
             * - by checking the environment variable FIREBASE_CREDENTIALS
             * - by checking the environment variable GOOGLE_APPLICATION_CREDENTIALS
             * - by trying to find Google's well known file
             * - by checking if the application is running on GCE/GCP
             *
             * If no credentials file can be found, an exception will be thrown the
             * first time you try to access a component of the Firebase Admin SDK.
             *
             */

            'credentials' =>
            [
                "type" => "service_account",
                "project_id" => "sntc-73467",
                "private_key_id" => "ddd568bb4b082b97f9538d1c662e702a4dbd6864",
                "private_key" => "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDW7oZz5bWe2MIm\nzWnmrBJuX7u670qyDgoAYHgSc5fLrd0QkbTlbcktE+oI6LwfEAZ+NGU6pJxt50ua\nAf7/D8XYUjb33ET0xO+dcOIS6tvSG86P72PGyJGfwRcq9+T1fKCTNWa70anS+23D\n8TTkvMtgvvnjZ0qW79+Mz1VYfGp/kOkYtwwSL003Xe8kHVDxJ1Hlb6ngOuVSwpY3\nmKioxGC8bWyehcVrCiuX/wltvhfd1/6ycg4+xJXDtEk3aAJ99J/B75CkmGh3deWo\nb/s6Zf1qxYKdChUYv4s9Fe28yJ4vU5MlgR+x9Yk8LtFBCEHCBRBBNOAoVySIWEM9\ngb9872ZfAgMBAAECggEACIQFoEoIYPq/792GbQUBwNRTo6gNmBWj+wbXdbexvj+Q\n3NzrtTCAwjH3S3aD1gvmHC6KfOS3wis3XMc9lD71WWWh11r85RoGbCurakohjS4V\nMun1fz3uzAXCVIKqN/Izpkpgv5hpvrn4vahs9ZLn0KA0AMANqFfalTpgM5BeY59U\nyFgJv/0UX5LhZso0uAQSApRGTUjpA5J622FEco7JqRe0KfWHI8hx7L2EFjE1Jcqb\n3a/cfCt3T8Tft+v6TYqv2HE2IPbhA7/r1jM6cf0n8TXnwGEvoGIUkwY/jQ4kz0P9\nKiAbXd8j1Ohz+bK3Vt/tuDFF0eqEgLyQFQ2LEvX4YQKBgQDu+azB5yTx6XBVTg3f\ninbcCOwCSSOnZiUTiiaeSOiPzOKU+g0qM+o2cSo7mQoM3qRbV3ko58Td0HFrC6m0\nZz/IP3lngyPOkVW9Dq+KejRH+Sy5yrmm5b7Jxho9MMMaRvlnaZcO8o3wVafXX96F\nXXjH4hv5rAkexvYYCRuOV58Y4QKBgQDmPlrP5bzNQ82YLoMrnw9NLDqnrAaWzKlo\nk3A0y95FNQMt1+kl5p+I2UNfljCqZSmPmORLpVbH53S3oaB0M+l3mJ3m4fwQ4mm0\nJ16yK9VkNpVz265Qob+bu5jwVQnUBV5XJW2eQP7Cc5N4XdrmBYvmpVaEk/dJpHfc\nKZMkWb4nPwKBgEhoK/kAYQhPM7MVGEz/9i2LIn86y+u/nkJCXJse+h//8zMyZGTC\nIBfox+QQ5aOqnd/zLAnB17thmcvWV9AKbJ/u44kCignfrTvARF3P0yFvlSaiDwhL\nmjgmpvKdYLAIiy7TJmroASutuFIggRqljJ/7mYBXqNfDbvMvZ0MEt3bhAoGADilh\nro3j6gA0ohqiSMmUyFtjFDMZiKb3+I2xBh5QApO7KVOxbHpd763lXfi/74sfXky6\nJgj6aHtNC4pucxdKUefaNnxJ48P5WnJdeboGew58bM7jTuRUA8ErLAUfAKJ/5f5Q\nWr6GrPEs7edf8mv+6eXbh3YObMIn+Su5eC/o6UMCgYEAqXTxj8NYJCcsbXQpyG+J\n/YMr9ilsD1TzwH5jzYMw/c/s0y136ZP2qEgaXzQdKC+Ns8mLu3rzlbDodgj9I9GS\nrRd5g56O2HjJu/j8ZuFZzBuKW+m8MlWvLBnQFpOd4oUtyDhxKjxUUV0YzfKDtR+b\nViJJckXcKUO1sWR4baMVnWc=\n-----END PRIVATE KEY-----\n",
                "client_email" => "firebase-adminsdk-wrqdc@sntc-73467.iam.gserviceaccount.com",
                "client_id" => "110734255261069630910",
                "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
                "token_uri" => "https://oauth2.googleapis.com/token",
                "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
                "client_x509_cert_url" => "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-wrqdc%40sntc-73467.iam.gserviceaccount.com",
                "universe_domain" => "googleapis.com"
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Auth Component
             * ------------------------------------------------------------------------
             */

            'auth' => [
                'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firestore Component
             * ------------------------------------------------------------------------
             */

            'firestore' => [

                /*
                 * If you want to access a Firestore database other than the default database,
                 * enter its name here.
                 *
                 * By default, the Firestore client will connect to the `(default)` database.
                 *
                 * https://firebase.google.com/docs/firestore/manage-databases
                 */

                // 'database' => env('FIREBASE_FIRESTORE_DATABASE'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Realtime Database
             * ------------------------------------------------------------------------
             */

            'database' => [

                /*
                 * In most of the cases the project ID defined in the credentials file
                 * determines the URL of your project's Realtime Database. If the
                 * connection to the Realtime Database fails, you can override
                 * its URL with the value you see at
                 *
                 * https://console.firebase.google.com/u/1/project/_/database
                 *
                 * Please make sure that you use a full URL like, for example,
                 * https://my-project-id.firebaseio.com
                 */

                'url' => env('FIREBASE_DATABASE_URL'),

                /*
                 * As a best practice, a service should have access to only the resources it needs.
                 * To get more fine-grained control over the resources a Firebase app instance can access,
                 * use a unique identifier in your Security Rules to represent your service.
                 *
                 * https://firebase.google.com/docs/database/admin/start#authenticate-with-limited-privileges
                 */

                // 'auth_variable_override' => [
                //     'uid' => 'my-service-worker'
                // ],

            ],

            'dynamic_links' => [

                /*
                 * Dynamic links can be built with any URL prefix registered on
                 *
                 * https://console.firebase.google.com/u/1/project/_/durablelinks/links/
                 *
                 * You can define one of those domains as the default for new Dynamic
                 * Links created within your project.
                 *
                 * The value must be a valid domain, for example,
                 * https://example.page.link
                 */

                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Cloud Storage
             * ------------------------------------------------------------------------
             */

            'storage' => [

                /*
                 * Your project's default storage bucket usually uses the project ID
                 * as its name. If you have multiple storage buckets and want to
                 * use another one as the default for your application, you can
                 * override it here.
                 */

                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),

            ],

            /*
             * ------------------------------------------------------------------------
             * Caching
             * ------------------------------------------------------------------------
             *
             * The Firebase Admin SDK can cache some data returned from the Firebase
             * API, for example Google's public keys used to verify ID tokens.
             *
             */

            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),

            /*
             * ------------------------------------------------------------------------
             * Logging
             * ------------------------------------------------------------------------
             *
             * Enable logging of HTTP interaction for insights and/or debugging.
             *
             * Log channels are defined in config/logging.php
             *
             * Successful HTTP messages are logged with the log level 'info'.
             * Failed HTTP messages are logged with the log level 'notice'.
             *
             * Note: Using the same channel for simple and debug logs will result in
             * two entries per request and response.
             */

            'logging' => [
                'http_log_channel' => env('FIREBASE_HTTP_LOG_CHANNEL'),
                'http_debug_log_channel' => env('FIREBASE_HTTP_DEBUG_LOG_CHANNEL'),
            ],

            /*
             * ------------------------------------------------------------------------
             * HTTP Client Options
             * ------------------------------------------------------------------------
             *
             * Behavior of the HTTP Client performing the API requests
             */

            'http_client_options' => [

                /*
                 * Use a proxy that all API requests should be passed through.
                 * (default: none)
                 */

                'proxy' => env('FIREBASE_HTTP_CLIENT_PROXY'),

                /*
                 * Set the maximum amount of seconds (float) that can pass before
                 * a request is considered timed out
                 *
                 * The default time out can be reviewed at
                 * https://github.com/kreait/firebase-php/blob/6.x/src/Firebase/Http/HttpClientOptions.php
                 */

                'timeout' => env('FIREBASE_HTTP_CLIENT_TIMEOUT'),

                'guzzle_middlewares' => [],
            ],
        ],
    ],
];
