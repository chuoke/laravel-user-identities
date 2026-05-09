<?php

namespace Chuoke\UserIdentities\Actions;

use Chuoke\UserIdentities\Contracts\IdentityTypeInterface;
use Chuoke\UserIdentities\CredentialProcessor;
use Chuoke\UserIdentities\Dtos\UserIdentityUpdateData;
use Chuoke\UserIdentities\Exceptions\DuplicateIdentityException;
use Chuoke\UserIdentities\Exceptions\IdentityValidationException;
use Chuoke\UserIdentities\Exceptions\UnsupportedIdentityTypeException;
use Chuoke\UserIdentities\IdentityTypeRegistry;
use Chuoke\UserIdentities\Models\UserIdentity;
use Illuminate\Support\Facades\Validator;

class UserIdentityUpdate
{
    /**
     * Update an existing user identity with full validation.
     *
     * @throws UnsupportedIdentityTypeException
     * @throws IdentityValidationException
     * @throws DuplicateIdentityException
     */
    public function execute(UserIdentity $identity, UserIdentityUpdateData $data): UserIdentity
    {
        $typeConfig = IdentityTypeRegistry::get($identity->type);

        // Prepare update data
        $updateData = [];

        // Update identifier
        if ($data->hasIdentifier()) {
            $updateData['identifier'] = $data->identifier;

            // Check uniqueness (if identifier is updated)
            if ($data->identifier !== $identity->identifier) {
                $this->checkUniqueness($identity, $data->identifier);
            }
        }

        // Update credentials
        if ($data->hasCredentials()) {
            if ($data->credentials === null && ! $typeConfig->requiresCredential()) {
                $updateData['credentials'] = null;
            } elseif ($data->credentials !== null) {
                $updateData['credentials'] = $data->credentials;
            }
        }

        // Validate data
        $this->validateUpdateData($identity, $typeConfig, $updateData);

        // Process credentials
        if (isset($updateData['credentials'])) {
            $updateData['credentials'] = $this->processCredentials($typeConfig, $updateData['credentials']);
        }

        // Execute update
        foreach ($updateData as $key => $value) {
            $identity->$key = $value;
        }

        if ($identity->isDirty('identifier')) {
            $identity->verified_at = ($data->verified ?? false) || $typeConfig->isDefaultVerified()
                ? now() : null;
        }

        if ($identity->isDirty()) {
            $identity->save();
        }

        return $identity;
    }

    /**
     * Validate update data.
     */
    protected function validateUpdateData(UserIdentity $identity, IdentityTypeInterface $typeConfig, array $data): void
    {
        $rules = [];

        if (isset($data['identifier'])) {
            $identityRules = $typeConfig->getValidationRules();
            $rules['identifier'] = $identityRules['identifier'] ?? ['required', 'string'];
        }

        if (array_key_exists('credentials', $data) && $data['credentials'] !== null) {
            $identityRules = $typeConfig->getValidationRules();
            if (isset($identityRules['credentials'])) {
                $rules['credentials'] = $identityRules['credentials'];
            }
        }

        if (empty($rules)) {
            return;
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw IdentityValidationException::make($validator->errors());
        }
    }

    /**
     * Check uniqueness for updated identifier.
     */
    protected function checkUniqueness(UserIdentity $identity, string $newIdentifier): void
    {
        (new UserIdentityUniquenessCheck())->execute(
            $identity->authenticatable_type,
            $identity->authenticatable_id,
            $identity->type,
            $newIdentifier,
            $identity->id
        );
    }

    /**
     * Process credentials for storage.
     */
    protected function processCredentials($typeConfig, ?string $credentials): ?string
    {
        if ($credentials === null) {
            return null;
        }

        return CredentialProcessor::process($typeConfig, $credentials);
    }
}
