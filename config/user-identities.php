<?php

use Chuoke\UserIdentities\Types\ApiKeyIdentityType;
use Chuoke\UserIdentities\Types\CustomJwtIdentityType;
use Chuoke\UserIdentities\Types\EmailIdentityType;
use Chuoke\UserIdentities\Types\GithubIdentityType;
use Chuoke\UserIdentities\Types\GoogleIdentityType;
use Chuoke\UserIdentities\Types\JwtIdentityType;
use Chuoke\UserIdentities\Types\MobileIdentityType;
use Chuoke\UserIdentities\Types\TwitterIdentityType;
use Chuoke\UserIdentities\Types\UsernameIdentityType;

return [

    'table' => env('USER_IDENTITIES_TABLE', 'user_identities'),

    // Register available identity types for authentication
    // Each type maps to a class that handles specific authentication method
    'types' => [
        // Password-based authentication types
        'email' => EmailIdentityType::class,
        'mobile' => MobileIdentityType::class,
        'username' => UsernameIdentityType::class,

        // OAuth-based authentication types
        'github' => GithubIdentityType::class,
        'google' => GoogleIdentityType::class,
        'twitter' => TwitterIdentityType::class,

        // Token-based authentication types
        'api_key' => ApiKeyIdentityType::class,
        'jwt' => JwtIdentityType::class,
        'custom_jwt' => CustomJwtIdentityType::class,
    ],

    // Require identity to be verified before allowing authentication
    // When true, only verified identities can be used for login
    // When false, both verified and unverified identities can be used for login
    'require_verification' => env('USER_IDENTITIES_REQUIRE_VERIFICATION', true),

    // Identity types that can use password authentication
    // When updating password, only these types will be updated
    'passwordable_types' => [
        'email',
        'mobile',
        'username',
    ],
];
