<?php

namespace Tests\Unit\BO;

use App\BO\UserBo;
use App\Exceptions\AuthenticationException;
use App\Exceptions\InvalidPasswordException;
use App\Exceptions\UserNotFoundException;
use App\Models\User;
use App\Resources\UserData;
use App\Services\Auth\AuthService;
use App\Services\User\UserRegistrationService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class UserBoTest extends TestCase
{
    protected $authServiceMock;
    protected $userRegistrationServiceMock;
    protected $userDataMock;
    protected $userBo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authServiceMock = Mockery::mock(AuthService::class);
        $this->userRegistrationServiceMock = Mockery::mock(UserRegistrationService::class);
        $this->userDataMock = Mockery::mock(UserData::class);

        $this->userBo = new UserBo(
            $this->userDataMock,
            $this->authServiceMock,
            $this->userRegistrationServiceMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testRegisterSuccess()
    {
        $request = new Request([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'cpf' => '12345678900'
        ]);

        $this->userRegistrationServiceMock->shouldReceive('register')
            ->once()
            ->with($request, $this->userDataMock)
            ->andReturn($this->userDataMock);

        $result = $this->userBo->Register($request);

        $this->assertSame($this->userDataMock, $result);
    }

    public function testLoginSuccess()
    {
        $request = new Request([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $user = new User();
        $user->id = 1;
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->cpf = '12345678900';
        $user->token = 'access-token-123';

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'cpf' => '12345678900',
            'token' => 'access-token-123'
        ];

        $this->authServiceMock->shouldReceive('login')
            ->once()
            ->with($request)
            ->andReturn($user);

        $this->authServiceMock->shouldReceive('mapUserToUserData')
            ->once()
            ->with($user, $this->userDataMock)
            ->andReturn($userData);

        $result = $this->userBo->Login($request);

        $this->assertSame($userData, $result);
    }

    public function testLoginWithInvalidCredentials()
    {
        $request = new Request([
            'email' => 'test@example.com',
            'password' => 'wrong-password'
        ]);

        $this->authServiceMock->shouldReceive('login')
            ->once()
            ->with($request)
            ->andThrow(new InvalidPasswordException());

        $this->expectException(InvalidPasswordException::class);
        $this->userBo->Login($request);
    }

    public function testLoginWithNonExistentUser()
    {
        $request = new Request([
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);

        $this->authServiceMock->shouldReceive('login')
            ->once()
            ->with($request)
            ->andThrow(new UserNotFoundException('nonexistent@example.com'));

        $this->expectException(UserNotFoundException::class);
        $this->userBo->Login($request);
    }

    public function testLogoutSuccess()
    {
        $request = new Request();

        $this->authServiceMock->shouldReceive('logout')
            ->once()
            ->with($request)
            ->andReturn(true);

        $result = $this->userBo->Logout($request);

        $this->assertTrue($result);
    }

    public function testLogoutWithInvalidToken()
    {
        $request = new Request();

        $this->authServiceMock->shouldReceive('logout')
            ->once()
            ->with($request)
            ->andThrow(new AuthenticationException());

        $this->expectException(AuthenticationException::class);
        $this->userBo->Logout($request);
    }
}
