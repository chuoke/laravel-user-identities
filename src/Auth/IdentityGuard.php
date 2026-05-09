<?php

namespace Chuoke\UserIdentities\Auth;

use Illuminate\Auth\SessionGuard;

class IdentityGuard extends SessionGuard
{
    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param  bool  $remember
     */
    public function attempt(array $credentials = [], $remember = false): bool
    {
        if (! $this->hasRequiredIdentityFields($credentials)) {
            return false;
        }

        $this->fireAttemptEvent($credentials, $remember);

        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        $this->fireFailedEvent($user, $credentials);

        return false;
    }

    /**
     * Check if credentials contain required identity fields.
     */
    protected function hasRequiredIdentityFields(array $credentials): bool
    {
        $type = $credentials['type'] ?? $credentials['identity_type'] ?? null;
        $identifier = $credentials['identifier'] ?? null;
        $password = $credentials['password'] ?? $credentials['credentials'] ?? null;

        return ! empty($type) && ! empty($identifier) && ! empty($password);
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed  $user
     * @param  array  $credentials
     */
    protected function hasValidCredentials($user, $credentials): bool
    {
        return $user !== null && $this->provider->validateCredentials($user, $credentials);
    }
}
