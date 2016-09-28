<?php

namespace Naoned\DatabaseConfigBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Extension entity
 *
 * @ORM\Table(name="container_extension")
 * @ORM\Entity(repositoryClass="Naoned\DatabaseConfigBundle\Entity\ExtensionRepository")
 */
class Extension
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string namespace defines the scope of the extension, allowing multiple configurations for an extension
     */
    private $namespace;

   /**
     * @ORM\OneToMany(targetEntity="Naoned\DatabaseConfigBundle\Entity\Config", mappedBy="extension", cascade={"persist", "remove"}, orphanRemoval=true)
     * @var \Doctrine\Common\Collections\Collection
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
     * @param string $name the extension name
     *
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
     * @param \Naoned\DatabaseConfigBundle\Entity\Config $config the root configuration node attached to the extension
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
     * @param \Naoned\DatabaseConfigBundle\Entity\Config $configs the root node of the configuration to remove
     *
     * @return void
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
     * Set configurations
     *
     * @param \Doctrine\Common\Collections\Collection $configs the collection of configurations to attach the extension
     *
     * @return void
     */
    public function setConfigs($configs)
    {
        $this->configs = $configs;
    }

    /**
     * Get Namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the namespace
     *
     * @param string $namespace the namespace
     *
     * @return Config
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Get a config by its name
     *
     * @param string $configName the config name
     *
     * @return Ambigous <\Doctrine\Common\Collections\ArrayCollection, unknown>|NULL
     */
    public function get($configName)
    {
        foreach ($this->getRootConfigs() as $config) {
            if ($config->getName() == $configName) {
                return $config;
            }
        }
        return null;
    }

    /**
     * An extension to string
     *
     * @return string
     */
    public function __toString()
    {
        $string = $this->name . '(' . $this->namespace . ') [';
        foreach ($this->getRootConfigs() as $config) {
            $string .= print_r($config->getConfigTree(), true);
        }
        $string .= ']';
        return $string;
    }

}