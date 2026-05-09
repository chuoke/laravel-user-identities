<?php

namespace Chuoke\UserIdentities\Tests\Feature;

use Chuoke\UserIdentities\Actions\UserIdentityCreate;
use Chuoke\UserIdentities\Concerns\HasIdentities;
use Chuoke\UserIdentities\Models\UserIdentity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class IdentityTypesTest extends TestCase
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
    public function it_can_create_email_identity()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity = $action->execute($user, 'email', 'test@example.com', 'password123', false);

        $this->assertInstanceOf(UserIdentity::class, $identity);
        $this->assertEquals('email', $identity->type);
        $this->assertEquals('test@example.com', $identity->identifier);
        $this->assertNotNull($identity->credentials);
        $this->assertTrue($identity->verifyCredentials('password123'));
    }

    /** @test */
    public function it_can_create_mobile_identity()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity = $action->execute($user, 'mobile', '+1234567890', 'password123', false);

        $this->assertEquals('mobile', $identity->type);
        $this->assertEquals('+1234567890', $identity->identifier);
        $this->assertTrue($identity->verifyCredentials('password123'));
    }

    /** @test */
    public function it_can_create_username_identity()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity = $action->execute($user, 'username', 'johndoe', 'password123', false);

        $this->assertEquals('username', $identity->type);
        $this->assertEquals('johndoe', $identity->identifier);
        $this->assertTrue($identity->verifyCredentials('password123'));
    }

    /** @test */
    public function it_can_create_github_oauth_identity()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity = $action->execute($user, 'github', 'github_user_123', 'oauth_token_here', true);

        $this->assertEquals('github', $identity->type);
        $this->assertEquals('github_user_123', $identity->identifier);
        $this->assertEquals('oauth_token_here', $identity->credentials);
        $this->assertNotNull($identity->verified_at);
    }

    /** @test */
    public function it_can_create_google_oauth_identity()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity = $action->execute($user, 'google', 'google_user_123', 'google_oauth_token', true);

        $this->assertEquals('google', $identity->type);
        $this->assertEquals('google_user_123', $identity->identifier);
        $this->assertEquals('google_oauth_token', $identity->credentials);
        $this->assertNotNull($identity->verified_at);
    }

    /** @test */
    public function it_can_create_twitter_oauth_identity()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity = $action->execute($user, 'twitter', 'twitter_user_123', 'twitter_oauth_token', true);

        $this->assertEquals('twitter', $identity->type);
        $this->assertEquals('twitter_user_123', $identity->identifier);
        $this->assertEquals('twitter_oauth_token', $identity->credentials);
        $this->assertNotNull($identity->verified_at);
    }

    /** @test */
    public function it_can_create_api_key_identity()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity = $action->execute($user, 'api_key', 'my_app', 'secret_api_key', true);

        $this->assertEquals('api_key', $identity->type);
        $this->assertEquals('my_app', $identity->identifier);
        $this->assertNotNull($identity->credentials);
        $this->assertNotNull($identity->verified_at);
    }

    /** @test */
    public function it_can_create_jwt_identity()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity = $action->execute($user, 'jwt', 'jwt_subject', 'jwt_token_here', true);

        $this->assertEquals('jwt', $identity->type);
        $this->assertEquals('jwt_subject', $identity->identifier);
        $this->assertEquals('jwt_token_here', $identity->credentials);
        $this->assertNotNull($identity->verified_at);
    }

    /** @test */
    public function it_can_create_custom_jwt_identity()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity = $action->execute($user, 'custom_jwt', 'custom_jwt_subject', 'custom_jwt_token', true);

        $this->assertEquals('custom_jwt', $identity->type);
        $this->assertEquals('custom_jwt_subject', $identity->identifier);
        $this->assertEquals('custom_jwt_token', $identity->credentials);
        $this->assertNotNull($identity->verified_at);
    }

    /** @test */
    public function it_prevents_duplicate_identity_types_for_same_user()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity1 = $action->execute($user, 'email', 'test@example.com', 'password123', false);

        $this->expectException(\Exception::class);
        $action->execute($user, 'email', 'test2@example.com', 'password456', false);
    }

    /** @test */
    public function it_allows_same_identity_type_for_different_users()
    {
        $user1 = $this->testUser::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
        ]);

        $user2 = $this->testUser::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity1 = $action->execute($user1, 'email', 'user1@example.com', 'password123', false);
        $identity2 = $action->execute($user2, 'email', 'user2@example.com', 'password456', false);

        $this->assertNotEquals($identity1->id, $identity2->id);
        $this->assertEquals($user1->id, $identity1->authenticatable_id);
        $this->assertEquals($user2->id, $identity2->authenticatable_id);
    }

    /** @test */
    public function it_validates_email_format()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->expectException(ValidationException::class);
        $action = new UserIdentityCreate();
        $action->execute($user, 'email', 'invalid-email', 'password123', false);
    }

    /** @test */
    public function it_validates_mobile_format()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->expectException(ValidationException::class);
        $action = new UserIdentityCreate();
        $action->execute($user, 'mobile', 'invalid-mobile', 'password123', false);
    }
}
