<?php

namespace Chuoke\UserIdentities\Actions;

use Chuoke\UserIdentities\CredentialProcessor;
use Chuoke\UserIdentities\IdentityTypeRegistry;
use Chuoke\UserIdentities\Models\UserIdentity;
use Illuminate\Database\Eloquent\Model;

class UserIdentityPasswordUpdate
{
    /**
     * Sync password to all passwordable identities for a user.
     */
    public function execute(Model $authenticatable, string $password): void
    {
        $passwordableTypes = config('user-identities.passwordable_types', []);

        if (empty($passwordableTypes)) {
            return;
        }

        UserIdentity::where('authenticatable_type', $authenticatable->getMorphClass())
            ->where('authenticatable_id', $authenticatable->getKey())
            ->whereIn('type', $passwordableTypes)
            ->get()
            ->each(function ($identity) use ($password) {
                $typeConfig = IdentityTypeRegistry::get($identity->type);

                if ($typeConfig && $typeConfig->requiresCredential()) {
                    $processedCredentials = CredentialProcessor::process($typeConfig, $password);
                    $identity->update(['credentials' => $processedCredentials]);
                }
            });
    }
}
