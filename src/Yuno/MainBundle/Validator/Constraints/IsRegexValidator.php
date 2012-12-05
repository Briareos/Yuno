<?php

namespace Yuno\MainBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

class IsRegexValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        if (@preg_match($value, '') === false) {
            $this->context->addViolation($constraint->message);
        }
    }


}