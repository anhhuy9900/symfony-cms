<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;


/**
 * @ORM\Entity
 * @ORM\Table(name="system_modules")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Admin\AdminSystemModulesRepository")
 */
class SystemModulesEntity {
    /**
     * @ORM\Column(type="integer", length=5)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=5)
     */
    private $parent_id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $module_name;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $module_alias;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $module_type;

    /**
     * @ORM\Column(type="smallint", length=1)
     */
    private $module_permission;

    /**
     * @ORM\Column(type="smallint", length=1)
     */
    private $module_status;

    /**
     * @ORM\Column(type="integer", length=10)
     */
    private $module_order;

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

    public function setParentID($parent_id) {
        $this->parent_id = $parent_id;
    }

    public function getParentID() {
        return $this->parent_id;
    }

    public function setModule_Name($module_name) {
        $this->module_name = $module_name;
    }

    public function getModule_Name() {
        return $this->module_name;
    }

    public function setModule_Alias($module_alias) {
        $this->module_alias = $module_alias;
    }

    public function getModule_Alias() {
        return $this->module_alias;
    }

    public function setModule_Type($module_type) {
        $this->module_type = $module_type;
    }

    public function getModule_Type() {
        return $this->module_type;
    }

    public function setModule_Permission($module_permission) {
        $this->module_permission = $module_permission;
    }

    public function getModule_Permission() {
        return $this->module_permission;
    }

    public function setModule_Status($module_status) {
        $this->module_status = $module_status;
    }

    public function getModule_Status() {
        return $this->module_status;
    }

    public function setModule_Order($module_order) {
        $this->module_order = $module_order;
    }

    public function getModule_Order() {
        return $this->module_order;
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