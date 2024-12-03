<?php

namespace App\Http\Controllers\API\Auth;

use App\Exceptions\SocialLoginException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SocialLoginRequest;
use App\Services\Auth\SocialLoginService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SocialLoginController extends Controller
{
    use ApiResponse;
    protected $socialLoginService;

    public function __construct(SocialLoginService $socialLoginService){
        $this->socialLoginService = $socialLoginService;
    }

    public function socialLogin(SocialLoginRequest $socialLoginRequest)
    {
        try{
            $validatedData = $socialLoginRequest->validated();
            $token = $this->socialLoginService->handleSocialLogin($validatedData);
            return $this->success(200, 'user login successfull', ['token' => $token]);
        }catch(SocialLoginException $e){
            Log::error('Social login' . $e->getMessage());
            return $this->error($e->getCode(),  $e->getMessage());
        }
        catch(Exception $e) {
            Log::error('Social login' . $e->getMessage());
            return $this->error(500, 'server error', $e->getMessage());
        }
    }
}
