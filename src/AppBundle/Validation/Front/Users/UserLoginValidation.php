<?php
namespace AppBundle\Validation\Front\Users;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use AppBundle\Validation\Front\Users\UserConstraints;

class UserLoginValidation extends Controller
{

    public $account;
    public $password;


    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {

        //Validation account
        $metadata->addPropertyConstraint('account', new Assert\NotBlank(
            array(
                'message' => 'Account should not be blank',
            )
        ));

        //Validation password
        $metadata->addPropertyConstraint('password', new Assert\NotBlank(
            array(
                'message' => 'Password should not be blank',
            )
        ));

        //$metadata->addPropertyConstraint('account', new UserConstraints());
        //dump($metadata->getVa);

        $metadata->addConstraint(new Assert\Callback('validation_user_account'));

    }

    public function validation_user_account(ExecutionContextInterface $context)
    {
        if($this->account && $this->password){
            global $kernel;
            $repository = $kernel->getContainer()->get('doctrine.orm.entity_manager')->getRepository('AppBundle:UsersEntity');
            $query = $repository->createQueryBuilder('pk')
                ->where('(pk.email = :account)')
                ->andWhere('pk.password = :password')
                ->setParameters(array('account' => $this->account, 'password' => $this->password))
                ->getQuery();
            $result = $query->getOneOrNullResult();
            if(empty($result)){
                $context->buildViolation("User is invalid.")
                    ->atPath('account')
                    ->addViolation();
            }
        }

    }

}