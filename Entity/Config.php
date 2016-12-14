<?php

namespace Carlinhus\DatabaseConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="container_config")
 * @ORM\Entity(repositoryClass="Carlinhus\DatabaseConfigBundle\Entity\ConfigRepository")
 */
class Config
{

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $value;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * Bidirectional - One-To-Many (INVERSE SIDE)
     * @ORM\OneToMany(targetEntity="Carlinhus\DatabaseConfigBundle\Entity\Config", mappedBy="parent", cascade={"remove"})
     */
    private $children;

    /**
     * @var \Carlinhus\DatabaseConfigBundle\Entity\Config
     *
     * @ORM\ManyToOne(targetEntity="Carlinhus\DatabaseConfigBundle\Entity\Config", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @var \Carlinhus\DatabaseConfigBundle\Entity\Extension
     *
     * @ORM\ManyToOne(targetEntity="Carlinhus\DatabaseConfigBundle\Entity\Extension", inversedBy="configs")
     * @ORM\JoinColumn(name="extension_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $extension;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name the config item name
     * @return Config
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value
     *
     * @param string $value the config item value
     * @return Config
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Add children
     *
     * @param \Carlinhus\DatabaseConfigBundle\Entity\Config $children the child to add
     * @return Config
     */
    public function addChildren(\Carlinhus\DatabaseConfigBundle\Entity\Config $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Carlinhus\DatabaseConfigBundle\Entity\Config $children the child to remove
     *
     * @return void
     */
    public function removeChildren(\Carlinhus\DatabaseConfigBundle\Entity\Config $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \Carlinhus\DatabaseConfigBundle\Entity\Config $parent the parent to set
     * @return Config
     */
    public function setParent(\Carlinhus\DatabaseConfigBundle\Entity\Config $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Carlinhus\DatabaseConfigBundle\Entity\Config
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set extension
     *
     * @param \Carlinhus\DatabaseConfigBundle\Entity\Extension $extension the extension to set
     * @return Config
     */
    public function setExtension(\Carlinhus\DatabaseConfigBundle\Entity\Extension $extension = null)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return \Carlinhus\DatabaseConfigBundle\Entity\Extension
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Return the configuration tree (associative array)
     *
     * @return multitype:array |string
     */
    public function getConfigTree()
    {
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

    /**
     * Get config child by name
     *
     * @param string $configName the config name
     *
     * @return Config|NULL
     */
    public function get($configName)
    {
        foreach ($this->getChildren() as $config) {
            if ($config->getName() == $configName) {
                if ($config->getValue() != '') {
                    return $config->getValue();
                } else {
                    return $config;
                }
            }
        }
        return null;
    }
}
