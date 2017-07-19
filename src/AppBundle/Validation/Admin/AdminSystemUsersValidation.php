<?php
namespace AppBundle\Validation\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class AdminSystemUsersValidation extends Controller {

    public $username;
    public $email;
    public $password;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {

        $metadata->addPropertyConstraint('username', new Assert\NotNull(
            array(
                'message' => 'Username is not null',
            )
        ));
        $metadata->addPropertyConstraint('username', new Assert\Length(array(
            'min'        => 6,
            'max'        => 100,
            'minMessage' => 'Username must be at least {{ limit }} characters long',
            'maxMessage' => 'Username cannot be longer than {{ limit }} characters',
        )));

        $metadata->addPropertyConstraint('email', new Assert\NotNull(
            array(
                'message' => 'Email is not null',
            )
        ));
        $metadata->addPropertyConstraint('email', new Assert\Email(
            array(
                'message' => 'Email invalid',
            )
        ));

        $metadata->addPropertyConstraint('password', new Assert\NotNull(
            array(
                'message' => 'Password is not null',
            )
        ));
        $metadata->addPropertyConstraint('password', new Assert\Length(array(
            'min'        => 6,
            'max'        => 100,
            'minMessage' => 'Password must be at least {{ limit }} characters long',
            'maxMessage' => 'Password cannot be longer than {{ limit }} characters',
        )));


        $metadata->addConstraint(new Assert\Callback('validate'));
    }

    public function validate(ExecutionContextInterface $context)
    {   
        global $kernel;

        $admincp_service = $kernel->getContainer()->get('app.admincp_service');
        
    }
}