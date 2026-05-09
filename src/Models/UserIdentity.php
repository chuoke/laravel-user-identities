<?php

namespace Chuoke\UserIdentities\Models;

use Chuoke\UserIdentities\CredentialProcessor;
use Chuoke\UserIdentities\IdentityTypeRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use SensitiveParameter;

/**
 * @property int $id
 * @property string $authenticatable_type
 * @property int $authenticatable_id
 * @property string $type
 * @property string $identifier
 * @property string|null $credentials
 * @property string|null $verified_at
 * @property string $created_at
 * @property string $updated_at
 */
class UserIdentity extends Model
{
    protected $fillable = [
        'type',
        'identifier',
        'credentials',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('user-identities.table', 'user_identities');
    }

    /**
     * Get the authenticatable entity that owns the identity.
     *
     * @return MorphTo<Model, $this>
     */
    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if the identity is verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Mark the identity as verified.
     */
    public function markAsVerified(): bool
    {
        if ($this->isVerified()) {
            return true;
        }

        return $this->update(['verified_at' => now()]);
    }

    /**
     * Mark the identity as unverified.
     */
    public function markAsUnverified(): bool
    {
        if (! $this->isVerified()) {
            return true;
        }

        return $this->update(['verified_at' => null]);
    }

    /**
     * Verify credentials against the stored credentials.
     */
    public function verifyCredentials(#[SensitiveParameter] string $credentials): bool
    {
        $typeConfig = IdentityTypeRegistry::get($this->type);

        return CredentialProcessor::verify($typeConfig, $credentials, $this->credentials);
    }
}
