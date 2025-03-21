<?php
  namespace App\Http\Controllers;

  use App\Http\Requests\UserRequest;
  use App\BO\UserBo;

  class UserController extends Controller
  {
      protected $userBo;

      public function __construct(UserBo $userBo) {
          $this->userBo = $userBo;
      }

      public function register(UserRequest $request)
      {

          $return = $this->userBo->register($request);

          if (!$return) {
              return response()->json(['message' => 'User not registered'], 400);
          }

          return response()->json([
              'data' => $return,
              'message' => 'User registered'
          ], 200);
      }

      public function login(UserRequest $request)
      {
          $return = $this->userBo->login($request);
          if (!$return) {
              return response()->json(['message' => 'User not logged in'], 400);
          }

          return response()->json([
              'data' => $return,
              'message' => 'User logged in'
          ], 200);
      }

      public function logout(UserRequest $request)
      {
          $return = $this->userBo->logout($request);

          if (!$return) {
              return response()->json(['message' => 'User not logged out'], 400);
          }

          return response()->json(['message' => 'User logged out'], 200);
      }
  }
