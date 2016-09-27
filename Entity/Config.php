<?php

namespace Naoned\DatabaseConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="container_config")
 * @ORM\Entity(repositoryClass="Naoned\DatabaseConfigBundle\Entity\ConfigRepository")
 */
class Config {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    /**
     * Bidirectional - One-To-Many (INVERSE SIDE)
     * @ORM\OneToMany(targetEntity="Naoned\DatabaseConfigBundle\Entity\Config", mappedBy="parent", cascade={"remove"})
     */
    private $children;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Naoned\DatabaseConfigBundle\Entity\Config", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Naoned\DatabaseConfigBundle\Entity\Extension", inversedBy="configs")
     * @ORM\JoinColumn(name="extension_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $extension;

    /**
     * Constructor
     */
    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Config
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Config
     */
    public function setValue($value) {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Add children
     *
     * @param \Naoned\DatabaseConfigBundle\Entity\Config $children
     * @return Config
     */
    public function addChildren(\Naoned\DatabaseConfigBundle\Entity\Config $children) {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Naoned\DatabaseConfigBundle\Entity\Config $children
     */
    public function removeChildren(\Naoned\DatabaseConfigBundle\Entity\Config $children) {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \Naoned\DatabaseConfigBundle\Entity\Config $parent
     * @return Config
     */
    public function setParent(\Naoned\DatabaseConfigBundle\Entity\Config $parent = null) {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Naoned\DatabaseConfigBundle\Entity\Config
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Set extension
     *
     * @param \Naoned\DatabaseConfigBundle\Entity\Extension $extension
     * @return Config
     */
    public function setExtension(\Naoned\DatabaseConfigBundle\Entity\Extension $extension = null) {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return \Naoned\DatabaseConfigBundle\Entity\Extension
     */
    public function getExtension() {
        return $this->extension;
    }

    public function getConfigTree() {
        if (count($this->children) > 0) {
            $configArray = array();
            foreach ($this->children as $child) {
                $configArray[$child->getName()] = $child->getConfigTree();
            }

            return $configArray;
        }

        if (is_numeric($this->value)) {
            $this->value = intval($this->value);
        }

        return $this->value;
    }

}
