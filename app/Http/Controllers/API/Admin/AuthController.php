<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use App\Http\Resources\AdminResource;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Admin\Auth\ResetRequest;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Http\Requests\Admin\Auth\ForgotRequest;
use Illuminate\Contracts\Hashing\Hasher as Hash;

class AuthController extends Controller
{
    private $str;
    private $hash;
    private $model;

    public function __construct(Admin $model, Hash $hash, Str $str)
    {
        $this->str = $str;
        $this->hash = $hash;
        $this->model = $model;
    }

    public function login(LoginRequest $request)
    {
        // dd($request);
        // exit();
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|max:60'
        ]);
        $user = $this->model->whereEmail($credentials['email'])->first();
        if (!$user) {
            $error = ValidationException::withMessages([
                'email' => ['The selected email is not valid'],
            ]);
            throw $error;
        }
        $passwordMatched = $this->hash->check($credentials['password'], $user->password);
        if (!$passwordMatched) {
            $error = ValidationException::withMessages([
                'password' => ['The selected password is not valid'],
            ]);
            throw $error;
        }
        $token = $user->createToken('appToken')->accessToken;

        return successResponse((new AdminResource($user))->additional([
            'token' => $token
        ]));
    }

    public function authenticate(Request $request)
    {
        $user = $request->user();

        return successResponse(new AdminResource($user));
    }

    public function logout()
    {
        $user = auth()->user();

        $user->token()->revoke();

        return successResponse(new AdminResource($user));
    }

    public function forgot(ForgotRequest $request)
    {
        $data = $request->validated();

        $this->broker()->sendResetLink($data);
        
        return successResponse();
    }

    public function reset(ResetRequest $request)
    {
        $data = $request->validated();
        $response = $this->broker()->reset(
            $data,
            function ($user, $password) {
                $user->password = $this->hash->make($password);
                $user->save();
            }
        );
        if ($response == Password::PASSWORD_RESET) {
            return successResponse();
        } else {
            throw ValidationException::withMessages([
                'email' => trans($response)
            ]);
        }
    }

    public function broker()
    {
        return Password::broker('admins');
    }
}
