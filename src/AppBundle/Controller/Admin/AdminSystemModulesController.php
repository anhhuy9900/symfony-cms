<?php
namespace AppBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/* import Bundle Custom */
use AppBundle\Controller\AdminController;
use AppBundle\Validation\Admin\AdminSystemModulesValidation;

class AdminSystemModulesController extends AdminController
{

    /**
     * Used as constructor
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->data['title'] = 'Manage System Modules';
    }

    /**
     * @Route("/system/system-modules", name="admincp_system_modules_page")
     */
    public function indexAction(Request $request)
    {
        $key = $request->query->get('key') ? $this->global_helper_service->__xss_clean_string($request->query->get('key')) : '';
        $arr_order = $request->query->get('order') ? $this->global_helper_service->__handle_param_order_in_url($request->query->get('order')) : array('field'=>'id', 'by'=>'DESC');
        $date_range = $request->query->get('date_range') ? $this->global_helper_service->__handle_param_date_range_in_url($request->query->get('date_range')) : array();

        $limit = $request->query->get('lm') ? (int)$request->query->get('lm') : 10;
        $page_offset = $request->query->get('p') ? (int)$request->query->get('p') : 0;
        $offset = $page_offset > 0 ? ($page_offset - 1) * $limit : $page_offset * $limit;

    	$repository = $this->getDoctrine()->getRepository('AppBundle:SystemModulesEntity');
        $total = $repository->_getTotalRecords($key);
        $results = $repository->_getListRecords($limit, $offset, array('key' => $key, 'date_range' => $date_range), $arr_order);

        if($request->query->get('report')){
            $this->_report_data($results);
        }

        $pagination = $this->global_helper_service->__pagination($total, $page_offset, $limit, 3, $this->generateUrl('admincp_system_modules_page'));

        $this->data['filter_options'] = $this->filter_options();
        $this->data['results'] = $results;
        $this->data['pagination'] = $pagination;

        return $this->render('@admin/system-modules/list.html.twig', $this->data);
    }

    /**
     * @Route("/system/system-modules/create", name="admincp_system_modules_create_page")
     */
    public function createAction(Request $request)
    {
        $id = 0;
        $handle_data = self::handle_form_data($id, $request);

        if($handle_data['success']){
            $request->getSession()->getFlashBag()->add('message_data', 'Created record success!');
            $url = $this->generateUrl('admincp_system_modules_page');
            return $this->redirect($url, 301);
        }

        $this->data['form'] = $handle_data['form']->createView();
        $this->data['form_errors'] = $handle_data['form_errors'];

        return $this->render('@admin/system-modules/edit.html.twig', $this->data);

    }

    /**
     * @Route("/system/system-modules/edit/{id}", name="admincp_system_modules_edit_page")
     */
    public function editAction($id, Request $request)
    {
        $handle_data = self::handle_form_data($id, $request);

        if($handle_data['success']){
            $request->getSession()->getFlashBag()->add('message_data', 'Updated record success!');
            $url = $this->generateUrl('admincp_system_modules_page');
            return $this->redirect($url, 301);
        }

        $this->data['form'] = $handle_data['form']->createView();
        $this->data['form_errors'] = $handle_data['form_errors'];

        return $this->render('@admin/system-modules/edit.html.twig', $this->data);
    }

    /**
     * @Route("/system/system-modules/delete/{id}", name="admincp_system_modules_delete_page")
     */
    public function deleteAction($id , Request $request)
    {
        if($id > 0){
            $em = $this->getDoctrine()->getEntityManager();
            $check_exist_record = $em->getRepository('AppBundle:SystemModulesEntity')->find($id);
            if($check_exist_record){
                $em->getRepository('AppBundle:SystemModulesEntity')->_delete_record_DB($id);

                $request->getSession()->getFlashBag()->add('message_data', 'Deleted record success!');
            }

            $url = $this->generateUrl('admincp_system_modules_page');
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
        $result_data = $em->getRepository('AppBundle:SystemModulesEntity')->find($id);

        $fields_value = array(
            'id' => ( $id ? $id : 0 ),
            'parent_id' => ( $result_data ? $result_data->getParentID() : 0 ),
            'module_name' => ( $result_data ? $result_data->getModule_Name() : '' ),
            'module_alias' => ( $result_data ? $result_data->getModule_Alias() : '' ),
            'module_order' => ( $result_data ? $result_data->getModule_Order() : 0 ),
            'module_status' => ( $result_data ? $result_data->getModule_Status() : 0 )
        );

        $get_recursive_modules = $em->getRepository('AppBundle:SystemModulesEntity')->_get_recursive_modules(0);
        $list_recursive_modules = $this->global_helper_service->__convert_array_result_selectbox($get_recursive_modules, array('key'=>'id', 'value'=>'module_name'));

        $defaultData = array('message' => 'Type your message here');
        $form = $this->createFormBuilder($defaultData)
            //->setAction($this->generateUrl('admincp_system_modules_edit_page'))
            ->add('id', HiddenType::class, array(
                'data' => $fields_value['id'],
            ))
            ->add('parent_id', ChoiceType::class, array(
                'label' => 'Parent',
                'choices' =>$list_recursive_modules,
                'data' => $fields_value['parent_id']
            ))
            ->add('module_name', TextType::class, array(
                'label' => 'Module Name',
                'data' => $fields_value['module_name']
            ))
            ->add('module_alias', TextType::class, array(
                'label' => 'Module Alias',
                'data' => $fields_value['module_alias'],
                'required' => FALSE
            ))
            ->add('module_order', TextType::class, array(
                'label' => 'Module Order',
                'data' => $fields_value['module_order']
            ))
            ->add('module_status', ChoiceType::class, array(
                'label' => 'Module Status',
                'data' => $fields_value['module_status'],
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

            $validation = new AdminSystemModulesValidation();
            $validation->module_name = $data['module_name'];
            $validation->module_alias = $data['module_alias'];
            $validation->module_order = (int)$data['module_order'];

            $errors = $this->get('validator')->validate($validation);
            $form_errors = $this->global_helper_service->getErrorMessages($errors);
            if(!$form_errors){
                if($data['id'] > 0){
                    /* Update record */
                    $id = $em->getRepository('AppBundle:SystemModulesEntity')->_update_record_DB($data);
                } else {
                    /* Create new record */
                    $id = $em->getRepository('AppBundle:SystemModulesEntity')->_create_record_DB($data);
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

        $file_name = 'List-Modules-' . date('Ymd') . '.xlsx';

        // Create excel file
        $header = array();
        $header[] = 'ID';
        $header[] = 'Nodule Name';
        $header[] = 'Module Alias';
        $header[] = 'Module Order';
        $header[] = 'Module Status';
        $header[] = 'Created Date';

        $data['headers'] = $header;

        $rows = array();
        if(!empty($arrData)){
            foreach($arrData as $key => $value) {
                $tmp = array();
                $tmp[] = $value->getID();
                $tmp[] = $value->getModule_Name();
                $tmp[] = $value->getModule_Alias();
                $tmp[] = $value->getModule_Order();
                $tmp[] = $value->getModule_Status() == 1 ? 'Active' : 'UnActive';
                $tmp[] = date('Y-m-d H:i:s',$value->getCreated_Date());

                $rows[] = $tmp;
            }
            $data['rows'] = $rows;
            $this->global_helper_service->__export_to_excel($data, $file_name);
        }
    }

    /*
     * This function used to render form html filter for data
     */
    private function filter_options(){
        $request = new Request();

        $key = $request->query->get('key') ? $this->global_helper_service->__xss_clean_string($request->query->get('key')) : '';
        $date_range = $request->query->get('date_range') ? $request->query->get('date_range') : '';

        $array_filters = array();

        $array_filters['key'] =  array(
            'type' => 'input',
            'title' => 'Search',
            'default_value' => $key
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