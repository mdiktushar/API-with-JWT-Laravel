<?php

namespace App\Services\Auth;

use App\Exceptions\OTPExpiredException;
use App\Exceptions\OTPMismatchException;
use App\Exceptions\UserAlreadyVarifiedException;
use App\Jobs\SendOTPEmail;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OTPService
{
    /**
     * Sends an OTP (One-Time Password) to the specified user for the given operation.
     *
     * This method first retrieves the user based on their email address, deletes any existing OTPs 
     * for the specified operation, and then generates and sends a new OTP to the user. 
     * The OTP is associated with a specific operation (e.g., login, password reset).
     * If any error occurs during the process, an error is logged, and the exception is rethrown.
     *
     * @param string $email The email address of the user to whom the OTP will be sent.
     * @param string $operation The operation for which the OTP is being generated (e.g., 'login', 'password_reset').
     *
     * @return void No value is returned from this method.
     *
     * @throws Exception If there is an error retrieving the user, deleting old OTPs, or sending the new OTP.
     */
    public function otpSend($email, $operation): void
    {

        try {
            $user = User::whereEmail($email)->first();
            $user->otps()->whereOperation($operation)->delete();
            $this->otp($user, $operation);

        } catch (Exception $e) {
            Log::error('OTPService::otpSend -> ' . $e->getMessage());
            throw $e;
        }
    }



    /**
     * Validate and match the provided OTP (One-Time Password) for a given operation.
     *
     * This method performs the following operations:
     * - Verifies that the provided OTP matches the one stored for the user based on the given email and operation.
     * - Checks if the OTP has expired (1 minute window).
     * - If the OTP is valid, the corresponding operation is performed (e.g., email verification).
     * - In the case of successful email verification, a new authentication token is generated for the user.
     * 
     * The method ensures that the OTP is invalidated after it is used, and any relevant changes (like email verification) are persisted in the database.
     *
     * @param string $email The user's email address.
     * @param string $operation The operation for which the OTP was generated (e.g., 'email').
     * @param string $otp The OTP that the user has provided.
     *
     * @return string|null The generated authentication token if the OTP is valid and email is verified. 
     *                     Returns null if the operation does not involve generating a token.
     *
     * @throws \App\Exceptions\UserAlreadyVarifiedException If the user's email has already been verified.
     * @throws \App\Exceptions\OTPMismatchException If the provided OTP does not match the stored OTP.
     * @throws \App\Exceptions\OTPExpiredException If the provided OTP has expired.
     * @throws \Exception If any other unexpected error occurs.
     */
    public function otpMatch($email, $operation, $otp): string|null
    {
        try {
            $user = User::whereEmail($email)->first();

            if ($user->email_verified_at) {
                throw new UserAlreadyVarifiedException();
            }

            $userOTP = $user->otps()->whereOperation($operation)->whereStatus(true)->first();

            if (!$userOTP || (int) $otp != $userOTP->number) {
                throw new OTPMismatchException();
            }

            if ($userOTP->created_at->diffInMinutes(now()) > 1) {
                throw new OTPExpiredException();
            }

            DB::beginTransaction();

            // Invalidate the OTP
            $userOTP->status = false;
            $userOTP->save();

            // Perform operation-specific logic
            if ($operation === 'email') {
                $user->email_verified_at = now();
                $user->save();

                $authService = new AuthService();
                $token = $authService->login(['email' => $user->email]);
                DB::commit();
                return $token;
            }
            DB::commit();
            return null;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('OTPService::otpMatch -> ' . $e->getMessage());
            throw $e;
        }
    }




    /**
     * Generates and sends a One-Time Password (OTP) for a specific user and operation.
     *
     * This method generates a random 6-digit OTP and associates it with the provided user and operation. 
     * The OTP is saved in the database, and then an email containing the OTP is dispatched to the user 
     * using the `SendOTPEmail` job. If any error occurs during the process, the exception is logged and 
     * rethrown.
     *
     * @param User $user The user to whom the OTP will be generated and sent.
     * @param string $operation The operation for which the OTP is generated (e.g., 'email' for email verification).
     *
     * @return void No value is returned from this method.
     *
     * @throws Exception If there is an error generating or saving the OTP, or dispatching the email.
     */
    public function otp($user, $operation): void
    {
        try {
            $otp = mt_rand(111111, 999999);
            $user->otps()->create([
                'operation' => $operation,
                'number' => $otp,
            ]);

            SendOTPEmail::dispatch($user, $otp);
        } catch (Exception $e) {
            Log::error('OTPService::otpMatch -> ' . $e->getMessage());
            throw $e;
        }
    }
}