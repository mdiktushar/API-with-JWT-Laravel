<?php

namespace App\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    protected $message = 'User not found.';
    protected $code = 404; // HTTP status code
}
