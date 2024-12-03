<?php

namespace App\Services\Auth;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordService
{
    /**
     * Changes the password for the user identified by the provided email.
     *
     * This method retrieves the user based on the given email and updates their password
     * with the new one provided. The password is hashed using the `Hash::make` function
     * before being saved in the database. If the operation is successful, it returns a 
     * success status. In case of any exception during the process, an error is logged 
     * and the exception is rethrown.
     *
     * @param string $email The email address of the user whose password is being changed.
     * @param string $password The new password to set for the user.
     *
     * @return string Returns '200' if the password is successfully changed.
     *
     * @throws Exception If there is an error while fetching the user or updating the password.
     */
    public function changePassword($email, $password)
    {
        try {
            $user = User::where('email', $email)->first();
            $user->update([
                'password' => Hash::make($password),
            ]);
            return '200';
        } catch (Exception $e) {
            Log::error('AuthService::changepassword -> ' . $e->getMessage());
            throw $e;
        }
    }
}