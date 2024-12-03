<?php

namespace App\Services\Auth;

use App\Exceptions\SocialLoginException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialLoginService
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function handleSocialLogin(array $credentials)
    {
        try {
            $socialUser = Socialite::driver($credentials['provider'])->stateless()->userFromToken($credentials['token']);

            if (!$socialUser) {
                throw new SocialLoginException("Invalid social login token or provider.", 401);
            }

            $user = User::whereEmail($socialUser->getEmail())->first();

            if ($user && !empty($user->deleted_at)) {
                throw new SocialLoginException("Your account has been deleted.", 410);
            }

            if (!$user) {
                $password = Str::random(8);

                $name = $socialUser->getName();
                $nameParts = explode(' ', $name, 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';

                DB::beginTransaction();
                $this->authService->createUser([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $socialUser->getEmail(),,
                    'password' => $password,
                    'address' => null,
                ]);

                $token = $this->authService->login(['email' => $socialUser->getEmail()]);
                DB::commit();
                return $token;
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SocialLoginService::handleSocialLogin-> ' . $e->getMessage());
            throw $e;
        }
    }
}
