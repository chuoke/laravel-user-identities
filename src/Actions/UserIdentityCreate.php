<?php

namespace Chuoke\UserIdentities\Actions;

use Chuoke\UserIdentities\Contracts\IdentityTypeInterface;
use Chuoke\UserIdentities\CredentialProcessor;
use Chuoke\UserIdentities\Exceptions\DuplicateIdentityException;
use Chuoke\UserIdentities\Exceptions\IdentityValidationException;
use Chuoke\UserIdentities\Exceptions\UnsupportedIdentityTypeException;
use Chuoke\UserIdentities\IdentityTypeRegistry;
use Chuoke\UserIdentities\Models\UserIdentity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UserIdentityCreate
{
    /**
     * Create a new user identity.
     *
     * @param  Model  $authenticatable  The authenticatable entity (e.g., User)
     * @param  string  $type  The type of identity (e.g., 'email', 'mobile', 'github')
     * @param  string  $identifier  The identifier value (e.g., 'user@example.com')
     * @param  string|null  $credentials  The credentials (e.g., password, token)
     * @param  bool  $verified  Whether the identity is already verified
     *
     * @throws UnsupportedIdentityTypeException
     * @throws DuplicateIdentityException
     * @throws IdentityValidationException
     */
    public function execute(
        Model $authenticatable,
        string $type,
        string $identifier,
        ?string $credentials = null,
        ?bool $verified = false
    ): UserIdentity {
        // Get identity type configuration
        $typeConfig = IdentityTypeRegistry::get($type);

        // Validate input data
        $this->validateInput($typeConfig, $type, $identifier, $credentials);

        // Check uniqueness
        $this->checkUniqueness($authenticatable, $type, $identifier);

        // Process credentials
        $processedCredentials = CredentialProcessor::process($typeConfig, $credentials);

        $identity = new UserIdentity([
            'type' => $type,
            'identifier' => $identifier,
            'credentials' => $processedCredentials,
            'verified_at' => ($verified ?? false) || $typeConfig->isDefaultVerified() ? now() : null,
        ]);

        $identity->authenticatable()->associate($authenticatable);
        $identity->save();

        return $identity;
    }

    /**
     * Validate input data against identity type rules.
     */
    protected function validateInput(
        IdentityTypeInterface $typeConfig,
        string $type,
        string $identifier,
        ?string $credentials
    ): void {
        $data = [
            'type' => $type,
            'identifier' => $identifier,
            'credentials' => $credentials,
        ];

        $rules = array_merge(
            ['type' => 'required|string'],
            $typeConfig->getValidationRules()
        );

        // If credentials are not required, remove credentials validation rules
        if (! $typeConfig->requiresCredential()) {
            unset($rules['credentials']);
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw IdentityValidationException::make($validator->errors());
        }
    }

    /**
     * Check for duplicate identities.
     */
    protected function checkUniqueness(Model $authenticatable, string $type, string $identifier): void
    {
        (new UserIdentityUniquenessCheck())->execute(
            $authenticatable->getMorphClass(),
            $authenticatable->getKey(),
            $type,
            $identifier
        );
    }
}
