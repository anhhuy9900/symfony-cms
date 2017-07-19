<?php
namespace AppBundle\Controller\Admin;

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
use AppBundle\Validation\Admin\AdminCategoriesNewsValidation;

class AdminCategoriesNewsController extends AdminController
{

    /**
     * Used as constructor
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->data['title'] = 'Admin Manage Categories News';
        $this->data['admin_module_id'] = $this->admincp_service->admin_get_current_module('admincp_categories_news_page')->getID();
    }

    /**
     * @Route("/system/categories_news", name="admincp_categories_news_page")
     */
    public function indexAction(Request $request)
    {
        $key = $request->query->get('key') ? $this->global_helper_service->__xss_clean_string($request->query->get('key')) : '';
        $arr_order = $request->query->get('order') ? $this->global_helper_service->__handle_param_order_in_url($request->query->get('order')) : array('field'=>'id', 'by'=>'DESC');
        $date_range = $request->query->get('date_range') ? $this->global_helper_service->__handle_param_date_range_in_url($request->query->get('date_range')) : array();

        $limit = $request->query->get('lm') ? (int)$request->query->get('lm') : 10;
        $page_offset = $request->query->get('p') ? (int)$request->query->get('p') : 0;
        $offset = $page_offset > 0 ? ($page_offset - 1) * $limit : $page_offset * $limit;

        $repository = $this->getDoctrine()->getRepository('AppBundle:CategoriesNewsEntity');
        $total = $repository->_getTotalRecords($key);
        $results = $repository->_getListRecords($limit, $offset, array('key' => $key, 'date_range' => $date_range), $arr_order);

        if($request->query->get('report')){
            $this->_report_data($results);
        }

        $pagination = $this->global_helper_service->__pagination($total, $page_offset, $limit, 3, $this->generateUrl('admincp_categories_news_page'));

        $this->data['filter_options'] = $this->filter_options();
        $this->data['results'] = $results;
        $this->data['pagination'] = $pagination;

        return $this->render('@admin/categories_news/list.html.twig', $this->data);
    }

    /**
     * @Route("/system/categories_news/create", name="admincp_categories_news_create_page")
     */
    public function createAction(Request $request)
    {
        $id = 0;
        $handle_data = self::handle_form_data($id, $request);

        if($handle_data['success']){
            $request->getSession()->getFlashBag()->add('message_data', 'Created record success!');
            $url = $this->generateUrl('admincp_categories_news_page');
            return $this->redirect($url, 301);
        }

        $this->data['form'] = $handle_data['form']->createView();
        $this->data['form_errors'] = $handle_data['form_errors'];
        $this->data['fields_value'] = $handle_data['fields_value'];

        return $this->render('@admin/categories_news/edit.html.twig', $this->data);

    }

    /**
     * @Route("/system/categories_news/edit/{id}", name="admincp_categories_news_edit_page")
     */
    public function editAction($id, Request $request)
    {
        $handle_data = self::handle_form_data($id, $request);

        if($handle_data['success']){
            $request->getSession()->getFlashBag()->add('message_data', 'Updated record success!');
            $url = $this->generateUrl('admincp_categories_news_page');
            return $this->redirect($url, 301);
        }

        $this->data['form'] = $handle_data['form']->createView();
        $this->data['form_errors'] = $handle_data['form_errors'];
        $this->data['fields_value'] = $handle_data['fields_value'];

        return $this->render('@admin/categories_news/edit.html.twig', $this->data);
    }

    /**
     * @Route("/system/categories_news/delete/{id}", name="admincp_categories_news_delete_page")
     */
    public function deleteAction($id , Request $request)
    {
        if($id > 0){
            $em = $this->getDoctrine()->getEntityManager();
            $check_exist_record = $em->getRepository('AppBundle:CategoriesNewsEntity')->find($id);
            if($check_exist_record){
                $em->getRepository('AppBundle:CategoriesNewsEntity')->_delete_record_DB($id);

                $request->getSession()->getFlashBag()->add('message_data', 'Deleted record success!');
            }

            $url = $this->generateUrl('admincp_categories_news_page');
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
        $result_data = $em->getRepository('AppBundle:CategoriesNewsEntity')->find($id);

        $fields_value = array(
            'id' => ( $id ? $id : 0 ),
            'title' => ( $result_data ? $result_data->getTitle() : '' ),
            'status' => ( $result_data ? $result_data->getStatus() : 0 )
        );

        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            //->setAction($this->generateUrl('admincp_categories_news_edit_page'))
            ->add('id', HiddenType::class, array(
                'data' => $fields_value['id'],
            ))
            ->add('title', TextType::class, array(
                'label' => 'Title',
                'data' => $fields_value['title']
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

            $validation = new AdminCategoriesNewsValidation();
            $validation->title = $data['title'];

            $errors = $this->get('validator')->validate($validation);
            $form_errors = $this->global_helper_service->getErrorMessages($errors);

            if(!$form_errors){
                if($data['id'] > 0){
                    /* Update record */
                    $id = $em->getRepository('AppBundle:CategoriesNewsEntity')->_update_record_DB($data);
                } else {
                    /* Create new record */
                    $id = $em->getRepository('AppBundle:CategoriesNewsEntity')->_create_record_DB($data);
                }

                $success = TRUE;
            }
        }

        $handle_data = array(
            'form' => $form,
            'form_errors' => $form_errors,
            'success' => $success,
            'fields_value' => $fields_value
        );

        return $handle_data;
    }

    /**
     * Report data into file excel
     */
    private function _report_data($arrData = array())
    {

        $file_name = 'List-CategoriesNews-Data-' . date('Ymd') . '.xlsx';

        // Create excel file
        $header = array();
        $header[] = 'ID';
        $header[] = 'Title';
        $header[] = 'Status';
        $header[] = 'Created Date';

        $data['headers'] = $header;

        $rows = array();
        if(!empty($arrData)){
            foreach($arrData as $key => $value) {
                $tmp = array();
                $tmp[] = $value->getID();
                $tmp[] = $value->getTitle();
                $tmp[] = $value->getStatus() == 1 ? 'Active' : 'UnActive';
                $tmp[] = date('Y-m-d H:i:s',$value->getCreated_Date());

                $rows[] = $tmp;
            }
            $data['rows'] = $rows;
            $this->global_helper_service->__export_to_excel($data,$file_name);
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
