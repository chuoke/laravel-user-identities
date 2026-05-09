<?php

namespace Chuoke\UserIdentities\Auth;

use Chuoke\UserIdentities\Actions\UserIdentityFindByAuthenticatable;
use Chuoke\UserIdentities\Models\UserIdentity;
use Illuminate\Auth\Notifications\VerifyEmail;

trait MustVerifyEmail
{
    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        $emailIdentity = $this->getEmailIdentity();

        return $emailIdentity && $emailIdentity->isVerified();
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        $emailIdentity = $this->getEmailIdentity();

        if (! $emailIdentity) {
            return false;
        }

        return $emailIdentity->markAsVerified();
    }

    /**
     * Mark the given user's email as unverified.
     *
     * @return bool
     */
    public function markEmailAsUnverified()
    {
        $emailIdentity = $this->getEmailIdentity();

        if (! $emailIdentity) {
            return false;
        }

        return $emailIdentity->markAsUnverified();
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        $emailIdentity = $this->getEmailIdentity();

        return $emailIdentity ? $emailIdentity->identifier : '';
    }

    public function routeNotificationForMail()
    {
        return $this->getEmailForVerification();
    }

    protected function getEmailIdentity(): ?UserIdentity
    {
        return (new UserIdentityFindByAuthenticatable())
            ->execute($this, $this->getEmailIdentityType());
    }

    protected function getEmailIdentityType(): string
    {
        return 'email';
    }
}
