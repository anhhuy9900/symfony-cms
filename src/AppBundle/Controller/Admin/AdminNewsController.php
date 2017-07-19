<?php
namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AdminController;
use MyProject\Proxies\__CG__\stdClass;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Validation\Admin\AdminNewsValidation;

class AdminNewsController extends AdminController
{

    /**
     * Used as constructor
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->data['title'] = 'Admin Manage News';
        $this->data['admin_module_id'] = $this->admincp_service->admin_get_current_module('admincp_news_page')->getID();
    }

    /**
     * @Route("/system/news", name="admincp_news_page")
     */
    public function indexAction(Request $request)
    {

        $key = $request->query->get('key') ? $this->global_helper_service->__xss_clean_string($request->query->get('key')) : '';
        $arr_order = $request->query->get('order') ? $this->global_helper_service->__handle_param_order_in_url($request->query->get('order')) : array('field'=>'id', 'by'=>'DESC');
        $date_range = $request->query->get('date_range') ? $this->global_helper_service->__handle_param_date_range_in_url($request->query->get('date_range')) : '';
        $status = $request->query->get('status') != '' ? (int)$this->global_helper_service->__xss_clean_string($request->query->get('status')) : '';
        $limit = !empty($request->query->get('lm')) ? (int)$request->query->get('lm') : 10;
        $page_offset = !empty($request->query->get('p')) ? (int)$request->query->get('p') : 0;
        $offset = $page_offset > 0 ? ($page_offset - 1) * $limit : $page_offset * $limit;

        $repository = $this->getDoctrine()->getRepository('AppBundle:NewsEntity');
        $total = $repository->_getTotalRecords(array('key' => $key, 'date_range' => $date_range, 'status' => $status));
        $results = $repository->_getListRecords($limit, $offset, array('key' => $key, 'date_range' => $date_range, 'status' => $status), $arr_order);

        if($request->query->get('report')){
            $this->_report_data($results);
        }

        $pagination = $this->global_helper_service->__pagination($total, $page_offset, $limit, 3, $this->generateUrl('admincp_news_page'));
        //dump($results);die();

        $this->data['filter_options'] = $this->filter_options();
        $this->data['results'] = $results;
        $this->data['pagination'] = $pagination;

        return $this->render('@admin/news/list.html.twig', $this->data);
    }

    /**
     * @Route("/system/news/create", name="admincp_news_create_page")
     */
    public function createAction(Request $request)
    {
        $id = 0;
        $handle_data = self::handle_form_data($id, $request);

        if($handle_data['success']){
            $request->getSession()->getFlashBag()->add('message_data', 'Created record success!');
            $url = $this->generateUrl('admincp_news_page');
            return $this->redirect($url, 301);
        }

        $this->data['form'] = $handle_data['form']->createView();
        $this->data['form_errors'] = $handle_data['form_errors'];
        $this->data['fields_value'] = $handle_data['fields_value'];
        $this->data['list_galleries'] = $handle_data['list_galleries'];
        $this->data['list_tags'] = $handle_data['list_tags'];

        return $this->render('@admin/news/edit.html.twig', $this->data);

    }

    /**
     * @Route("/system/news/edit/{id}", name="admincp_news_edit_page")
     */
    public function editAction($id, Request $request)
    {
        $handle_data = self::handle_form_data($id, $request);

        if($handle_data['success']){
            $request->getSession()->getFlashBag()->add('message_data', 'Updated record success!');
            $url = $this->generateUrl('admincp_news_page');
            return $this->redirect($url, 301);
        }

        $this->data['form'] = $handle_data['form']->createView();
        $this->data['form_errors'] = $handle_data['form_errors'];
        $this->data['fields_value'] = $handle_data['fields_value'];
        $this->data['list_galleries'] = $handle_data['list_galleries'];
        $this->data['list_tags'] = $handle_data['list_tags'];

        return $this->render('@admin/news/edit.html.twig', $this->data);
    }

    /**
     * @Route("/system/news/delete/{id}", name="admincp_news_delete_page")
     */
    public function deleteAction($id , Request $request)
    {
        if($id > 0){
            $em = $this->getDoctrine()->getEntityManager();
            $check_exist_record = $em->getRepository('AppBundle:NewsEntity')->find($id);
            if($check_exist_record){
                $em->getRepository('AppBundle:NewsEntity')->_delete_record_DB($id);

                $request->getSession()->getFlashBag()->add('message_data', 'Deleted record success!');
            }

            $url = $this->generateUrl('admincp_news_page');
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
        $result_data = $em->getRepository('AppBundle:NewsEntity')->find($id);

        $fields_value = array(
            'id' => ( $id ? $id : 0 ),
            'category_id' => ( $result_data ? $result_data->getCategoryID() : 0 ),
            'title' => ( $result_data ? $result_data->getTitle() : '' ),
            'image' => ( $result_data ? $result_data->getImage() : '' ),
            'description' => ( $result_data ? $result_data->getDescription() : '' ),
            'content' => ( $result_data ? $result_data->getContent() : '' ),
            'status' => ( $result_data ? $result_data->getStatus() : 0 )
        );

        //Get list categories
        $categories = $em->getRepository('AppBundle:NewsEntity')->_getCategoriesNews();
        $list_categories = $this->global_helper_service->__convert_array_result_selectbox($categories, array('key'=>'id', 'value'=>'title'));

        //Get list galleries
        $list_galleries = $this->global_service->__get_list_galleries($id, 'news');

        //Get list tags
        $list_tags = $em->getRepository('AppBundle:NewsEntity')->_getListTagsNews($id, 'news');

        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            //->setAction($this->generateUrl('admincp_news_edit_page'))
            ->add('id', HiddenType::class, array(
                'data' => $fields_value['id'],
            ))
            ->add('category_id', ChoiceType::class, array(
                'label' => 'Category ID',
                'choices' => $list_categories,
                'data' => $fields_value['category_id']
            ))
            ->add('title', TextType::class, array(
                'label' => 'Title',
                'data' => $fields_value['title']
            ))
            ->add('image', FileType::class, array(
                'label' => 'Image',
                'required' => FALSE
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'Description',
                'data' => $fields_value['description']
            ))
            ->add('content', TextareaType::class, array(
                'label' => 'Content',
                'data' => $fields_value['content']
            ))
            ->add('status', ChoiceType::class, array(
                'label' => 'Status',
                'data' => $fields_value['status'],
                'choices' => array( 0 => 'Unpblish', 1 => 'Publish')
            ))
            ->add('tags', TextType::class, array(
                'label' => 'Tags input',
                'data' =>  '',
                'required' => FALSE
            ))
            ->add('lists_thumb', TextareaType::class, array(
                'data' => (!empty($list_galleries) ? json_encode($list_galleries) : ''),
                'required' => FALSE
            ))
            ->add('lists_del_file', TextareaType::class, array(
                'data' => '',
                'required' => FALSE
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

            $validation = new AdminNewsValidation();
            $validation->title = $data['title'];
            $validation->description = $data['description'];
            $validation->content = $data['content'];

            $errors = $this->get('validator')->validate($validation);
            $form_errors = $this->global_helper_service->getErrorMessages($errors);
            if(!$form_errors){

                //Create Slug
                $data['slug'] = $this->global_helper_service->_createSlug($data['title']);

                //Upload image
                $service = $this->container->get('app.upload_files_service');
                $data['image'] = $service->__upload_file_request($data['image'],'news');

                if($data['id'] > 0){
                    /* Update record */
                    $id = $em->getRepository('AppBundle:NewsEntity')->_update_record_DB($data);
                } else {
                    /* Create new record */
                    $id = $em->getRepository('AppBundle:NewsEntity')->_create_record_DB($data);
                }

                /* handle gallery images */
                //create new files
                if(!empty($data['lists_thumb'])){
                    $files_gallery = json_decode($data['lists_thumb']);
                    foreach ($files_gallery as $key => $value) {
                        $service->__save_files_data($id, 'news', $value->file);
                    }
                }

                //delete new files
                if(!empty($data['lists_del_file'])){
                    $lists_del_file = json_decode($data['lists_del_file']);
                    foreach ($lists_del_file as $key_del_file => $del_file) {
                        $service->__delete_files_data($id, 'news', $del_file->id);
                    }
                }
                /* End handle gallery images */

                /* handle tags */
                $em->getRepository('AppBundle:NewsEntity')->_handle_tags_new($id, 'news', $data['tags']);
                /* End handle tags */

                $success = TRUE;
            }
        }

        $handle_data = array(
            'form' => $form,
            'form_errors' => $form_errors,
            'success' => $success,
            'fields_value' => $fields_value,
            'list_galleries' => $list_galleries,
            'list_tags' => (!empty($list_tags)) ? json_encode($list_tags): ''
        );

        return $handle_data;
    }

    /**
     * Report data into file excel
     */
    private function _report_data($arrData = array())
    {

        $file_name = 'List-News-Data-' . date('Ymd') . '.xlsx';

        // Create excel file
        $header = array();
        $header[] = 'ID';
        $header[] = 'Title';
        $header[] = 'Description';
        $header[] = 'Content';
        $header[] = 'Status';
        $header[] = 'Created Date';

        $data['headers'] = $header;

        $rows = array();
        if(!empty($arrData)){
            foreach($arrData as $key => $value) {
                $tmp = array();
                $tmp[] = $value->getID();
                $tmp[] = $value->getTitle();
                $tmp[] = $value->getDescription();
                $tmp[] = $value->getContent();
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
