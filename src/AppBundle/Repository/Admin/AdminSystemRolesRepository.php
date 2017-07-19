<?php
namespace AppBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use AppBundle\Entity\SystemRolesEntity;


/**
 * @ORM\Table(name="system_roles")
 * @ORM\Entity(repositoryClass="AdminSystemRolesRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AdminSystemRolesRepository extends EntityRepository
{

    public function _create_record_DB($data)
    {
        $entity = new SystemRolesEntity();
        $entity->setRole_Name($data['role_name']);
        $entity->setRole_Type($data['role_type']);
        $entity->setRole_Status($data['role_status']);
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
        $entity = $em->getRepository('AppBundle:SystemRolesEntity')->find($data['id']);

        $entity->setRole_Name($data['role_name']);
        $entity->setRole_Type($data['role_type']);
        $entity->setRole_Status($data['role_status']);
        $entity->setUpdated_Date(time());

        $em->flush();

        return $entity->getID();
    }

    public function _delete_record_DB($id){
        $em = $this->getEntityManager();
        $entity = $em->getRepository('AppBundle:SystemRolesEntity')->findOneBy(array('id'=>$id));
        $em->remove($entity);
        $em->flush();
    }

    public function _getListRecords($offset, $limit, $where = array(), $order = array('field'=>'id', 'by'=>'DESC')){
        $repository = $this->getEntityManager()->getRepository('AppBundle:SystemRolesEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select("pk");
        $query->where('pk.id > 0');
        if(!empty($where)){
            if(isset($where['key']) && $where['key']) {
                $query->andWhere('pk.role_name LIKE :key')->setParameter('key', '%'.$where['key'].'%');
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
        $repository = $this->getEntityManager()->getRepository('AppBundle:SystemRolesEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select('COUNT(pk.id)');
        if($key){
            $query->where('pk.role_name LIKE :key')->setParameter('key', '%'.$key.'%');
        }
        $total = $query->getQuery()->getSingleScalarResult();

        return $total;
    }

    public function _getListModules(){
        $repository = $this->getEntityManager()->getRepository('AppBundle:AdminSystemModulesEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select('pk.id, pk.module_name');
        $query->where('pk.module_status = 1');
        $query->andwhere('pk.parent_id > 0');
        $result = $query->getQuery()->getResult();

        return $result;
    }

}