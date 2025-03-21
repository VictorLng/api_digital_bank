<?php
namespace App\Http\Controllers;

use App\Exceptions\InvalidPasswordException;
use App\Exceptions\UserNotFoundException;
use App\Http\Requests\UserRequest;
use App\BO\UserBo;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $userBo;

    public function __construct(UserBo $userBo) {
        $this->userBo = $userBo;
    }

    public function register(UserRequest $request)
    {
        try {
            $userData = $this->userBo->register($request);

            return response()->json([
                'data' => $userData->toArray(),
                'message' => 'User registered'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error registering user: ' . $e->getMessage());
            return response()->json(['message' => 'User not registered: ' . $e->getMessage()], 400);
        }
    }

    public function login(UserRequest $request)
    {
        try {
            $userData = $this->userBo->login($request);

            return response()->json([
                'data' => $userData,
                'message' => 'User logged in'
            ], 200);
        } catch (UserNotFoundException | InvalidPasswordException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        } catch (\Exception $e) {
            Log::error('Error logging in: ' . $e->getMessage());
            return response()->json(['message' => 'User not logged in'], 400);
        }
    }

    public function logout(UserRequest $request)
    {
        try {
            $result = $this->userBo->logout($request);

            if (!$result) {
                return response()->json(['message' => 'User not logged out'], 400);
            }

            return response()->json(['message' => 'User logged out'], 200);
        } catch (\Exception $e) {
            Log::error('Error logging out: ' . $e->getMessage());
            return response()->json(['message' => 'User not logged out: ' . $e->getMessage()], 400);
        }
    }
}
