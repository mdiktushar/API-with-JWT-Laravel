<?php

namespace App\Exceptions;

use Exception;

class OTPExpiredException extends Exception
{
    protected $message = 'OTP has expired.';
    protected $code = 400; // HTTP status code
}
