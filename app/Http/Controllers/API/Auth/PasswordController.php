<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordChangeRequest;
use App\Services\Auth\PasswordService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PasswordController extends Controller
{

    use ApiResponse;


    protected PasswordService $passwordService;

    /**
     * Class constructor that initializes the PasswordService dependency.
     *
     * This constructor is used to inject an instance of the PasswordService class
     * into the current class. The injected service is stored in a class property,
     * providing access to password management functionalities. This allows the class
     * to perform tasks such as resetting user passwords, validating password strength, 
     * and other password-related operations.
     * 
     * @param PasswordService $passwordService The PasswordService instance responsible 
     *                                          for handling user password tasks, 
     *                                          including password resets and validation.
     */
    public function __construct(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }





    /**
     * Changes the password for the specified user.
     *
     * This method allows a user to change their password. It leverages the `authService` to 
     * update the password for the given email. If the password change is successful, 
     * a success response is returned. In case of any error during the process, a server error 
     * response is returned with the exception details.
     *
     * @param PasswordChangeRequest $request The request containing the user's email and the new password.
     *                                        - email: The user's email address.
     *                                        - password: The new password to be set.
     *
     * @return JsonResponse A JSON response indicating the result of the password change operation.
     *                       - 200: Password changed successfully.
     *                       - 500: Server error with exception details.
     *
     * @throws Exception If there is an error during the password change process.
     */
    public function changePassword(PasswordChangeRequest $request): JsonResponse
    {
        try {
            $this->passwordService->changePassword($request->email, $request->password);
            return $this->success(200, 'password changed successfully', []);
        } catch (Exception $e) {
            Log::error('Change Password' . $e->getMessage());
            return $this->error(500, 'server error', $e->getMessage());
        }
    }
}
