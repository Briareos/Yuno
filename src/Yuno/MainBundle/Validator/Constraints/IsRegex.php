<?php

namespace Yuno\MainBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class IsRegex extends Constraint
{
    public $message = "Specified value is not a valid regex.";
}