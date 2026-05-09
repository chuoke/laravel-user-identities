<?php

namespace Chuoke\UserIdentities\Auth;

use Chuoke\UserIdentities\Actions\UserIdentityFind;
use Chuoke\UserIdentities\Models\UserIdentity;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class IdentityUserProvider extends EloquentUserProvider
{
    protected const CURRENT_AUTH_IDENTITY_KEY = '_auth_identity';

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials)) {
            return null;
        }

        $authenticatableType = $credentials['authenticatable_type'] ?? null;
        $identityType = $credentials['type'] ?? $credentials['identity_type'] ?? null;
        $identifier = $credentials['identifier'] ?? null;

        if (! $identityType && ! empty($credentials['email'])) {
            $identityType = 'email';
            $identifier = $credentials['email'];
        }

        if (! $identifier) {
            return null;
        }

        $identity = (new UserIdentityFind())->execute($identifier, $identityType, $authenticatableType);
        if (! $identity) {
            return null;
        }

        /** @var (Authenticatable&Model)|null $authenticatable */
        $authenticatable = $identity->authenticatable;
        if (! $authenticatable) {
            return null;
        }

        $authenticatable->setRelation(self::CURRENT_AUTH_IDENTITY_KEY, $identity);

        return $authenticatable;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  Authenticatable&Model  $user
     * @param  array<string, string>  $credentials
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $credentials = $credentials['credentials'] ?? $credentials['password'] ?? null;

        if (! $credentials) {
            return false;
        }

        /** @var UserIdentity|null $identity */
        $identity = $user->getRelation(self::CURRENT_AUTH_IDENTITY_KEY);

        if (! $identity) {
            return false;
        }

        if (! $identity->isVerified() && $this->shouldRequireVerification()) {
            return false;
        }

        return $identity->verifyCredentials($credentials);
    }

    /**
     * Determine if verification should be required for authentication.
     */
    protected function shouldRequireVerification(): bool
    {
        return config('user-identities.require_verification', false);
    }
}
