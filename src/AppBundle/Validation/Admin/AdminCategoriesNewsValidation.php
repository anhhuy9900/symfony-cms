<?php
namespace AppBundle\Validation\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class AdminCategoriesNewsValidation extends Controller {

    public $title;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {

        $metadata->addPropertyConstraint('title', new Assert\NotNull(
            array(
                'message' => 'Title is not null',
            )
        ));
        $metadata->addPropertyConstraint('title', new Assert\Length(array(
            'min'        => 6,
            'max'        => 100,
            'minMessage' => 'Title must be at least {{ limit }} characters long',
            'maxMessage' => 'Title cannot be longer than {{ limit }} characters',
        )));


        $metadata->addConstraint(new Assert\Callback('validate'));
    }

    public function validate(ExecutionContextInterface $context)
    {
        global $kernel;

        $admincp_service = $kernel->getContainer()->get('app.admincp_service');

    }
}