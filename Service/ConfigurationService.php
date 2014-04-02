<?php
namespace Unifik\DatabaseConfigBundle\Service;

use Unifik\DatabaseConfigBundle\Entity\ExtensionRepository;
use Unifik\DatabaseConfigBundle\Entity\Config;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Config\Definition\BooleanNode;
use Symfony\Component\Config\Definition\IntegerNode;
use Symfony\Component\Config\Definition\FloatNode;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/** ConfigurationService
 *
 * @package DatabaseConfigBundle
 * @author  Guillaume Petit <guillaume.petit@sword-group.com>
 *
 */
class ConfigurationService
{
    /**
     * @var ExtensionRepository the repository to the extension doctrine entity
     */
    private $extensionRepository;

    /**
     * @var AppKernel
     */
    private $kernel;

    /**
     * @var Logger the logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param \AppKernel          $kernel              the application kernel
     * @param ExtensionRepository $extensionRepository the repository for the extension entity
     * @param Logger              $logger              the logger
     *
     * @return void
     */
    public function __construct(\AppKernel $kernel, ExtensionRepository $extensionRepository, Logger $logger)
    {
        $this->kernel = $kernel;
        $this->extensionRepository = $extensionRepository;
        $this->logger = $logger;
    }

     /**
     * Get configuration value either from database or bundle config
     *
     * @param string $configurationClass the configuration class
     * @param string $namespace          the namespace linked to the extension
     * @param string $key                the configuration key
     *
     * @throws \InvalidArgumentException when the configuration key is not found
     * @return mixed string|boolean the configuration value or false if it doesn't exists
     */
    public function getConfigurationValue($configurationClass, $namespace, $key)
    {
        $value = '';
        $path = explode('.', $key);
        $tree = $this->getContainerConfigurationTree($configurationClass);

        $treeName = $tree->getName();
        $node = $this->getConfigurationFromTree($tree, $path);

        if ($node === null) {
            throw new \InvalidArgumentException('Configuration key not found: ' . $key);
        }

        if (null !== $value = $this->getConfigurationFromDatabase($treeName, $namespace, $path)) {
            if ($node instanceof BooleanNode) {
                $value = (boolean) $value;
            } elseif ($node instanceof IntegerNode) {
                $value = (integer) $value;
            } elseif ($node instanceof FloatNode) {
                $value = (float) $value;
            }
        } else {
            $value = $node->getDefaultValue();
        }
        return $value;
    }

    /**
     * Get configuration value from database
     *
     * @param string $extensionName the extension name
     * @param string $namespace     the namespace linked to the extension
     * @param string $path          the configuration path
     *
     * @return string|null
     */
    protected function getConfigurationFromDatabase($extensionName, $namespace, $path)
    {
        $value = null;
        $extension = $this->extensionRepository->findOneBy(
            array(
                'name' => $extensionName,
                'namespace' => $namespace,
            )
        );
        if ($extension) {
            $value = $extension;
            foreach ($path as $pathElement) {
                $value = $value->get($pathElement);
                if ($value == null) {
                    break;
                }
            }
        }
        return $value;
    }

    /**
     * Get configuration value from default bundle configuration
     *
     * @param ArrayNodeDefinition $tree the configuration tree
     * @param string              $path the path of the configuration key
     *
     * @return string|null
     */
    protected function getConfigurationFromTree(ArrayNode $tree, $path)
    {
        foreach ($path as $pathElement) {
            foreach ($tree->getChildren() as $node) {
                if ($node->getName() == $pathElement) {
                    if ($node instanceof ArrayNode) {
                        $tree = $node;
                    } else {
                        return $node;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Return the configuration tree of a bundle or false if not defined
     *
     * @param string $configurationClass the configuration class
     *
     * @return mixed boolean|array
     */
    public function getContainerConfigurationTree($configurationClass)
    {
        if (class_exists($configurationClass)) {
            $r = new \ReflectionClass($configurationClass);

            if (!method_exists($configurationClass, '__construct')) {
                $configuration = new $configurationClass();

                return $configuration->getConfigTreeBuilder()->buildTree();
            }
        }

        return null;
    }

}
