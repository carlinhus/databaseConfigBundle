<?php

namespace Naoned\DatabaseConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 * @ORM\Table(name="container_extension")
 * @ORM\Entity(repositoryClass="Naoned\DatabaseConfigBundle\Entity\ExtensionRepository")
 */
class Extension
{
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
     * @ORM\OneToMany(targetEntity="Naoned\DatabaseConfigBundle\Entity\Config", mappedBy="extension", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $configs;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->configs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param string $name
     * @return Extension
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
     * Add configs
     *
     * @param \Naoned\DatabaseConfigBundle\Entity\Config $config
     * @return Extension
     */
    public function addConfig(\Naoned\DatabaseConfigBundle\Entity\Config $config)
    {
        $config->setExtension($this);

        $this->configs[] = $config;

        return $this;
    }

    /**
     * Remove configs
     *
     * @param \Naoned\DatabaseConfigBundle\Entity\Config $configs
     */
    public function removeConfig(\Naoned\DatabaseConfigBundle\Entity\Config $configs)
    {
        $this->configs->removeElement($configs);
    }

    /**
     * Get configs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * Get root configs
     *
     * @return ArrayCollection
     */
    public function getRootConfigs()
    {
        $configs = new ArrayCollection();

        foreach ($this->configs as $config) {
            if (false == $config->getParent()) {
                $configs[] = $config;
            }
        }

        return $configs;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $configs
     */
    public function setConfigs($configs)
    {
        $this->configs = $configs;
    }
}