<?php

namespace Tests\Unit\Services\User;

use App\BO\CustomerAccountBo;
use App\Exceptions\DomainException;
use App\Interfaces\HashServiceInterface;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Resources\CustomerAccountData;
use App\Resources\UserData;
use App\Services\User\UserRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class UserRegistrationServiceTest extends TestCase
{
    protected $userRepositoryMock;
    protected $hashServiceMock;
    protected $customerAccountBoMock;
    protected $userRegistrationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepositoryMock = Mockery::mock(UserRepository::class);
        $this->hashServiceMock = Mockery::mock(HashServiceInterface::class);
        $this->customerAccountBoMock = Mockery::mock(CustomerAccountBo::class);

        $this->userRegistrationService = new UserRegistrationService(
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

    public function testRegisterSuccess()
    {
        $request = new Request([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'cpf' => '12345678900',
            'birth_date' => '1990-01-01',
            'phone' => '11999999999',
            'bank_account_type' => 'checking'
        ]);

        $userData = Mockery::mock(UserData::class);
        $userData->shouldReceive('setName')
            ->once()
            ->with('Test User')
            ->andReturnSelf();
        $userData->shouldReceive('setCpf')
            ->once()
            ->with('12345678900')
            ->andReturnSelf();
        $userData->shouldReceive('setBirthDate')
            ->once()
            ->with('1990-01-01')
            ->andReturnSelf();
        $userData->shouldReceive('setPhone')
            ->once()
            ->with('11999999999')
            ->andReturnSelf();
        $userData->shouldReceive('setEmail')
            ->once()
            ->with('test@example.com')
            ->andReturnSelf();
        $userData->shouldReceive('setPassword')
            ->once()
            ->with('hashed_password')
            ->andReturnSelf();
        $userData->shouldReceive('toArray')
            ->once()
            ->andReturn([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'hashed_password',
                'cpf' => '12345678900',
                'birth_date' => '1990-01-01',
                'phone' => '11999999999'
            ]);
        $userData->shouldReceive('setCustomerAccount')
            ->once()
            ->andReturnSelf();

        $user = new User();
        $user->id = 1;
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->cpf = '12345678900';

        $customerAccountData = Mockery::mock(CustomerAccountData::class);

        $this->hashServiceMock->shouldReceive('hash')
            ->once()
            ->with('password123')
            ->andReturn('hashed_password');

        $this->userRepositoryMock->shouldReceive('Register')
            ->once()
            ->with([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'hashed_password',
                'cpf' => '12345678900',
                'birth_date' => '1990-01-01',
                'phone' => '11999999999'
            ])
            ->andReturn($user);

        $this->customerAccountBoMock->shouldReceive('createCustomerAccount')
            ->once()
            ->with($user, 'checking')
            ->andReturn($customerAccountData);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        $result = $this->userRegistrationService->register($request, $userData);

        $this->assertSame($userData, $result);
    }

    public function testRegisterWithException()
    {
        $request = new Request([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'cpf' => '12345678900'
        ]);

        $userData = Mockery::mock(UserData::class);
        $userData->shouldReceive('setName')
            ->once()
            ->with('Test User')
            ->andReturnSelf();
        $userData->shouldReceive('setCpf')
            ->once()
            ->with('12345678900')
            ->andReturnSelf();
        $userData->shouldReceive('setBirthDate')
            ->once()
            ->with(null)
            ->andReturnSelf();
        $userData->shouldReceive('setPhone')
            ->once()
            ->with(null)
            ->andReturnSelf();
        $userData->shouldReceive('setEmail')
            ->once()
            ->with('test@example.com')
            ->andReturnSelf();
        $userData->shouldReceive('setPassword')
            ->once()
            ->with('hashed_password')
            ->andReturnSelf();
        $userData->shouldReceive('toArray')
            ->once()
            ->andReturn([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'hashed_password',
                'cpf' => '12345678900'
            ]);

        $this->hashServiceMock->shouldReceive('hash')
            ->once()
            ->with('password123')
            ->andReturn('hashed_password');

        $this->userRepositoryMock->shouldReceive('Register')
            ->once()
            ->with([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'hashed_password',
                'cpf' => '12345678900'
            ])
            ->andThrow(new \Exception('Database error'));

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $this->expectException(DomainException::class);
        $this->userRegistrationService->register($request, $userData);
    }
}
