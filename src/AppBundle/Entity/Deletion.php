<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Deletion
 *
 * @ORM\Table(name="deletion")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DeletionRepository")
 */
class Deletion
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="seq", type="integer", unique=true)
     */
    private $seq;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set seq
     *
     * @param integer $seq
     *
     * @return Deletion
     */
    public function setSeq($seq)
    {
        $this->seq = $seq;

        return $this;
    }

    /**
     * Get seq
     *
     * @return int
     */
    public function getSeq()
    {
        return $this->seq;
    }
}

