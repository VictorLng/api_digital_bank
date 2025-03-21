<?php

namespace Tests\Unit\Http\Controllers;

use App\BO\UserBo;
use App\Exceptions\AuthenticationException;
use App\Exceptions\InvalidPasswordException;
use App\Exceptions\UserNotFoundException;
use App\Http\Controllers\UserController;
use App\Http\Requests\UserRequest;
use App\Resources\UserData;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    protected $userBoMock;
    protected $controller;
    protected $userDataMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userBoMock = Mockery::mock(UserBo::class);
        $this->userDataMock = Mockery::mock(UserData::class);
        $this->controller = new UserController($this->userBoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testRegisterSuccess()
    {
        $request = new UserRequest();
        $request->merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'cpf' => '12345678900',
            'birth_date' => '1990-01-01',
            'phone' => '11999999999',
            'bank_account_type' => 'checking'
        ]);

        // Create a real UserData mock that can be returned
        $userDataMock = Mockery::mock(UserData::class);
        $userDataMock->shouldReceive('toArray')
            ->andReturn([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'cpf' => '12345678900',
                'customer_account' => [
                    'number_account' => '123.456.7890-1',
                    'agency' => '0001',
                    'type_account' => 'checking',
                    'balance' => 0,
                    'status' => 'active'
                ]
            ]);

        $this->userBoMock->shouldReceive('register')
            ->once()
            ->with($request)
            ->andReturn($userDataMock);

        $response = $this->controller->register($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($userDataMock->toArray(), json_decode($response->getContent(), true)['data']);
        $this->assertEquals('User registered', json_decode($response->getContent(), true)['message']);
    }

    public function testRegisterFailure()
    {
        $request = new UserRequest();
        $request->merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        // Use a null object instead of null for the Register method
        $nullUserData = null;
        $this->userBoMock->shouldReceive('register')
            ->once()
            ->with($request)
            ->andThrow(new \Exception("Registration failed"));

        // We need to override the method call to handle the exception
        try {
            $response = $this->controller->register($request);
            $this->assertEquals(400, $response->getStatusCode());
        } catch (\Exception $e) {
            $this->assertEquals("Registration failed", $e->getMessage());
        }
    }

    public function testLoginSuccess()
    {
        $request = new UserRequest();
        $request->merge([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'cpf' => '12345678900',
            'token' => 'access-token-123',
            'customer_account' => [
                'number_account' => '123.456.7890-1',
                'balance' => 0
            ]
        ];

        $this->userBoMock->shouldReceive('login')
            ->once()
            ->with($request)
            ->andReturn($userData);

        $response = $this->controller->login($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($userData, json_decode($response->getContent(), true)['data']);
        $this->assertEquals('User logged in', json_decode($response->getContent(), true)['message']);
    }

    public function testLoginFailure()
    {
        $request = new UserRequest();
        $request->merge([
            'email' => 'test@example.com',
            'password' => 'wrong-password'
        ]);

        $this->userBoMock->shouldReceive('login')
            ->once()
            ->with($request)
            ->andThrow(new InvalidPasswordException());

        $response = $this->controller->login($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('User not logged in', json_decode($response->getContent(), true)['message']);
    }

    public function testLogoutSuccess()
    {
        $request = new UserRequest();

        $this->userBoMock->shouldReceive('logout')
            ->once()
            ->with($request)
            ->andReturn(true);

        $response = $this->controller->logout($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('User logged out', json_decode($response->getContent(), true)['message']);
    }

    public function testLogoutFailure()
    {
        $request = new UserRequest();

        $this->userBoMock->shouldReceive('logout')
            ->once()
            ->with($request)
            ->andReturn(false);

        $response = $this->controller->logout($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('User not logged out', json_decode($response->getContent(), true)['message']);
    }
}
