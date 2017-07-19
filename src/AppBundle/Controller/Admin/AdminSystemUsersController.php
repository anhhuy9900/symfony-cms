<?php
namespace AppBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/* import Bundle Custom */
use AppBundle\Controller\AdminController;
use AppBundle\Validation\Admin\AdminSystemUsersValidation;


class AdminSystemUsersController extends AdminController
{

    /**
     * Used as constructor
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->data['title'] = 'Manage System Users';
    }

    /**
     * @Route("/system/system-users", name="admincp_system_users_page")
     */
    public function indexAction(Request $request)
    {
        $key = $request->query->get('key') ? $this->global_helper_service->__xss_clean_string($request->query->get('key')) : '';
        $arr_order = $request->query->get('order') ? $this->global_helper_service->__handle_param_order_in_url($request->query->get('order')) : array('field'=>'id', 'by'=>'DESC');
        $date_range = $request->query->get('date_range') ? $this->global_helper_service->__handle_param_date_range_in_url($request->query->get('date_range')) : array();

        $limit = $request->query->get('lm') ? (int)$request->query->get('lm') : 10;
        $page_offset = $request->query->get('p') ? (int)$request->query->get('p') : 0;
        $offset = $page_offset > 0 ? ($page_offset - 1) * $limit : $page_offset * $limit;

    	$repository = $this->getDoctrine()->getRepository('AppBundle:SystemUsersEntity');
        $total = $repository->_getTotalRecords($key);
        $results = $repository->_getListRecords($limit, $offset, array('key' => $key, 'date_range' => $date_range), $arr_order);

        if($request->query->get('report')){
            $this->_report_data($results);
        }

        $pagination = $this->global_helper_service->__pagination($total, $page_offset, $limit, 3, $this->generateUrl('admincp_system_users_page'));

        $this->data['filter_options'] = $this->filter_options();
        $this->data['results'] = $results;
        $this->data['pagination'] = $pagination;

        return $this->render('@admin/system-users/list.html.twig', $this->data);
    }

    /**
     * @Route("/system/system-users/create", name="admincp_system_users_create_page")
     */
    public function createAction(Request $request)
    {
        $id = 0;
        $handle_data = self::handle_form_data($id, $request);

        if($handle_data['success']){
            $request->getSession()->getFlashBag()->add('message_data', 'Created record success!');
            $url = $this->generateUrl('admincp_system_users_page');
            return $this->redirect($url, 301);
        }

        $this->data['form'] = $handle_data['form']->createView();
        $this->data['form_errors'] = $handle_data['form_errors'];

        return $this->render('@admin/system-users/edit.html.twig', $this->data);

    }

    /**
     * @Route("/system/system-users/edit/{id}", name="admincp_system_users_edit_page")
     */
    public function editAction($id, Request $request)
    {
        $handle_data = self::handle_form_data($id, $request);

        if($handle_data['success']){
            $request->getSession()->getFlashBag()->add('message_data', 'Updated record success!');
            $url = $this->generateUrl('admincp_system_users_page');
            return $this->redirect($url, 301);
        }

        $this->data['form'] = $handle_data['form']->createView();
        $this->data['form_errors'] = $handle_data['form_errors'];

        return $this->render('@admin/system-users/edit.html.twig', $this->data);
    }

    /**
     * @Route("/system/system-users/delete/{id}", name="admincp_system_users_delete_page")
     */
    public function deleteAction($id , Request $request)
    {
        if($id > 0){
            $em = $this->getDoctrine()->getEntityManager();
            $check_exist_record = $em->getRepository('AppBundle:SystemUsersEntity')->find($id);
            if($check_exist_record){
                $em->getRepository('AppBundle:SystemUsersEntity')->_delete_record_DB($id);

                $request->getSession()->getFlashBag()->add('message_data', 'Deleted record success!');
            }

            $url = $this->generateUrl('admincp_system_users_page');
            return $this->redirect($url, 301);
            exit();
        }

        return $this->render();
    }

    /**
     * This function Handle create vs update data including handle and handle record in database
     */
    private function handle_form_data($id, $request){
        $em = $this->getDoctrine()->getEntityManager();
        $result_data = $em->getRepository('AppBundle:SystemUsersEntity')->find($id);

        $fields_value = array(
            'id' => ( $id ? $id : 0 ),
            'role_id' => ( $result_data ? $result_data->getRoleID() : 0 ),
            'username' => ( $result_data ? $result_data->getUsername() : '' ),
            'email' => ( $result_data ? $result_data->getEmail() : '' ),
            'password' => ( $result_data ? $result_data->getPassword() : 0 ),
            'status' => ( $result_data ? $result_data->getStatus() : 0 )
        );

        $getListRolesUser = $em->getRepository('AppBundle:SystemUsersEntity')->_getListRolesUser();
        $list_roles = $this->global_helper_service->__convert_array_result_selectbox($getListRolesUser, array('key'=>'id', 'value'=>'role_name'));

        $defaultData = array('message' => 'Type your message here');

        $form = $this->createFormBuilder($defaultData)
            //->setAction($this->generateUrl('admincp_system_users_edit_page'))
            ->add('id', HiddenType::class, array(
                'data' => $fields_value['id'],
            ))
            ->add('role_id', ChoiceType::class, array(
                'label' => 'Select Role',
                'choices' => $list_roles,
                'data' => $fields_value['role_id']
            ))
            ->add('username', TextType::class, array(
                'label' => 'UserName',
                'data' => $fields_value['username']
            ))
            ->add('email', TextType::class, array(
                'label' => 'Email',
                'data' => $fields_value['email']
            ))
            ->add('password', PasswordType::class, array(
                'label' => 'Password',
                'data' => $fields_value['password']
            ))
            ->add('status', ChoiceType::class, array(
                'label' => 'Status',
                'data' => $fields_value['status'],
                'choices' => array( 0 => 'Unpblish', 1 => 'Publish')
            ))
            ->add('send', SubmitType::class, array(
                'label' => 'Submit',
            ))
            ->getForm();

        $form->handleRequest($request);

        $form_errors = '';
        $success = FALSE;
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $validation = new AdminSystemUsersValidation();
            $validation->username = $data['username'];
            $validation->email = $data['email'];
            $validation->password = $data['password'];

            $errors = $this->get('validator')->validate($validation);
            $form_errors = $this->global_helper_service->getErrorMessages($errors);
            if(!$form_errors){

                $data['password'] = $this->admin_service->encodePassword('MyPass', $data['password']);
                if($data['id'] > 0){
                    /* Update record */
                    $id = $em->getRepository('AppBundle:SystemUsersEntity')->_update_record_DB($data);
                } else {
                    /* Create new record */
                    $id = $em->getRepository('AppBundle:SystemUsersEntity')->_create_record_DB($data);
                }

                $success = TRUE;
            }
        }

        $handle_data = array(
            'form' => $form,
            'form_errors' => $form_errors,
            'success' => $success
        );

        return $handle_data;
    }

    /**
     * Report data into file excel
     */
    private function _report_data($arrData = array())
    {

        return new Response();
    }

    /*
     * This function used to render form html filter for data
     */
    private function filter_options(){
        $request = new Request();

        $key = $request->query->get('key') ? $this->global_helper_service->__xss_clean_string($request->query->get('key')) : '';
        $date_range = $request->query->get('date_range') ? $request->query->get('date_range') : '';
        $status = $request->query->get('status') != '' ? (int)$this->global_helper_service->__xss_clean_string($request->query->get('status')) : '';

        $array_filters = array();

        $array_filters['key'] =  array(
            'type' => 'input',
            'title' => 'Search',
            'default_value' => $key
        );

        $array_filters['status'] =  array(
            'type' => 'select',
            'title' => 'Status',
            'options' => array(
                '' => 'Choose status',
                0 => 'UnPublish',
                1 => 'Publish',
            ),
            'default_value' => $status
        );

        $array_filters['date_range'] =  array(
            'type' => 'date_picker',
            'title' => 'Date Range',
            'options' => '',
            'default_value' => $date_range
        );

        return $this->admincp_service->handle_element_form_filter($array_filters);
    }
}    