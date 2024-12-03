<?php

namespace App\Http\Controllers\API\Auth;

use App\Exceptions\OTPExpiredException;
use App\Exceptions\OTPMismatchException;
use App\Exceptions\UserAlreadyVarifiedException;
use App\Http\Requests\Auth\OTPRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OTPMatchRequest;
use App\Services\Auth\OTPService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OTPController extends Controller
{
    use ApiResponse;

    protected OTPService $otpService;

    /**
     * Class constructor that initializes the OTPService dependency.
     *
     * This constructor is used to inject an instance of the OTPService class
     * into the current class. The injected service is stored in a class property
     * and provides access to OTP (One-Time Password) related functionalities,
     * such as generating, sending, and validating OTPs. This allows the class 
     * to interact with OTP-related tasks throughout its lifecycle.
     * 
     * @param OTPService $otpService The OTPService instance responsible for 
     *                               handling OTP operations like sending, 
     *                               validating, and matching OTPs.
     */
    public function __construct(OTPService $otpService)
    {
        $this->otpService = $otpService;
    }




    /**
     * Sends an OTP (One-Time Password) to the provided email address based on the requested operation.
     *
     * This method handles the process of generating and sending an OTP via email. It utilizes
     * the OTPService to perform the sending operation. If the OTP is successfully sent, a 
     * success response is returned. In case of an exception or failure, an error response is 
     * generated with relevant information.
     *
     * @param OTPRequest $request The request containing the email address and operation details.
     *                            - email: The recipient's email address.
     *                            - operation: The type of operation that the OTP is being sent for.
     *
     * @return JsonResponse A JSON response indicating the success or failure of the OTP sending operation.
     *                       - 200: Success with message 'otp sended'.
     *                       - 500: Server error with exception details.
     *
     * @throws Exception If there is an error in sending the OTP.
     */
    public function otpSend(OTPRequest $request): JsonResponse
    {
        try {
            $this->otpService->otpSend($request->email, $request->operation);
            return $this->success(200, 'otp sended', []);
        } catch (Exception $e) {
            Log::error('Send OTP' . $e->getMessage());
            return $this->error(500, 'server error', $e->getMessage());
        }
    }



    /**
     * Verifies the OTP (One-Time Password) provided by the user for a given email and operation.
     *
     * This method checks if the provided OTP matches the one sent to the user's email for 
     * a specific operation. It utilizes the OTPService to perform the matching operation. 
     * Based on the outcome, the appropriate response is returned:
     * - If the OTP is verified successfully, a success response is returned.
     * - If the OTP does not match, is expired, or if the user has already been verified, 
     *   a corresponding error response is returned.
     *
     * @param OTPMatchRequest $request The request containing the email address, operation details,
     *                                 and OTP to be verified.
     *                                 - email: The recipient's email address.
     *                                 - operation: The operation for which the OTP was issued.
     *                                 - otp: The OTP to be verified.
     *
     * @return JsonResponse A JSON response indicating the success or failure of the OTP verification.
     *                       - 200: OTP verified successfully.
     *                       - 400: User is already verified.
     *                       - 400: OTP did not match.
     *                       - 400: OTP has expired.
     *                       - 500: Server error with exception details.
     *
     * @throws UserAlreadyVarifiedException If the user is already verified.
     * @throws OTPMismatchException If the OTP does not match.
     * @throws OTPExpiredException If the OTP has expired.
     * @throws Exception If a general error occurs during the verification process.
     */
    public function otpMatch(OTPMatchRequest $request): JsonResponse
    {
        try {
            $token = $this->otpService->otpMatch($request->email, $request->operation, $request->otp);
            if ($token) {
                return $this->success(200, 'otp verified', ['token' => $token]);
            }
            throw new Exception('Server Error', 500);
        } catch (UserAlreadyVarifiedException $e) {
            Log::error('OTP Match: ' . $e->getMessage());
            return $this->error($e->getCode(), 'User is already verified', $e->getMessage());
        } catch (OTPMismatchException $e) {
            Log::error('OTP Match: ' . $e->getMessage());
            return $this->error($e->getCode(), 'OTP did not match', $e->getMessage());
        } catch (OTPExpiredException $e) {
            Log::error('OTP Match: ' . $e->getMessage());
            return $this->error($e->getCode(), 'OTP Expired', $e->getMessage());
        } catch (Exception $e) {
            Log::error('OTP Match: ' . $e->getMessage());
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }

}
