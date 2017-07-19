<?php
namespace AppBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;

class GlobalService extends Controller
{

    private $global_helper_service;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->global_helper_service = $this->container->get('app.global_helper_service');
    }

    function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function __get_list_galleries($type_id, $type){
        $entity = $this->em->getRepository('AppBundle:FilesManagedEntity');
        $query = $entity->createQueryBuilder('pk');
        $query->select("pk");
        $query->where('pk.type = :type');
        $query->andWhere('pk.type_id = :type_id');
        $query->setParameter('type', $type);
        $query->setParameter('type_id', $type_id);
        $result = $query->getQuery()->getArrayResult();

        if(!empty($result)){
            return $this->global_helper_service->__convert_result_to_object($result, 1);
        }

        return NULL;
    }

    public function session_current_user(){
        $session = new Session();

        $user = '';
        if(!empty($session->get('user'))){
            $user = unserialize($session->get('user'));
        }

        return $user;
    }

    public function check_valid_csrf_token($intention, $token){
        if (!$this->isCsrfTokenValid($intention, $token)) {
            return FALSE;
        }

        return TRUE;
    }

}