<?php
namespace AppBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;

/* import Bundle Custom */
use AppBundle\Entity\FilesManagedEntity;


class UploadFilesService extends Controller{

    /**
     * Used as constructor
     */

    function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function __get_path_folder_upload(){
        return $this->get('request')->getBasePath() . '/uploads/';
    }

    public function __upload_file_request($file, $type_name){
        if($file){
            $extension = $file->guessExtension(); // getting image extension
            $fileName = $type_name.'_'.rand(11111,99999).time().'.jpg';
            $var_path = $this->__creat_folder_upload($type_name);

            $file->move( $var_path['folder_path'], $fileName);
            $new_file = $var_path['path_url'].$fileName;

            return $new_file;
        }
    }

    public function __creat_folder_upload($folder_name = 'images') {
        $uploadDir = $this->getParameter('upload_dir');
        $path_url = $folder_name.'/'.date('Y').'/'.date('m').'/'.date('d').'/';
        $folder_path = $uploadDir .'/'.$path_url;
        $folder = self::__Newfolder($folder_path);

        $data  = array(
            'folder_path' => $folder_path,
            'path_url' => $path_url,
        );
        return $data;
    }

    public static function __Newfolder($folder) {
        $fs = new Filesystem();
        $arr_folder = explode('/', $folder);
        $fol = '';
        foreach ($arr_folder as $row) {
            if (!empty($row)) {
                $fol.=$row . '/';
                if (!file_exists($fol)) {
                    $fs->mkdir($fol, 0777);
                } else {
                    if ($row != 'static') {
                        $mod = substr(sprintf('%o', fileperms($fol)), -4);
                        if ($mod != 0777) {
                            $fs->mkdir($fol, 0777);
                        }
                    }
                }
            }
        }
    }

    static function __random_file_name($length = 10) {
        $allowed_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $allowed_chars_len = strlen($allowed_chars);

        if($allowed_chars_len == 1) {
            return str_pad('', $length, $allowed_chars);
        } else {
            $result = '';

            while(strlen($result) < $length) {
                $result .= substr($allowed_chars, rand(0, $allowed_chars_len), 1);
            } // while

            return $result;
        }
    }

    /**
     * This function use save file to database
     */
    public function __save_files_data($type_id, $type = 'default', $file = '')
    {

        if($file && file_exists($this->getParameter('upload_dir').'/'.$file)){
            $gallery_name = $type.'_gallery';

            $entity = $this->em->getRepository('AppBundle:FilesManagedEntity');
            $query = $entity->createQueryBuilder('pk');
            $query->select("pk");
            $query->where('pk.type = :type');
            $query->andWhere('pk.type_id = :type_id');
            $query->andWhere('pk.file = :file');
            $query->setParameter('type', $type);
            $query->setParameter('type_id', $type_id);
            $query->setParameter('file', $file);
            $get_file_exists = $query->getQuery()->getResult();

            if(empty($get_file_exists)) {
                $file_gallery = $this->__creat_folder_upload($gallery_name);
                $file_gallery_name = self::__random_file_name(15).'_'.rand(11111,99999).time().'.jpg';
                $newfile = $file_gallery['path_url'].$file_gallery_name;

                copy($this->getParameter('upload_dir').'/'.$file, $file_gallery['folder_path'].$file_gallery_name);
                unlink($this->getParameter('upload_dir').'/'.$file);

                //Create file in database
                $create = new FilesManagedEntity();
                $create->setTypeID($type_id);
                $create->setType($type);
                $create->setFile($newfile);
                $create->setStatus(1);
                $create->setCreated_Date(time());
                $this->em->persist($create);
                $this->em->flush();

                return TRUE;

            }
        }

        return FALSE;
    }

    /**
     * This function use delete file to database
     */
    public function __delete_files_data($type_id, $type = 'default', $file_id = 0){

        if(!empty($file_id)){

            $entity = $this->em->getRepository('AppBundle:FilesManagedEntity');
            $query = $entity->createQueryBuilder('pk');
            $query->select("pk");
            $query->where('pk.type = :type');
            $query->andWhere('pk.type_id = :type_id');
            $query->andWhere('pk.id = :id');
            $query->setParameter('type', $type);
            $query->setParameter('type_id', $type_id);
            $query->setParameter('id', $file_id);
            $get_file = $query->getQuery()->getSingleResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            if(!empty($get_file)) {
                $get_file = (object)$get_file;
                if(file_exists($this->getParameter('upload_dir').'/'.$get_file->file)){
                    unlink($this->getParameter('upload_dir').'/'.$get_file->file);
                }
                $entity_delete = $entity->findOneBy(array('id' => $file_id));
                $this->em->remove($entity_delete);
                $this->em->flush();

                return TRUE;
            }
        }

        return FALSE;

    }
}