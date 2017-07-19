<?php
namespace AppBundle\Validation\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class AdminSystemModulesValidation extends Controller {

    public $module_name;
    public $module_alias;
    public $module_order;

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {

        $metadata->addPropertyConstraint('module_name', new Assert\NotNull(
            array(
                'message' => 'Module Name is not null',
            )
        ));
        $metadata->addPropertyConstraint('module_name', new Assert\Length(array(
            'min'        => 6,
            'max'        => 100,
            'minMessage' => 'Module Name must be at least {{ limit }} characters long',
            'maxMessage' => 'Module Name cannot be longer than {{ limit }} characters',
        )));

        /*$metadata->addPropertyConstraint('module_alias', new Assert\NotNull(
            array(
                'message' => 'Module Alias is not null',
            )
        ));
        $metadata->addPropertyConstraint('module_alias', new Assert\Length(array(
            'min'        => 6,
            'max'        => 100,
            'minMessage' => 'Module Alias must be at least {{ limit }} characters long',
            'maxMessage' => 'Module Alias cannot be longer than {{ limit }} characters',
        )));*/

        $metadata->addPropertyConstraint('module_order', new Assert\NotNull(
            array(
                'message' => 'Module Order is not null',
            )
        ));
        $metadata->addPropertyConstraint('module_order', new Assert\Type(
        	  array(
        		    'type'    => 'integer',
                'message' => 'Module Order {{ value }} is not a valid {{ type }}',
            )
        ));


        $metadata->addConstraint(new Assert\Callback('validate'));
    }

    public function validate(ExecutionContextInterface $context)
    {   
        global $kernel;

        $admincp_service = $kernel->getContainer()->get('app.admincp_service');
        
    }
}