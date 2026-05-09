# Laravel User Identities

A flexible and extensible authentication system for Laravel that enables multiple identity types per user, supporting password-based, OAuth, and token authentication methods.

## Core Philosophy

**Keep your users table lean** - Move authentication data out of users table and into a dedicated identities table. This approach:

- **Separates Concerns** - User data stays clean, auth data stays organized
- **Improves Performance** - User queries remain fast, auth queries are optimized
- **Enables Flexibility** - Add new auth methods without touching users table
- **Better Analytics** - Clear separation of authentication methods per user
- **Enhanced Security** - Minimize sensitive data exposure in user queries, logs, and debugging

## Features

- **Multiple Authentication Types**: Support email, mobile, username, OAuth (GitHub, Google, Twitter), and token-based (API Key, JWT) authentication
- **Hierarchical Architecture**: Base classes for different authentication categories with built-in common behaviors
- **Separation of Concerns**: Decouple authentication data from user business data
- **Highly Extensible**: Easy to add new identity types without schema changes
- **Independent Verification**: Track verification status for each identity separately
- **Secure by Default**: Automatic password hashing and credential protection
- **Laravel Integration**: Custom Guard and UserProvider for seamless authentication
- **Performance Optimized**: Smart caching to minimize database queries
- 🎯 **Separation of Concerns**: Decouple authentication data from user business data
- 🔄 **Highly Extensible**: Easy to add new identity types without schema changes
- ✅ **Independent Verification**: Track verification status for each identity separately
- 🛡️ **Secure by Default**: Automatic password hashing and credential protection
- 🔌 **Laravel Integration**: Custom Guard and UserProvider for seamless authentication
- ⚡ **Performance Optimized**: Smart caching to minimize database queries

## Installation

```bash
composer require chuoke/laravel-user-identities
```

Publish configuration and migrations:

```bash
php artisan vendor:publish --tag=user-identities-config
php artisan vendor:publish --tag=user-identities-migrations
php artisan migrate
```

## Quick Start

### 1. Configure Authentication Types

```php
// config/user-identities.php
return [
    'types' => [
        // Password-based authentication types
        'email' => \Chuoke\UserIdentities\Types\EmailIdentityType::class,
        'mobile' => \Chuoke\UserIdentities\Types\MobileIdentityType::class,
        'username' => \Chuoke\UserIdentities\Types\UsernameIdentityType::class,

        // OAuth-based authentication types
        'github' => \Chuoke\UserIdentities\Types\GithubIdentityType::class,
        'google' => \Chuoke\UserIdentities\Types\GoogleIdentityType::class,
        'twitter' => \Chuoke\UserIdentities\Types\TwitterIdentityType::class,

        // Token-based authentication types
        'api_key' => \Chuoke\UserIdentities\Types\ApiKeyIdentityType::class,
        'jwt' => \Chuoke\UserIdentities\Types\JwtIdentityType::class,
        'custom_jwt' => \Chuoke\UserIdentities\Types\CustomJwtIdentityType::class,
    ],

    // Require identity to be verified before allowing authentication
    'require_verification' => env('USER_IDENTITIES_REQUIRE_VERIFICATION', false),
];
```

### 2. Add Trait to User Model

```php
use Chuoke\UserIdentities\Concerns\HasIdentities;

class User extends Authenticatable
{
    use HasIdentities;
}
```

### 3. Create Identities

```php
use Chuoke\UserIdentities\Actions\UserIdentityCreate;

$action = new UserIdentityCreate();

// Password-based identities
$action->execute($user, 'email', 'user@example.com', 'password123');
$action->execute($user, 'mobile', '+1234567890', 'password123');
$action->execute($user, 'username', 'johndoe', 'password123');

// OAuth identities (pre-verified)
$action->execute($user, 'github', 'github_id_12345', 'oauth_token_here', true);

// Token-based identities
$action->execute($user, 'api_key', 'my-app', 'secret_api_key_here', true);
```

### 4. Configure Laravel Auth

```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'user-identity',
    ],
],

'providers' => [
    'user-identity' => [
        'driver' => 'user-identity',
        'model' => App\Domain\User\Models\User::class,
    ],
],
```

### 5. Authenticate Users

```php
// Standard authentication
Auth::attempt([
    'type' => 'email',
    'identifier' => 'user@example.com',
    'password' => 'password123'
]);

// OAuth authentication
Auth::attempt([
    'type' => 'github',
    'identifier' => 'github_id_12345',
    'password' => 'oauth_token_here'
]);

// Token authentication
Auth::attempt([
    'type' => 'api_key',
    'identifier' => 'my-app',
    'credentials' => 'secret_api_key_here'
]);
```

## Architecture

### Identity Type Hierarchy

```
IdentityTypeInterface
├── PasswordBasedIdentityType
│   ├── EmailIdentityType (HashCredentialProcessor)
│   ├── MobileIdentityType (HashCredentialProcessor)
│   └── UsernameIdentityType (HashCredentialProcessor)
├── OAuthBasedIdentityType
│   ├── GithubIdentityType (PlainCredentialProcessor)
│   ├── GoogleIdentityType (PlainCredentialProcessor)
│   └── TwitterIdentityType (PlainCredentialProcessor)
└── TokenBasedIdentityType
    ├── ApiKeyIdentityType (EncryptCredentialProcessor)
    ├── JwtIdentityType (PlainCredentialProcessor)
    └── CustomJwtIdentityType (CustomJwtCredentialProcessor)
```

### Database Schema

The package uses a single `user_identities` table, **keeping your users table clean**:

#### Traditional Approach (Problems):

```sql
users table (bloated):
- id, name, email, mobile, github_id, google_id, twitter_id, api_key...
- Many nullable columns for different auth methods
- Complex migrations when adding new auth types
- User queries become slower over time
```

#### Our Approach (Solution):

```sql
users table (lean):
- id, name, created_at, updated_at  // Core user data only

user_identities table (flexible):
- id, type, identifier, credentials, verified_at
- authenticatable_type, authenticatable_id  // Polymorphic relation
- Easy to add new identity types without touching users table
- Optimized queries for authentication
```

**Benefits:**

- 🏗️ **Clean Schema** - Users table contains only user data
- ⚡ **Fast Queries** - User queries unaffected by auth data
- 🔄 **Easy Extension** - Add new auth types without user table changes
- 📊 **Clear Analytics** - Separate tracking of auth methods
- 🔒 **Enhanced Security** - Minimize sensitive data exposure in user queries and logs

### Core Components

- **Models**: `UserIdentity` - Main model for identity records
- **Traits**: `HasIdentities` - Add to authenticatable models for identity management
- **Actions**: `UserIdentityCreate`, `UserIdentityVerify`, `UserIdentityUpdate`, `UserIdentityDelete`
- **Auth**: `IdentityGuard`, `IdentityUserProvider` - Custom authentication with smart caching
- **Types**: Hierarchical identity type classes with built-in behaviors
- **Processors**: `HashCredentialProcessor`, `EncryptCredentialProcessor`, `PlainCredentialProcessor`

### Credential Processors

- **HashCredentialProcessor**: Hash passwords using Laravel's built-in hasher
- **EncryptCredentialProcessor**: Encrypt credentials for storage
- **PlainCredentialProcessor**: Store credentials as plain text (for tokens)
- **CustomJwtCredentialProcessor**: Custom JWT processing logic

## Use Cases

### 🎯 Keeping Users Table Lean

#### **Multi-channel Applications**

```php
// User table stays clean with just core data
User: { id: 1, name: "John Doe", email: null, mobile: null }

// Identities table handles all auth methods
UserIdentity: [
    { type: 'email', identifier: 'john@work.com' },
    { type: 'mobile', identifier: '+1234567890' },
    { type: 'github', identifier: 'john_doe' }
]
```

#### **Enterprise Systems**

```php
// Support employee IDs, badge numbers, API keys without bloating user table
UserIdentity: [
    { type: 'employee_id', identifier: 'EMP001' },
    { type: 'api_key', identifier: 'production_system' }
]
```

#### **SaaS Applications**

```php
// Different clients can have different auth methods
User: { id: 1, name: "Acme Corp" }

UserIdentity: [
    { type: 'oauth', identifier: 'acme_oauth' },
    { type: 'api_key', identifier: 'acme_api_key' },
    { type: 'saml', identifier: 'acme_sso' }
]
```

#### **International Products**

```php
// Support region-specific identifiers without schema changes
UserIdentity: [
    { type: 'mobile', identifier: '+8613812345678' }, // China
    { type: 'mobile', identifier: '+12125551234' }, // Singapore
    { type: 'email', identifier: 'user@example.co.uk' } // UK
]
```

**Key Benefits:**

- 🏗️ **Schema Stability** - User table structure remains stable
- ⚡ **Query Performance** - User queries stay fast regardless of auth methods
- 🔄 **Easy Migration** - Add new auth types without touching user table
- 📊 **Clean Analytics** - Clear separation of authentication data

## Configuration Options

```php
// config/user-identities.php
return [
    // Register your identity types
    'types' => [
        'email' => EmailIdentityType::class,
        // ... other types
    ],

    // Security option
    'require_verification' => false, // Only allow verified identities for login
];
```

## Requirements

- PHP >= 8.1
- Laravel >= 12.0

## License

MIT

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

For detailed API documentation, check the inline code comments in the source files.
