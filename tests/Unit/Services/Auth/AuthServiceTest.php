<?php

namespace Tests\Unit\Services\Auth;

use App\BO\CustomerAccountBo;
use App\Exceptions\AuthenticationException;
use App\Exceptions\InvalidPasswordException;
use App\Exceptions\UserNotFoundException;
use App\Interfaces\HashServiceInterface;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Resources\CustomerAccountData;
use App\Resources\UserData;
use App\Services\Auth\AuthService;
use Laravel\Passport\Token;
use Mockery;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    protected $userRepositoryMock;
    protected $hashServiceMock;
    protected $customerAccountBoMock;
    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepositoryMock = Mockery::mock(UserRepository::class);
        $this->hashServiceMock = Mockery::mock(HashServiceInterface::class);
        $this->customerAccountBoMock = Mockery::mock(CustomerAccountBo::class);

        $this->authService = new AuthService(
            $this->userRepositoryMock,
            $this->hashServiceMock,
            $this->customerAccountBoMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testLoginSuccess()
    {
        $request = (object)[
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        // Usar Mockery para criar o mock do User
        $user = Mockery::mock(User::class);
        $user->id = 1;
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = 'hashed_password';

        // Mock para createToken
        $tokenMock = Mockery::mock();
        $tokenMock->shouldReceive('accessToken')
            ->andReturn('access-token-123');

        $user->shouldReceive('createToken')
            ->once()
            ->with('UserToken')
            ->andReturn($tokenMock);

        // Permitir definir token como propriedade
        $user->shouldReceive('setAttribute')
            ->with('token', 'access-token-123')
            ->andReturnSelf();

        // Permitir ler a propriedade token
        $user->shouldReceive('__get')
            ->with('token')
            ->andReturn('access-token-123');

        $this->userRepositoryMock->shouldReceive('findByEmail')
            ->once()
            ->with($request)
            ->andReturn($user);

        $this->hashServiceMock->shouldReceive('check')
            ->once()
            ->with('password123', 'hashed_password')
            ->andReturn(true);

        $result = $this->authService->login($request);

        $this->assertSame($user, $result);
    }

    public function testLoginWithNonExistentUser()
    {
        $request = (object)[
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $this->userRepositoryMock->shouldReceive('findByEmail')
            ->once()
            ->with($request)
            ->andReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->authService->login($request);
    }

    public function testLoginWithInvalidPassword()
    {
        $request = (object)[
            'email' => 'test@example.com',
            'password' => 'wrong-password'
        ];

        // Usar Mockery para criar o mock do User
        $user = Mockery::mock(User::class);
        $user->password = 'hashed_password';

        $this->userRepositoryMock->shouldReceive('findByEmail')
            ->once()
            ->with($request)
            ->andReturn($user);

        $this->hashServiceMock->shouldReceive('check')
            ->once()
            ->with('wrong-password', 'hashed_password')
            ->andReturn(false);

        $this->expectException(InvalidPasswordException::class);
        $this->authService->login($request);
    }

    public function testValidateAuthenticatedUserSuccess()
    {
        // Usar Mockery para criar o mock do User
        $user = Mockery::mock(User::class);
        $user->email = 'test@example.com';

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('user')
            ->andReturn($user);

        $result = $this->authService->validateAuthenticatedUser($request);

        $this->assertSame($user, $result);
    }

    public function testValidateAuthenticatedUserWithInvalidToken()
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('user')
            ->andReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->authService->validateAuthenticatedUser($request);
    }

    public function testLogoutSuccess()
    {
        $token = Mockery::mock(Token::class);
        $token->shouldReceive('revoke')
            ->once()
            ->andReturn(true);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('token')
            ->once()
            ->andReturn($token);
        $user->email = 'test@example.com';

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('user')
            ->andReturn($user);

        $result = $this->authService->logout($request);

        $this->assertTrue($result);
    }

    public function testMapUserToUserData()
    {
        // Usar Mockery para criar o mock do User
        $user = Mockery::mock(User::class);
        $user->shouldReceive('__get')
            ->with('id')
            ->andReturn(1);
        $user->shouldReceive('__get')
            ->with('name')
            ->andReturn('Test User');
        $user->shouldReceive('__get')
            ->with('email')
            ->andReturn('test@example.com');
        $user->shouldReceive('__get')
            ->with('cpf')
            ->andReturn('12345678900');
        $user->shouldReceive('__get')
            ->with('phone')
            ->andReturn('11999999999');
        $user->shouldReceive('__get')
            ->with('birth_date')
            ->andReturn('1990-01-01');
        $user->shouldReceive('__get')
            ->with('token')
            ->andReturn('access-token-123');

        $customerAccount = Mockery::mock(CustomerAccountData::class);
        $customerAccount->shouldReceive('toArray')
            ->andReturn([
                'number_account' => '123.456.7890-1',
                'balance' => 0
            ]);

        $this->customerAccountBoMock->shouldReceive('findByUserId')
            ->once()
            ->with(1)
            ->andReturn($customerAccount);

        $userData = Mockery::mock(UserData::class);
        $userData->shouldReceive('setName')
            ->once()
            ->andReturnSelf();
        $userData->shouldReceive('setEmail')
            ->once()
            ->andReturnSelf();
        $userData->shouldReceive('setCpf')
            ->once()
            ->andReturnSelf();
        $userData->shouldReceive('setPhone')
            ->once()
            ->andReturnSelf();
        $userData->shouldReceive('setBirthDate')
            ->once()
            ->andReturnSelf();
        $userData->shouldReceive('setToken')
            ->once()
            ->andReturnSelf();
        $userData->shouldReceive('setCustomerAccount')
            ->once()
            ->andReturnSelf();
        $userData->shouldReceive('toArray')
            ->once()
            ->andReturn([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'cpf' => '12345678900',
                'phone' => '11999999999',
                'birth_date' => '1990-01-01',
                'token' => 'access-token-123',
                'customer_account' => [
                    'number_account' => '123.456.7890-1',
                    'balance' => 0
                ]
            ]);

        $result = $this->authService->mapUserToUserData($user, $userData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('token', $result);
    }
}
