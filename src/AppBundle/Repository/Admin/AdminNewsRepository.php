<?php
namespace AppBundle\Repository\Admin;

use Doctrine\ORM\EntityRepository;

class AdminNewsRepository extends EntityRepository
{

    public function _create_record_DB($data)
    {
        $entity = new NewsEntity();
        $entity->setCategoryID($data['category_id']);
        $entity->setTitle($data['title']);
        $entity->setSlug($data['slug']);
        if($data['image']){
            $entity->setImage($data['image']);
        }
        $entity->setDescription($data['description']);
        $entity->setContent($data['content']);
        $entity->setStatus($data['status']);
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
        $entity = $em->getRepository('AppBundle:NewsEntity')->find($data['id']);

        $entity->setCategoryID($data['category_id']);
        $entity->setTitle($data['title']);
        $entity->setSlug($data['slug']);
        if($data['image']){
            $entity->setImage($data['image']);
        }
        $entity->setDescription($data['description']);
        $entity->setContent($data['content']);
        $entity->setStatus($data['status']);
        $entity->setUpdated_Date(time());

        $em->flush();

        return $entity->getID();
    }

    public function _delete_record_DB($id){
        $em = $this->getEntityManager();
        $entity = $em->getRepository('AppBundle:NewsEntity')->findOneBy(array('id'=>$id));
        $em->remove($entity);
        $em->flush();
    }

    public function _getListRecords($limit, $offset, $where = array(), $order = array('field'=>'id', 'by'=>'DESC')){
        $repository = $this->getEntityManager()->getRepository('AppBundle:NewsEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select("pk.id as id, pk.title as title, pk.image as image, pk.status as status, pk.updated_date as updated_date");
        $query->addSelect("fk.title as category_title");
        $query->leftJoin("AppBundle:CategoriesNewsEntity", "fk", "WITH", "pk.category_id=fk.id");
        $query->where('pk.id > 0');

        if(!empty($where)) {
            if(isset($where['key']) && $where['key']) {
                $query->andWhere('pk.title LIKE :key')->setParameter('key', '%'.$where['key'].'%');
            }
            if(isset($where['date_range']) && $where['date_range']) {
                $query->andWhere('pk.updated_date >= :date_from')->setParameter('date_from', $where['date_range']['from']);
                $query->andWhere('pk.updated_date <= :date_to')->setParameter('date_to', $where['date_range']['to']);
            }

            if(isset($where['status']) && $where['status']) {
                $query->andWhere('pk.status = :status')->setParameter('status', $where['status']);
            }
        }
        $query->orderBy("pk.".$order['field'], $order['by']);
        $query->setMaxResults($limit);
        $query->setFirstResult($offset);
        $result = $query->getQuery()->getResult();

        return $result;
    }

    public function _getTotalRecords($where = array()){
        $repository = $this->getEntityManager()->getRepository('AppBundle:NewsEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select('COUNT(pk.id)');
        if(!empty($where)) {
            if(isset($where['key']) && $where['key']) {
                $query->andWhere('pk.title LIKE :key')->setParameter('key', '%'.$where['key'].'%');
            }
            if(isset($where['date_range']) && $where['date_range']) {
                $query->andWhere('pk.updated_date >= :date_from')->setParameter('date_from', $where['date_range']['from']);
                $query->andWhere('pk.updated_date <= :date_to')->setParameter('date_to', $where['date_range']['to']);
            }

            if(isset($where['status']) && $where['status']) {
                $query->andWhere('pk.status = :status')->setParameter('status', $where['status']);
            }
        }
        $total = $query->getQuery()->getSingleScalarResult();

        return $total;
    }

    public function _getCategoriesNews(){
        $repository = $this->getEntityManager()->getRepository('AppBundle:CategoriesNewsEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select("pk.id, pk.title");
        $results = $query->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        return $results;
    }

    public function _getListTagsNews($type_id, $type = 'default'){
        $repository = $this->getEntityManager()->getRepository('AppBundle:TagsEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select("pk.id, pk.tag_name");
        $query->where('pk.type = :type');
        $query->andWhere('pk.type_id = :type_id');
        $query->setParameter('type', $type);
        $query->setParameter('type_id', $type_id);
        $results = $query->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        return $results;
    }

    /**
     * This function use create and update atgs for each news
     */
    public function _handle_tags_new($type_id, $type = 'default', $tags = ''){

        if($tags){
            $entity = $this->getEntityManager()->getRepository('AppBundle:TagsEntity');
            $list_tags = explode(',', $tags);
            if(!empty($list_tags)){
                foreach($list_tags as $tag){
                    $query = $entity->createQueryBuilder('pk');
                    $query->select("pk");
                    $query->where('pk.type = :type');
                    $query->andWhere('pk.type_id = :type_id');
                    $query->andWhere('pk.tag_name = :tag_name');
                    $query->setParameter('type', $type);
                    $query->setParameter('type_id', $type_id);
                    $query->setParameter('tag_name', $tag);
                    $get_tag_exists = $query->getQuery()->getResult();

                    if(empty($get_tag_exists)) {

                        //Create tag in database
                        $create = new TagsEntity();
                        $create->setTypeID($type_id);
                        $create->setType($type);
                        $create->setTag_Name($tag);
                        $create->setStatus(1);
                        $create->setCreated_Date(time());
                        $em = $this->getEntityManager();
                        $em->persist($create);
                        $em->flush();

                    }
                }
            }

            //delete tag if it isn't exists in list atgs
            $query = $entity->createQueryBuilder('pk');
            $query->select("pk");
            $query->where('pk.type = :type');
            $query->andWhere('pk.type_id = :type_id');
            $query->andWhere($query->expr()->notIn('pk.tag_name', ':list_tags'));
            $query->setParameter('type', $type);
            $query->setParameter('type_id', $type_id);
            $query->setParameter('list_tags', $list_tags);
            $list_tags_delete = $query->getQuery()->getResult();
            if(!empty($list_tags_delete)) {
                foreach ($list_tags_delete as $tag) {
                    $entity_delete = $entity->findOneBy(array('id' => $tag->getID()));
                    $em = $this->getEntityManager();
                    $em->remove($entity_delete);
                    $em->flush();
                }
            }

            return TRUE;
        }

        return FALSE;
    }
}