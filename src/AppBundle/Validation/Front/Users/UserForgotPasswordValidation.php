<?php
namespace AppBundle\Validation\Front\Users;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use AppBundle\Validation\Front\Users\UserConstraints;

class UserForgotPasswordValidation extends Controller
{

    public $email;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {

        //Validation Email
        $metadata->addPropertyConstraint('email', new Assert\NotBlank(
            array(
                'message' => 'Email should not be blank',
            )
        ));
        $metadata->addPropertyConstraint('email', new Assert\Email(
            array(
                'message' => 'Email invalid',
            )
        ));

        $metadata->addConstraint(new Assert\Callback('validate'));

    }

    public function validate(ExecutionContextInterface $context)
    {
        if($this->email){
            global $kernel;
            $repository = $kernel->getContainer()->get('doctrine.orm.entity_manager')->getRepository('AppBundle:UsersEntity');
            $query = $repository->createQueryBuilder('pk')
                ->where('pk.email = :email')
                ->setParameter('email', $this->email)
                ->getQuery();

            $result = $query->getArrayResult();
            if(empty($result)){
                $context->buildViolation("User not exists in system.")
                    ->atPath('email')
                    ->addViolation();
            }
        }

    }

}