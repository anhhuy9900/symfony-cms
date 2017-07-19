<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;


/**
 * @ORM\Entity
 * @ORM\Table(name="tags")
 */
class TagsEntity {
    /**
     * @ORM\Column(type="integer", length=11)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=11)
     */
    private $type_id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $tag_name;

    /**
     * @ORM\Column(type="smallint", length=1)
     */
    private $status;

    /**
     * @ORM\Column(type="integer", length=10)
     */
    private $created_date;


    public function setID($id) {
        $this->id = $id;
    }

    public function getID() {
        return $this->id;
    }

    public function setTypeID($type_id) {
        $this->type_id = $type_id;
    }

    public function getTypeID() {
        return $this->type_id;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

    public function setTag_name($tag_name) {
        $this->tag_name = $tag_name;
    }

    public function getTag_Name() {
        return $this->tag_name;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setCreated_Date($created_date) {
        $this->created_date = $created_date;
    }

    public function getCreated_Date() {
        return $this->created_date;
    }

}