<?php

namespace App\Exceptions;

use Exception;

class OTPMismatchException extends Exception
{
    protected $message = 'OTP did not match.';
    protected $code = 400; // HTTP status code
}
