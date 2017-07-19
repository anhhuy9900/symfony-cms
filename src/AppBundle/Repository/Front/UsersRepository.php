<?php
namespace AppBundle\Repository\Front;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Entity\UsersEntity;


/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="UsersRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class UsersRepository extends EntityRepository
{

    /*
     * This function use to input info user in database
     * @param
     *      - user_data (object)
     *          - fullname (string)
     *          - email (string)
     *          - password (string)
     *          - phone (string)
     *          - address (string)
     *          - gender (int)
     *          - status (int)
     *          - active_code (string)
     */
    public function user_data_insert($data)
    {
        global $kernel;
        $helper = $kernel->getContainer()->get('app.global_helper_service');

        $password = $helper->encode_password('UserPass', $data->password);
        $active_code = md5($data->email.time());

        $entity = new UsersEntity();
        $entity->setFullname($helper->__xss_clean_string($data->fullname));
        $entity->setEmail($helper->__xss_clean_string($data->email));
        $entity->setPassword($password);
        $entity->setPhone($helper->__xss_clean_string($data->phone));
        $entity->setAddress($helper->__xss_clean_string($data->address));
        $entity->setGender($helper->__xss_clean_int($data->gender));
        $entity->setStatus(0);
        $entity->setActive_Code($active_code);
        $entity->setActive_Date(0);
        $entity->setCreated_Date(time());

        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();

        return $entity->getID();
    }

    /*
     * This function use to get user login
     * @param
     *      - user_data (object)
     *          - account (string)
     *          - password (tring)
     */
    function get_current_user($user_data){
        global $kernel;
        $helper = $kernel->getContainer()->get('app.global_helper_service');
        $password = $helper->encode_password('UserPass', $user_data->password);

        $repository = $this->getEntityManager()->getRepository('AppBundle:UsersEntity');
        $query = $repository->createQueryBuilder('pk')
            ->where('pk.email = :account')
            ->andWhere('pk.password = :password')
            ->setParameters(array('account' => $user_data->account, 'password' => $password))
            ->getQuery();

        $result = $query->getSingleResult();
        if(!empty($result)){
            return $result;
        }

        return FALSE;
    }

    /*
     * This function use to get user by the email
     * @param
     *      - email (string)
     */
    function get_current_user_by_email($email){
        $repository = $this->getEntityManager()->getRepository('AppBundle:UsersEntity');
        $query = $repository->createQueryBuilder('pk')
            ->where('pk.email = :email')
            ->setParameters(array('email' => $email))
            ->getQuery();

        $result = $query->getSingleResult();
        if(!empty($result)){
            return $result;
        }

        return FALSE;
    }
}