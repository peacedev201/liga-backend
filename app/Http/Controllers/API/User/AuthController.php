<?php

namespace App\Http\Controllers\API\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\User\Auth\ResetRequest;
use App\Http\Requests\User\Auth\LoginRequest;
use App\Http\Requests\User\Auth\VerifyRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\User\Auth\ForgotRequest;
use App\Http\Requests\User\Auth\RegisterRequest;
use Illuminate\Contracts\Hashing\Hasher as Hash;
use Illuminate\Auth\Access\AuthorizationException;

class AuthController extends Controller
{
    private $hash;
    private $model;

    public function __construct(User $model, Hash $hash)
    {
        $this->hash = $hash;
        $this->model = $model;
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = $this->hash->make($data['password']);
        $profile = [];
        if (isset($profile['biography']) && $profile['biography']) {
            $profile['biography'] = $data['biography'];
        }
        if ($data['type'] == 'player') {
            $profile['first_name'] = $data['first_name'];
            $profile['last_name'] = $data['last_name'];
            $profile['nick_name'] = $data['nick_name'];
            $profile['postal_code'] = $data['postal_code'];
            $profile['street'] = $data['street'];
            $profile['city'] = $data['city'];
            $profile['country'] = $data['country'];
            $profile['optin_marketing'] = $request->optin_marketing;
        }
        if ($data['type'] == 'club') {
            $profile['name'] = $data['name'];
        }
        $profileable = $this->model->profileable()->getMorphedModel($data['type']);
        $profile = $this->model->profileable()->createModelByType($profileable)->create($profile);

        $user = [
            'email' => $data['email'],
            'password' => $data['password'],
            'profileable_type' => $data['type'],
            'profileable_id' => $profile->id,
        ];
        $user = $this->model->create($user);

        if ($data['type'] == 'player') {
            if (isset($data['club']) && $data['club']) {
                $club = [
                    'club_id' => $data['club']
                ];
                if (isset($data['is_member']) && $data['is_member']) {
                    $profile->membership()->create($club);
                } else {
                    $profile->willing()->create($club);
                }
            }
            $profile->setting()->create([]);
        }

        event(new Registered($user));

        $login = resolve(LoginRequest::class);
        $login->request->add($data);
        return $this->login($login);
    }

    public function login(LoginRequest $request)
    {
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

        return successResponse((new UserResource($user))->additional([
            'token' => $token
        ]));
    }

    public function authenticate()
    {
        $user = auth()->user();

        return successResponse(new UserResource($user));
    }

    public function logout()
    {
        auth()->user()->token()->revoke();

        return successResponse();
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

    public function verify(VerifyRequest $request)
    {
        $data = $request->validated();

        if (!hash_equals((string) $data['key'], (string) $request->user()->getKey())) {
            throw new AuthorizationException;
        }

        if (!hash_equals((string) $data['secret'], sha1($request->user()->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($request->user()->hasVerifiedEmail()) {
            return $this->authenticate();
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $this->authenticate();
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->authenticate();
        }

        $request->user()->sendEmailVerificationNotification();

        return successResponse();
    }

    public function broker()
    {
        return Password::broker('users');
    }
}
