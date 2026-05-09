<?php

namespace Chuoke\UserIdentities\Tests\Unit;

use Chuoke\UserIdentities\CredentialProcessor;
use Chuoke\UserIdentities\Types\ApiKeyIdentityType;
use Chuoke\UserIdentities\Types\EmailIdentityType;
use Chuoke\UserIdentities\Types\GithubIdentityType;
use Tests\TestCase;

class CredentialProcessorTest extends TestCase
{
    /** @test */
    public function it_hashes_password_credentials()
    {
        $type = new EmailIdentityType();
        $plainPassword = 'password123';

        $processed = CredentialProcessor::process($type, $plainPassword);

        $this->assertNotNull($processed);
        $this->assertNotEquals($plainPassword, $processed);
        $this->assertTrue(CredentialProcessor::verify($type, $plainPassword, $processed));
    }

    /** @test */
    public function it_encrypts_api_key_credentials()
    {
        $type = new ApiKeyIdentityType();
        $apiKey = 'secret_api_key_123';

        $processed = CredentialProcessor::process($type, $apiKey);

        $this->assertNotNull($processed);
        $this->assertNotEquals($apiKey, $processed);
        $this->assertTrue(CredentialProcessor::verify($type, $apiKey, $processed));
    }

    /** @test */
    public function it_stores_oauth_tokens_as_plain_text()
    {
        $type = new GithubIdentityType();
        $oauthToken = 'github_oauth_token_123';

        $processed = CredentialProcessor::process($type, $oauthToken);

        $this->assertEquals($oauthToken, $processed);
        $this->assertTrue(CredentialProcessor::verify($type, $oauthToken, $processed));
    }

    /** @test */
    public function it_verifies_hashed_passwords_correctly()
    {
        $type = new EmailIdentityType();
        $plainPassword = 'password123';
        $hashedPassword = CredentialProcessor::process($type, $plainPassword);

        $this->assertTrue(CredentialProcessor::verify($type, $plainPassword, $hashedPassword));
        $this->assertFalse(CredentialProcessor::verify($type, 'wrongpassword', $hashedPassword));
    }

    /** @test */
    public function it_verifies_encrypted_api_keys_correctly()
    {
        $type = new ApiKeyIdentityType();
        $apiKey = 'secret_api_key_123';
        $encryptedKey = CredentialProcessor::process($type, $apiKey);

        $this->assertTrue(CredentialProcessor::verify($type, $apiKey, $encryptedKey));
        $this->assertFalse(CredentialProcessor::verify($type, 'wrong_key', $encryptedKey));
    }

    /** @test */
    public function it_verifies_oauth_tokens_correctly()
    {
        $type = new GithubIdentityType();
        $oauthToken = 'github_oauth_token_123';

        $this->assertTrue(CredentialProcessor::verify($type, $oauthToken, $oauthToken));
        $this->assertFalse(CredentialProcessor::verify($type, 'wrong_token', $oauthToken));
    }

    /** @test */
    public function it_handles_null_credentials_for_non_credential_types()
    {
        $type = new GithubIdentityType();

        $processed = CredentialProcessor::process($type, '');

        $this->assertNull($processed);
    }

    /** @test */
    public function it_handles_empty_credentials()
    {
        $type = new EmailIdentityType();

        $processed = CredentialProcessor::process($type, '');

        $this->assertNotNull($processed);
    }
}
