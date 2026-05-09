<?php

namespace Chuoke\UserIdentities\Tests\Feature;

use Chuoke\UserIdentities\Actions\UserIdentityCreate;
use Chuoke\UserIdentities\Auth\IdentityGuard;
use Chuoke\UserIdentities\Auth\IdentityUserProvider;
use Chuoke\UserIdentities\Concerns\HasIdentities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected string $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUser = $this->createTestUser();
    }

    protected function createTestUser(): string
    {
        return new class() extends Model
        {
            use HasIdentities;

            protected $table = 'users';

            protected $fillable = ['name', 'email'];
        };
    }

    /** @test */
    public function it_can_authenticate_with_email_password()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $createAction = new UserIdentityCreate();
        $createAction->execute($user, 'email', 'test@example.com', 'password123', false);

        $provider = new IdentityUserProvider(app('hash'), $this->testUser);
        $guard = new IdentityGuard('web', $provider, session()->driver());

        $result = $guard->attempt([
            'type' => 'email',
            'identifier' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertTrue($result);
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_can_authenticate_with_oauth_token()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $createAction = new UserIdentityCreate();
        $createAction->execute(
            $user,
            'github',
            'github_user_123',
            'oauth_token_here',
            true
        );

        $provider = new IdentityUserProvider(app('hash'), $this->testUser);
        $guard = new IdentityGuard('web', $provider, session()->driver());

        $result = $guard->attempt([
            'type' => 'github',
            'identifier' => 'github_user_123',
            'password' => 'oauth_token_here',
        ]);

        $this->assertTrue($result);
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_can_authenticate_with_api_key()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $createAction = new UserIdentityCreate();
        $createAction->execute(
            $user,
            'api_key',
            'my_app',
            'secret_api_key',
            true
        );

        $provider = new IdentityUserProvider(app('hash'), $this->testUser);
        $guard = new IdentityGuard('web', $provider, session()->driver());

        $result = $guard->attempt([
            'type' => 'api_key',
            'identifier' => 'my_app',
            'credentials' => 'secret_api_key',
        ]);

        $this->assertTrue($result);
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_fails_authentication_with_wrong_credentials()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $createAction = new UserIdentityCreate();
        $createAction->execute($user, 'email', 'test@example.com', 'password123', false);

        $provider = new IdentityUserProvider(app('hash'), $this->testUser);
        $guard = new IdentityGuard('web', $provider, session()->driver());

        $result = $guard->attempt([
            'type' => 'email',
            'identifier' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertFalse($result);
        $this->assertGuest();
    }

    /** @test */
    public function it_fails_authentication_with_nonexistent_identity()
    {
        $provider = new IdentityUserProvider(app('hash'), $this->testUser);
        $guard = new IdentityGuard('web', $provider, session()->driver());

        $result = $guard->attempt([
            'type' => 'email',
            'identifier' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertFalse($result);
        $this->assertGuest();
    }

    /** @test */
    public function it_respects_verification_requirement()
    {
        config(['user-identities.require_verification' => true]);

        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create unverified identity
        $createAction = new UserIdentityCreate();
        $createAction->execute(
            $user,
            'email',
            'test@example.com',
            'password123',
            false
        );

        $provider = new IdentityUserProvider(app('hash'), $this->testUser);
        $guard = new IdentityGuard('web', $provider, session()->driver());

        $result = $guard->attempt([
            'type' => 'email',
            'identifier' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertFalse($result);
        $this->assertGuest();
    }

    /** @test */
    public function it_allows_verified_identity_when_verification_required()
    {
        config(['user-identities.require_verification' => true]);

        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create verified identity
        $createAction = new UserIdentityCreate();
        $createAction->execute(
            $user,
            'email',
            'test@example.com',
            'password123',
            true
        );

        $provider = new IdentityUserProvider(app('hash'), $this->testUser);
        $guard = new IdentityGuard('web', $provider, session()->driver());

        $result = $guard->attempt([
            'type' => 'email',
            'identifier' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertTrue($result);
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_can_retrieve_user_by_id()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $provider = new IdentityUserProvider(app('hash'), $this->testUser);
        $retrievedUser = $provider->retrieveById($user->id);

        $this->assertNotNull($retrievedUser);
        $this->assertEquals($user->id, $retrievedUser->id);
        $this->assertEquals($user->name, $retrievedUser->name);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_user_id()
    {
        $provider = new IdentityUserProvider(app('hash'), $this->testUser);
        $retrievedUser = $provider->retrieveById(999);

        $this->assertNull($retrievedUser);
    }
}
