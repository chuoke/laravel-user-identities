<?php

namespace Chuoke\UserIdentities\Auth;

use Chuoke\UserIdentities\Actions\UserIdentityFind;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;

class UserIdentityAuthenticator
{
    /**
     * Authenticate user by identity type and identifier.
     */
    public function authenticate(
        string $identifier,
        #[SensitiveParameter] string $credentials,
        ?string $authenticatableType = null,
        ?string $type = null
    ): ?Model {
        $identity = (new UserIdentityFind())->execute($identifier, $type, $authenticatableType);
        if (! $identity) {
            return null;
        }

        if (! $identity->verifyCredentials($credentials)) {
            return null;
        }

        return $identity->authenticatable;
    }

    /**
     * Authenticate user by multiple identity types.
     *
     * @param  array  $credentials  [
     *                              'type' => 'email',
     *                              'identifier' => 'user@example.com',
     *                              'password' => 'secret',
     *                              'authenticatable_type' => 'user',
     *                              ]
     */
    public function authenticateByCredentials(array $credentials): ?Model
    {
        $type = $credentials['type'] ?? null;
        $identifier = $credentials['identifier'] ?? null;
        $password = $credentials['password'] ?? $credentials['credentials'] ?? null;
        $authenticatableType = $credentials['authenticatable_type'] ?? null;

        if (! $type || ! $identifier || ! $password) {
            return null;
        }

        return $this->authenticate(
            $identifier,
            $password,
            $authenticatableType,
            $type
        );
    }

    /**
     * Check if credentials are valid without returning user.
     */
    public function validateCredentials(string $identifier, #[SensitiveParameter] string $credentials, ?string $type = null): bool
    {
        $identity = (new UserIdentityFind())->execute($identifier, $type);

        if (! $identity) {
            return false;
        }

        return $identity->verifyCredentials($credentials);
    }
}
