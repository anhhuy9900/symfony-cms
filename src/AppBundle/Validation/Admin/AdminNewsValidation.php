<?php
namespace AppBundle\Validation\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class AdminNewsValidation extends Controller {

    public $title;
    public $description;
    public $content;
    public $image;

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

        $metadata->addPropertyConstraint('description', new Assert\NotNull(
            array(
                'message' => 'Description is not null',
            )
        ));
        $metadata->addPropertyConstraint('description', new Assert\Length(array(
            'min'        => 6,
            'max'        => 500,
            'minMessage' => 'Description must be at least {{ limit }} characters long',
            'maxMessage' => 'Description cannot be longer than {{ limit }} characters',
        )));

        $metadata->addPropertyConstraint('content', new Assert\NotNull(
            array(
                'message' => 'Content is not null',
            )
        ));
        $metadata->addPropertyConstraint('content', new Assert\Length(array(
            'min'        => 6,
            'minMessage' => 'Content must be at least {{ limit }} characters long',
        )));



        $metadata->addConstraint(new Assert\Callback('validate'));
    }

    public function validate(ExecutionContextInterface $context)
    {
        global $kernel;

        $admincp_service = $kernel->getContainer()->get('app.admincp_service');

    }
}