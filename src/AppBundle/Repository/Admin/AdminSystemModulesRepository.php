<?php
namespace AppBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use AppBundle\Entity\SystemModulesEntity;


/**
 * @ORM\Table(name="system_modules")
 * @ORM\Entity(repositoryClass="AdminSystemModulesRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AdminSystemModulesRepository extends EntityRepository
{

    public function _create_record_DB($data)
    {
        $entity = new SystemModulesEntity();
        $entity->setParentID($data['parent_id']);
        $entity->setModule_Name($data['module_name']);
        $entity->setModule_Alias($data['module_alias']);
        $entity->setModule_Status($data['module_status']);
        $entity->setModule_Order($data['module_order']);
        $entity->setUpdated_Date(time());
        $entity->setCreated_Date(time());

        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        return $entity->getID();
    }

    public function _update_record_DB($data)
    {
        $em = $this->getEntityManager();
        $entity = $em->getRepository('AppBundle:SystemModulesEntity')->find($data['id']);

        $entity->setParentID($data['parent_id']);
        $entity->setModule_Name($data['module_name']);
        $entity->setModule_Alias($data['module_alias']);
        $entity->setModule_Status($data['module_status']);
        $entity->setModule_Order((int)$data['module_order']);
        $entity->setUpdated_Date(time());

        $em->flush();

        return $entity->getID();
    }

    public function _delete_record_DB($id){
        $em = $this->getEntityManager();
        $entity = $em->getRepository('AppBundle:SystemModulesEntity')->findOneBy(array('id'=>$id));
        $em->remove($entity);
        $em->flush();
    }

    public function _getListRecords($offset, $limit, $where = array(), $order = array('field'=>'id', 'by'=>'DESC')){
        $repository = $this->getEntityManager()->getRepository('AppBundle:SystemModulesEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select("pk");
        $query->where('pk.id > 0');
        if(!empty($where)){
            if(isset($where['key']) && $where['key']) {
                $query->andWhere('pk.module_name LIKE :key')->setParameter('key', '%'.$where['key'].'%');
            }
            if(isset($where['date_range']) && $where['date_range']) {
                $query->andWhere('pk.updated_date >= :date_from')->setParameter('date_from', $where['date_range']['from']);
                $query->andWhere('pk.updated_date <= :date_to')->setParameter('date_to', $where['date_range']['to']);
            }
        }
        $query->orderBy("pk.".$order['field'], $order['by']);
        $query->setMaxResults($offset);
        $query->setFirstResult($limit);
        $result = $query->getQuery();

        return $result->getResult();
    }

    public function _getTotalRecords($key = ''){
        $repository = $this->getEntityManager()->getRepository('AppBundle:SystemModulesEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select('COUNT(pk.id)');
        if($key){
            $query->where('pk.module_name LIKE :key')->setParameter('key', '%'.$key.'%');
        }
        $total = $query->getQuery()->getSingleScalarResult();

        return $total;
    }

    public function _get_recursive_modules($parent_id, &$arr_menu = array()){

        $repository = $this->getEntityManager()->getRepository('AppBundle:SystemModulesEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select("pk.id, pk.module_name");
        $query->where('pk.module_status = 1');
        $query->andWhere('pk.parent_id = :parent_id')->setParameter('parent_id', $parent_id);
        $results = $query->getQuery()->getResult();
        if(!empty($results)){
            foreach($results as $value){
                $str = '';
                if($parent_id > 0){
                    $str .= '--';
                }
                $value['module_name'] = $str.$value['module_name'];
                $arr_menu[] = $value;
                $this->_get_recursive_modules($value['id'], $arr_menu);
            }
        }

        return $arr_menu;
    }

}