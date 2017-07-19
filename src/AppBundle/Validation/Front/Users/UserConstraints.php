<?php

namespace AppBundle\Validation\Front\Users;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserConstraints extends ConstraintValidator
{
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
    public function validate($data , Constraint $constraint)
    {
        global $kernel;
        $repository = $kernel->getContainer()->get('doctrine.orm.entity_manager')->getRepository('AppBundle:UsersEntity');
        $query = $repository->createQueryBuilder('pk')
            ->where('(pk.email = :account)')
            ->andWhere('pk.password = :password')
            ->setParameters(array('account' => $data['account'], 'password' => $data['password']))
            ->getQuery();

        //dump($query);die();

        $result = $query->getArrayResult();
        if(!empty($result)){
            $this->context->buildViolation("User is invalid.")
                ->atPath('account')
                ->addViolation();
        }
    }
}