<?php
namespace AppBundle\Validation\Admin;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AdminCommonValidation
{
    public function valid_password_not_match($value, Constraint $constraint)
    {
        $value = md5($value);
        $doctrime = $this->getDoctrine()->getManager();
        $query = $doctrime->createQuery(
            "SELECT password
            FROM  system_users pk
            WHERE pk.password > :password"
        )->setParameter('password', $value);

        $result = $query->getResult();
        if(empty($result)){
            $this->context->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}