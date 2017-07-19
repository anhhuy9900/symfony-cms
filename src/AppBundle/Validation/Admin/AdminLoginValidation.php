<?php
namespace AppBundle\Validation\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class AdminLoginValidation extends Controller {

    public $username;
    public $password;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {

        $metadata->addPropertyConstraint('username', new Assert\NotBlank(
            array(
                'message' => 'Username should not be blank',
            )
        ));
        $metadata->addPropertyConstraint('password', new Assert\NotBlank(
            array(
                'message' => 'Password should not be blank',
            )
        ));
        $metadata->addPropertyConstraint('password', new Assert\Length(array(
            'min'        => 6,
            'max'        => 50,
            'minMessage' => 'Password must be at least {{ limit }} characters long',
            'maxMessage' => 'Password cannot be longer than {{ limit }} characters',
        )));


        $metadata->addConstraint(new Assert\Callback('validate'));
    }

    public function validate(ExecutionContextInterface $context)
    {   
        global $kernel;

        $admincp_service = $kernel->getContainer()->get('app.admincp_service');

        if(!$admincp_service->admin_checkValidUser($this->username, $this->password)){
            $context->buildViolation('The username and password invalid')
                ->atPath('username')
                ->addViolation();
        }
        
    }
}