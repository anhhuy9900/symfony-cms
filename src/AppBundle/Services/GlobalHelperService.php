<?php

namespace AppBundle\Services;

use MyProject\Proxies\__CG__\stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
use PHPExcel;

class GlobalHelperService  extends Controller
{

    function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function cutUnicode($str){
        if(!$str) return false;
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'd'=>'đ',
            'D'=>'Đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
        );
        foreach($unicode as $khongdau=>$codau) {
            $arr=explode("|",$codau);
            $str = str_replace($arr,$khongdau,$str);
        }
        return $str;
    }

    public function _createSlug($string) {
        $string= trim(self::cutUnicode($string));
        $string = strtolower($string);
        //Strip any unwanted characters
        $string = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $string);
        $string = strtolower(trim($string, '-'));
        $string = preg_replace("/[\/_|+ -]+/", '-', $string);
        return $string;
    }

    public function pr($data, $type = 0) {
        print '<pre>';
        print_r($data);
        print '</pre>';
        if ($type != 0) {
            exit();
        }
    }

    public function getErrorMessages($errors) {
        $error_message = '';

        if(count($errors) > 0){
            foreach ($errors as $key => $error) {
                $error_message = $error->getMessage();
                break;
            }
        }

        return $error_message;
    }

    public function __handle_param_order_in_url($value) {
        $arr_order = array();
        $explode = explode('|', $value);
        if(!empty($explode)){
            $arr_order = array(
                'field' => $explode[0],
                'by' => $explode[1]
            );
        }

        return $arr_order;
    }

    public function __handle_param_date_range_in_url($date_range){
        $arr_date_range = array();
        $explode_date = explode('-', $date_range);
        if(!empty($explode_date)){
            $arr_date_range = array(
                'from' => strtotime(date('d-m-Y 00:00:00',strtotime(trim($explode_date[0])))),
                'to' => strtotime(date('d-m-Y 00:00:00',strtotime(trim($explode_date[1])))),
            );
        }

        return $arr_date_range;
    }

    public function __pagination($totalRows, $pageNum = 1, $pageSize, $limit = 3, $current_url = '') {
        settype($totalRows, "int");
        settype($pageSize, "int");
        if ($totalRows <= 0)
            return "";
        $totalPages = ceil($totalRows / $pageSize);
        if ($totalPages <= 1)
            return "";
        $currentPage = $pageNum;
        if ($currentPage <= 0 || $currentPage > $totalPages)
            $currentPage = 1;

        //From to
        $form = $currentPage - $limit;
        $to = $currentPage + $limit;

        //Tinh toan From to
        if ($form <= 0) {
            $form = 1;
            $to = $limit * 2;
        };
        if ($to > $totalPages)
            $to = $totalPages;

        //Tinh toan nut first prev next last
        $first = '';
        $prev = '';
        $next = '';
        $last = '';
        $link = '';

        //Link URL
        $linkUrl = $current_url;

        $get = '';
        $querystring = '';
        if ($_GET) {
            foreach ($_GET as $k => $v) {
                if ($k != 'p')
                    $querystring = $querystring . "&{$k}={$v}";
            }
            $querystring = substr($querystring, 1);
            $get.='?' . $querystring;
        }
        $sep = (!empty($querystring)) ? '&' : '';
        $linkUrl = $linkUrl . '?' . $querystring . $sep . 'p=';

        if ($currentPage > $limit + 2) {
            /** first */
            //$first= "<a href='$linkUrl' class='first'>...</a>&nbsp;";
        }

        /** **** prev ** */
        if ($currentPage > 1) {
            $prevPage = $currentPage - 1;
            $prev = "<li class='paginate_button previous'><a href='$linkUrl$prevPage' class='prev'> Previous </a></li>";
        }

        /** *Next** */
        if ($currentPage < $totalPages) {
            $nextPage = $currentPage + 1;
            $next = "<li class='paginate_button next'><a href='$linkUrl$nextPage' class='next'> Next </a></li>";
        }

        /** *Last** */
        if ($currentPage < $totalPages - 4) {
            $lastPage = $totalPages;
            //$last= "<a href='$linkUrl$lastPage' class='last'>...</a>";
        }

        /* * *Link** */
        for ($i = $form; $i <= $to; $i++) {
            if ($currentPage == $i)
                $link.= "<li class='paginate_button active'><a href='javascript:;'>$i</a></li>";
            else
                $link.= "<li class='paginate_button'><a href='$linkUrl$i'>$i</a></li>";
        }

        $pagination = '<div class="dataTables_paginate paging_simple_numbers pagination" id="dynamic-table_paginate"><ul class="pagination">' . $first . $prev . $link . $next . $last . '</ul></div>';

        return $pagination;
    }


    public function __xss_clean_string($input){
        $output = strip_tags(htmlspecialchars($input));
        return $output;
    }

    public function __xss_clean_int($input){
        $output = intval($input);
        return $output;
    }

    /* EXPORT */
    public static function __createExcelFile($header, $formatExcel = array()) {
        //require_once DRUPAL_ROOT.'/'.'sites/all/libraries/PHPExcel/PHPExcel.php';

        // Create report
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("setCreator")
            ->setLastModifiedBy("setLastModifiedBy")
            ->setTitle("setTitle")
            ->setSubject("setSubject")
            ->setDescription("setDescription")
            ->setKeywords("setKeywords")
            ->setCategory("setCategory");

        // Set default columns
        $styleArray = array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FFFFFF')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $sheet1 = $objPHPExcel->getActiveSheet(0);

        foreach($header as $k=>$v) {

            // Write Header
            $sheet1->setCellValue($k.'1', $v);

            // Set Column Width
            if(isset($formatExcel['c_width'][$k])) {
                $sheet1->getColumnDimension($k)->setWidth($formatExcel['c_width'][$k]);
            } else {
                $sheet1->getColumnDimension($k)->setAutoSize(true);
            }
        }

        $sheet1->getStyle('A1:'.$sheet1->getHighestColumn().'1')->applyFromArray($styleArray);
        // Set cell background color

        /**
         * Set cell background color
         */
        $objPHPExcel->getActiveSheet()->getStyle('A1:'.$sheet1->getHighestColumn().'1')->getFill()
            ->applyFromArray(array('type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => '0489B1')
            ));

        // Set Column Alignment
        $sheet1->getStyle('A1:'.$sheet1->getHighestColumn().'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        return $objPHPExcel;
    }


    /**
     * Download excel file
     * @param $objPHPExcel
     */
    public static function __downloadExcelFile($objPHPExcel, $fileName = 'report.xls') {

        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '333333')
                )
            )
        );

        $objPHPExcel->getActiveSheet()->getStyle(
            'A1:' .
            $objPHPExcel->getActiveSheet()->getHighestColumn() .
            $objPHPExcel->getActiveSheet()->getHighestRow()
        )->applyFromArray($styleArray);

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Report');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Download file
        ob_get_clean();
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$fileName.'"');
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->sendHeaders();

        // Do your stuff here
        $writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        // This line will force the file to download
        $writer->save('php://output');
        exit();

    }

    public function __export_to_excel ($data, $name ='') {
        $_headers = array(
            'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
        ,'AA','AB','AC','AD','AE','AF','AG','AH','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
        );
        $headers = $data['headers'];
        $arrHeaders = array();
        foreach($headers as $key=>$value) {
            $arrHeaders[$_headers[$key]]  = $value;
        }

        $objPHPExcel = self::__createExcelFile($arrHeaders);

        $rowCount = 1;
        $rows = $data['rows'];
        foreach($rows as $item) {
            $rowCount++;
            foreach($item as $key=>$value) {
                $objPHPExcel->getActiveSheet()->setCellValue($_headers[$key].$rowCount, $value);
            }
        }

        self::__downloadExcelFile($objPHPExcel, $name);

    }

    public function __convert_array_result_selectbox($data, $fields = array()){
        $arr_values = array(
            0 => 'Select Value'
        );
        if(!empty($data)){
            foreach($data as $value){
                $value = (object)$value;
                $arr_values[$value->$fields['key']] = $value->$fields['value'];
            }
        }
        return $arr_values;
    }

    public function __convert_array_result($data, $fields = array()){
        $arr_values = array();
        if(!empty($data)){
            foreach($data as $value){
                $value = (object)$value;
                $arr_values[$value->$fields['key']] = $value->$fields['value'];
            }
        }
        return $arr_values;
    }

    public function __convert_result_to_object($data, $check_list_array = 0){

        $values = array();
        if(!empty($data)){
            //$count_records = count($data);
            if($check_list_array == 1){
                foreach($data as $key => $value){
                    $object = (object)$value;
                    $values[] = $object;
                }
                return $values;
            } else {
                $object = (object)$data[0];
                return $object;
            }

        }
        return NULL;
    }

    public function system_add_js($javascripts = array()){
        //$arr_js = array();
        $js_str = '';
        if(!empty($javascripts)){
            foreach($javascripts as $js){
                $js_str .= '<script type="text/javascript" src="' .$js['path']. '"></script>';
            }
        }

        return $js_str;
    }

    public function encode_password($raw, $salt)
    {
        return hash('sha256', $salt . $raw); // Custom function for encrypt
    }


}