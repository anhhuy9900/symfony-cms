<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;


/**
 * @ORM\Entity
 * @ORM\Table(name="system_roles")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Admin\AdminSystemRolesRepository")
 */
class SystemRolesEntity {
    /**
     * @ORM\Column(type="integer", length=5)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $role_name;

    /**
     * @ORM\Column(type="text")
     */
    private $role_type;

    /**
     * @ORM\Column(type="smallint", length=1)
     */
    private $role_status;

    /**
     * @ORM\Column(type="smallint", length=1)
     */
    private $access;

    /**
     * @ORM\Column(type="integer", length=10)
     */
    private $updated_date;

    /**
     * @ORM\Column(type="integer", length=10)
     */
    private $created_date;

    /*public function __construct(Doctrine $doctrine)
    {
        $this->em = $doctrine->getEntityManager();
    }*/

    public function setID($id) {
        $this->id = $id;
    }

    public function getID() {
        return $this->id;
    }

    public function setRole_Name($role_name) {
        $this->role_name = $role_name;
    }

    public function getRole_Name() {
        return $this->role_name;
    }

    public function setRole_Type($role_type) {
        $this->role_type = $role_type;
    }

    public function getRole_Type() {
        return $this->role_type;
    }

    public function setRole_Status($role_status) {
        $this->role_status = $role_status;
    }

    public function getRole_Status() {
        return $this->role_status;
    }

    public function setAccess($access) {
        $this->access = $access;
    }

    public function getAccess() {
        return $this->access;
    }

    public function setUpdated_Date($updated_date) {
        $this->updated_date = $updated_date;
    }

    public function getUpdated_Date() {
        return $this->updated_date;
    }

    public function setCreated_Date($created_date) {
        $this->created_date = $created_date;
    }

    public function getCreated_Date() {
        return $this->created_date;
    }

}