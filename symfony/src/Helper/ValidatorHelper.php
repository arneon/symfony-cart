<?php

namespace App\Helper;

use Symfony\Component\Validator\ConstraintViolation;

class ValidatorHelper
{
    public function buildConstraintViolation(string $message, string $propertyPath, string $code)
    {
        return new ConstraintViolation($message,null,[],null,$propertyPath, $code);
    }

}
