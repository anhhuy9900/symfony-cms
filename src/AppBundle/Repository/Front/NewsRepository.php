<?php
namespace AppBundle\Repository\Front;

use AppBundle\Repository\Admin\AdminNewsRepository;


class NewsRepository extends AdminNewsRepository
{
    public function list_data_news_get($limit, $offset){
        $repository = $this->getEntityManager()->getRepository('AppBundle:NewsEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select("pk");
        $query->where('pk.status = 1');
        $query->orderBy("pk.updated_date", "DESC");
        $query->setMaxResults($limit);
        $query->setFirstResult($offset);
        $result = $query->getQuery()->getResult();

        if(!empty($result)){
          return $result;
        }

        return NULL;
    }

    public function total_list_data_news_get($where = array()){
        $repository = $this->getEntityManager()->getRepository('AppBundle:NewsEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select('COUNT(pk.id)');
        $query->where('pk.status = 1');
        $total = $query->getQuery()->getSingleScalarResult();

        return $total;
    }

    public function list_news_get_all(){
        $repository = $this->getEntityManager()->getRepository('AppBundle:NewsEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select("pk");
        $query->where('pk.status = 1');
        $query->orderBy("pk.updated_date", "DESC");
        $query->setMaxResults(9);
        $result = $query->getQuery()->getResult();

        if(!empty($result)){
            return $result;
        }

        return NULL;
    }

    public function get_news_detail($slug){
        $repository = $this->getEntityManager()->getRepository('AppBundle:NewsEntity');
        $query = $repository->createQueryBuilder('pk');
        $query->select("pk");
        $query->where('pk.status = 1');
        $query->andWhere('pk.slug = :slug')->setParameter('slug', $slug);

        $result = $query->getQuery()->getSingleResult();

        if(!empty($result)){
            return $result;
        }

        return NULL;
    }
}