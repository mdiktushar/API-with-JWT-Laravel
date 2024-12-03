<?php

namespace App\Exceptions;

use Exception;

class UserAlreadyVarifiedException extends Exception
{
    protected $message = 'User is already verified';
    protected $code = 400;
}
