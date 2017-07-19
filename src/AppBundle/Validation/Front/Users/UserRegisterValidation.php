<?php
namespace AppBundle\Validation\Front\Users;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserRegisterValidation extends Controller
{
    public $fullname;
    public $email;
    public $password;
    public $confirm_password;
    public $phone;
    public $gender;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {

        //Validation fullname
        $metadata->addPropertyConstraint('fullname', new Assert\NotBlank(
            array(
                'message' => 'Fullname should not be blank',
            )
        ));

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

        //Validation password
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

        //Validation confirm password
        $metadata->addPropertyConstraint('confirm_password', new Assert\NotBlank(
            array(
                'message' => 'Password should not be blank',
            )
        ));

        //Validation Phone Number
        $metadata->addPropertyConstraint('phone', new Assert\NotBlank(
            array(
                'message' => 'Phone number should not be blank',
            )
        ));
        $metadata->addPropertyConstraint('phone', new Assert\Length(array(
            'min'        => 6,
            'max'        => 50,
            'minMessage' => 'Phone number must be at least {{ limit }} characters long',
            'maxMessage' => 'Phone number cannot be longer than {{ limit }} characters',
        )));
        $metadata->addPropertyConstraint('phone', new Assert\Type(array(
            'type'    => 'integer',
            'message' => 'Your phone must be integer.',
        )));

        //Validation Gender
        $metadata->addPropertyConstraint('gender', new Assert\Type(array(
            'type'    => 'integer',
            'message' => 'The value {{ value }} is not a valid {{ type }}.',
        )));

        $metadata->addConstraint(new Assert\Callback('validate'));
    }

    public function validate(ExecutionContextInterface $context)
    {

        if(!$this->validation_confirm_password($this->password, $this->confirm_password)){
            $context->buildViolation("Confirm password don't match your password")
                ->atPath('confirm_password')
                ->addViolation();
        }
        if(!$this->validation_email_exists($this->email)){
            $context->buildViolation("Email is existing in system. Please input email other.")
                ->atPath('email')
                ->addViolation();
        }
    }

    private function validation_confirm_password($password, $confirm_password){
        if($password && $confirm_password){
            if($password != $confirm_password){
                return FALSE;
            }
        }

        return TRUE;
    }

    private function validation_email_exists($email)
    {
        global $kernel;
        $repository = $kernel->getContainer()->get('doctrine.orm.entity_manager')->getRepository('AppBundle:UsersEntity');
        $query = $repository->createQueryBuilder('pk')
            ->where('pk.email LIKE :email')
            ->setParameters(array('email' => $email))
            ->getQuery();

        $result = $query->getArrayResult();
        if(!empty($result)){
            return FALSE;
        }

        return TRUE;
    }
}