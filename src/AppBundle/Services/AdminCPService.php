<?php
namespace AppBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class AdminCPService extends Controller{

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

    function admin_checkValidUser($username, $password)
    {
        $password = $this->encodePassword('MyPass', $password);
        $repository = $this->em->getRepository('AppBundle:SystemUsersEntity');
        $result = $repository->findOneBy(
            array('username' => $username, 'password' => $password, 'status' => 1 )
        );
        if($result) {
            return $result;
        }

        return NULL;
    }

    function admin_getUserByToken($user_token)
    {

        $repository = $this->em->getRepository('AppBundle:SystemUsersEntity');
        // $query = $repository->createQueryBuilder('pk')
        //     ->where('pk.user_token LIKE :user_token')
        //     ->andwhere('pk.status = 1')
        //     ->setParameters(array('user_token' => $user_token))
        //     ->getQuery();
        // $result = $query->getArrayResult();
        $result = $repository->findOneBy(
            array('user_token' => $user_token, 'status' => 1 )
        );
        if($result) {
            return $result;
        }

        return NULL;
    }

    function admin_get_current_user_login(){
        $session = new Session(new PhpBridgeSessionStorage());
        if(!empty($session->get('userad_authentication'))) {
            $session_user = $session->get('userad_authentication');

            //get current user
            $get_user = $this->admin_getUserByToken($session_user['token']);

            return $get_user;
        }
        return NULL;
    }


    function admin_onAuthentication($user_data, $remember){
        $session = new Session(new PhpBridgeSessionStorage());
        $session->start();

        $firewall = 'secured_userad';
        $token = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));

        $user = array(
            'username' => $user_data->getUsername(),
            'token' => $user_data->getUser_Token(),
            'ad_token' => $token,
        );

        $session->set('security_'.$firewall, serialize($token));
        $session->set('userad_authentication', $user);
        $session->save();

        //set cookie for remmeber me
        if($remember){
            $response = new Response();
            $cookie = new Cookie('remember_me', 1, time()+86400);
            $response->headers->setCookie($cookie);
            $response->sendHeaders();
        }
    }

    function admin_UserSessionLogin(){
        $session = new Session(new PhpBridgeSessionStorage());
        $is_login = FALSE;

        $cookies = $this->get('request')->cookies;
        if ($cookies->has('remember_me') && $cookies->get('remember_me')) {
            $is_login = TRUE;
        } 
        else {
            if(!empty($session->get('security_secured_userad'))){
                $is_login = TRUE;
            }
        }
        return $is_login;
    }

    function admin_CheckValidLogin(){
        if($this->admin_UserSessionLogin()){
            return TRUE;
        }
        return FALSE;

    }

    function admin_UserAdminInfo(){
        $session = new Session();
        $user = $session->get('userad_authentication');
        return $user;
    }

    public function encodePassword($raw, $salt)
    {
        return hash('sha256', $salt . $raw); // Custom function for encrypt
    }

    public function _lists_modules_left_theme($parent_id){
        //get current url
        $route = $this->get("router")->match($this->getRequest()->getPathInfo());

        $current_user = $this->admin_get_current_user_login();
        $repository = $this->em->getRepository('AppBundle:SystemModulesEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select("pk.id, pk.module_name, pk.module_alias");
        $query->where('pk.module_status = 1');
        $query->andWhere('pk.parent_id = :parent_id')->setParameter('parent_id', $parent_id);
        //Just only super admin enough permission access for all modules
        if($current_user->getPermission_limit() == 0){
            $query->andWhere('pk.module_permission = 0');
        }
        $query->orderBy("pk.module_order", 'ASC');
        $results = $query->getQuery()->getResult();

        $html = '';
        if(!empty($results)){
            $html .= '<ul class="submenu">';
            foreach($results as $value){
                $html_menu = $this->_lists_modules_left_theme($value['id']);
                $url_redirect = $value['module_alias'] ? $this->generateUrl($value['module_alias']) : '#';
                $class_active = $route['_route']==$value['module_alias'] ? ' class="active leftmenu-child-active"' : '';
                $html .='<li'.$class_active.'>';
                    $html .='<a href="' .$url_redirect. '"' .($html_menu ? 'class="dropdown-toggle"' : ''). '>';
                        $html .='<i class="menu-icon fa fa-caret-right"></i>';
                        $html .= $value['module_name'];
                    $html .='</a>';
                    $html .='<b class="arrow ' .($html_menu ? 'fa fa-angle-down' : ''). '"></b>';

                    $html .= $html_menu;

                $html .='</li>';

            }
            $html .= '</ul>';
        }

        return $html;
    }

    public function admin_check_roles_user($module_id, $role_type){
        $valid = FALSE;
        $get_user = $this->admin_get_current_user_login();
        if($get_user){
            $repository = $this->em->getRepository('AppBundle:SystemUsersEntity');
            $query = $repository->createQueryBuilder('pk');
            $query->select("fk.role_type");
            $query->leftJoin("AppBundle:SystemRolesEntity", "fk", "WITH", "pk.role_id=fk.id");
            $query->where('pk.id = :id')->setParameter('id', $get_user->getID());
            $query->andwhere('pk.status = 1');
            $result = $query->getQuery()->getArrayResult(\Doctrine\ORM\Query::HYDRATE_SCALAR);

            $result_role_type = unserialize($result[0]['role_type']);
            if(!empty($result_role_type[$module_id])){
                switch($role_type){
                    case 'view':
                        if($result_role_type[$module_id][$role_type]){
                            $valid = TRUE;
                        }
                        break;
                    case 'add':
                        if($result_role_type[$module_id][$role_type]){
                            $valid = TRUE;
                        }
                        break;
                    case 'edit':
                        if($result_role_type[$module_id][$role_type]){
                            $valid = TRUE;
                        }
                        break;
                    case 'delete':
                        if($result_role_type[$module_id][$role_type]){
                            $valid = TRUE;
                        }
                        break;
                    default:
                        break;
                }
            }

            if($get_user->getPermission_limit() == 1){
                $valid = TRUE;
            }
        }

        return $valid;

    }

    public static function __admin_random_token($length = 16)
    {
        if (function_exists('openssl_random_pseudo_bytes'))
        {
            $bytes = openssl_random_pseudo_bytes($length * 2);

            if ($bytes === false)
            {
                // throw exception that unable to create random token
            }

            return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
        }

        return NULL;
    }

    public function admin_get_current_module($module_alias){
        $repository = $this->em->getRepository('AppBundle:SystemModulesEntity');
        $result = $repository->findOneBy(array('module_alias' => $module_alias));
        if($result) {
            return $result;
        }
        return NULL;
    }


    public static function handle_element_form_filter($array_filters = array()){
        $html = '';
        if(!empty($array_filters)){
            //$html .= '<form name="filter_options" id="filter_options">';
            foreach($array_filters as $key => $value){
                $html .= '<div class="row">';
                $html .= '<div class="col-xs-6 hr4">';
                $html .= '<label for="' .$key. '" class="col-xs-3">' .$value['title']. ' : </label>';
                switch($value['type']){
                    case 'input':
                        $html .= '<div class="input-group dataTables_filter col-xs-8">';
                        $html .= '<input type="input" id="' .$key. '" name="' .$key. '" value="' .$value['default_value']. '" class="form-control input-sm col-xs-5" placeholder="" aria-controls="dynamic-table">';
                        $html .= '</div>';
                        break;
                    case 'select':
                        $html .= '<div class="input-group col-xs-8">';
                        $html .= '<select name="' .$key. '" class="col-xs-8">';
                        foreach($value['options'] as $option_key => $option_value){
                            $selected = $value['default_value'] == $option_key ? 'selectetd="selected"' : '';
                            $html .= '<option value="' .$option_key. '" '.$selected.'>' .$option_value. '</option>';
                        }
                        $html .= '</select>';
                        $html .= '</div>';
                        break;
                    case 'date_picker':
                        $html .= '<div class="input-group col-xs-8">';
                        $html .= '<span class="input-group-addon">';
                        $html .= '<i class="fa fa-calendar bigger-110"></i>';
                        $html .= '</span>';
                        $html .= '<input class="form-control date-picker" type="text" name="' .$key. '" value="' .$value['default_value']. '" id="' .$key. '" data-type="date-picker">';
                        $html .= '</div>';
                        break;
                }
                $html .= '</div>';
                $html .= '</div>';
            }
            //$html .= '</form>';
        }

        return  $html;
    }

}