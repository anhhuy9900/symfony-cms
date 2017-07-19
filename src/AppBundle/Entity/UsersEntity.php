<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;


/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Front\UsersRepository")
 */
class UsersEntity {
    /**
     * @ORM\Column(type="integer", length=5)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=200)
     */
    private $fullname;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $phone;

    /**
     * @ORM\Column(type="text")
     */
    private $address;

    /**
     * @ORM\Column(type="smallint", length=1)
     */
    private $gender;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $active_code;

    /**
     * @ORM\Column(type="smallint", length=1)
     */
    private $status;

    /**
     * @ORM\Column(type="integer", length=10)
     */
    private $active_date;

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

    public function setFullname($fullname) {
        $this->fullname = $fullname;
    }

    public function getFullname() {
        return $this->fullname;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }
    public function getPhone() {
        return $this->phone;
    }

    public function setAddress($address) {
        $this->address = $address;
    }
    public function getAddress() {
        return $this->address;
    }

    public function setGender($gender) {
        $this->gender = $gender;
    }

    public function getGender() {
        return $this->gender;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setActive_Code($active_code) {
        $this->active_code = $active_code;
    }

    public function getActive_Code() {
        return $this->active_code;
    }

    public function setActive_Date($active_date) {
        $this->active_date = $active_date;
    }

    public function getActive_Date() {
        return $this->active_date;
    }

    public function setCreated_Date($created_date) {
        $this->created_date = $created_date;
    }

    public function getCreated_Date() {
        return $this->created_date;
    }

}