<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\AdminLoginEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FrontController extends Controller
{
    public $global_service;
    public $global_helper_service;
    public $data;



    /**
     * Used as constructor
     */
    public function setContainer(ContainerInterface $container = null)
    {
      parent::setContainer($container);
      $this->global_service = $this->container->get('app.global_service');
      $this->global_helper_service = $this->container->get('app.global_helper_service');
      $this->data = array(
          'title' => '',
          'description' => '',
          'add_scripts' => '',

      );
    }

    /**
     * @Route("/error403", name="403_not_found_page")
     */
    public function notfoundAction(){

}
}