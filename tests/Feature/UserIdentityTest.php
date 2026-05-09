<?php

namespace Chuoke\UserIdentities\Tests\Feature;

use Chuoke\UserIdentities\Actions\UserIdentityCreate;
use Chuoke\UserIdentities\Actions\UserIdentityUpdate;
use Chuoke\UserIdentities\Actions\UserIdentityVerify;
use Chuoke\UserIdentities\Concerns\HasIdentities;
use Chuoke\UserIdentities\Models\UserIdentity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIdentityTest extends TestCase
{
    use RefreshDatabase;

    protected string $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user model
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
    public function it_can_create_user_identity()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $identity = $action->execute(
            $user,
            'email',
            'test@example.com',
            'password123'
        );

        $this->assertInstanceOf(UserIdentity::class, $identity);
        $this->assertEquals('email', $identity->type);
        $this->assertEquals('test@example.com', $identity->identifier);
        $this->assertNotNull($identity->credentials);
    }

    /** @test */
    public function it_can_verify_identity()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $createAction = new UserIdentityCreate();
        $identity = $createAction->execute(
            $user,
            'email',
            'test@example.com',
            'password123'
        );

        $this->assertNull($identity->verified_at);

        $verifyAction = new UserIdentityVerify();
        $result = $verifyAction->execute($identity);

        $this->assertTrue($result);
        $identity->refresh();
        $this->assertNotNull($identity->verified_at);
    }

    /** @test */
    public function it_can_verify_credentials()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $createAction = new UserIdentityCreate();
        $identity = $createAction->execute(
            $user,
            'email',
            'test@example.com',
            'password123'
        );

        $this->assertTrue($identity->verifyCredentials('password123'));
        $this->assertFalse($identity->verifyCredentials('wrongpassword'));
    }

    /** @test */
    public function it_can_update_credentials()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $createAction = new UserIdentityCreate();
        $identity = $createAction->execute(
            $user,
            'email',
            'test@example.com',
            'oldpassword'
        );

        $updateAction = new UserIdentityUpdate();
        $updateAction->execute($identity, 'newpassword');

        $identity->refresh();
        $this->assertTrue($identity->verifyCredentials('newpassword'));
        $this->assertFalse($identity->verifyCredentials('oldpassword'));
    }

    /** @test */
    public function user_can_have_multiple_identities()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();

        $action->execute($user, 'email', 'test@example.com', 'password');
        $action->execute($user, 'mobile', '1234567890', 'password');
        $action->execute($user, 'github', 'testuser', null);

        $this->assertCount(3, $user->identities);
        $this->assertTrue($user->hasIdentity('email'));
        $this->assertTrue($user->hasIdentity('mobile'));
        $this->assertTrue($user->hasIdentity('github'));
    }

    /** @test */
    public function it_can_get_identity_by_type()
    {
        $user = $this->testUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $action = new UserIdentityCreate();
        $action->execute($user, 'email', 'test@example.com', 'password');

        $identity = $user->getIdentity('email');

        $this->assertNotNull($identity);
        $this->assertEquals('email', $identity->type);
        $this->assertEquals('test@example.com', $identity->identifier);
    }
}
