<?php

namespace Naoned\DatabaseConfigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

use Naoned\DatabaseConfigBundle\Entity\Extension;
use Naoned\DatabaseConfigBundle\Form\ConfiguratorType;

/**
 * Configurator Controller
 *
 * @package Naoned.DatabaseConfigBundle.Controller
 *
 * @author  Guillaume Petit <guillaume.petit@sword-group.com>
 */
class ConfiguratorController extends Controller
{

    /**
     * Display a form to edit the configuration of a bundle
     *
     * @param Request $request    the request
     * @param string  $bundleName the bundle name to be configured
     * @param string  $namespace  the namespace of the extension
     *
     * @return Response
     */
    public function editAction(Request $request, $bundleName, $namespace = '')
    {
        $extensionRepository = $this->getDoctrine()->getRepository('NaonedDatabaseConfigBundle:Extension');
        $configRepository = $this->getDoctrine()->getRepository('NaonedDatabaseConfigBundle:Config');

        $manager = $this->getDoctrine()->getManager();
        $bundles = $this->get('kernel')->getBundles();

        $tree = $this->get('naoned_database_config.services.configuration')->getContainerConfigurationTree($bundles[$bundleName]);
        $extension = $extensionRepository->findOneBy(
            [
                'name' => $tree->getName(),
                'namespace' => $namespace,
            ]
        );

        if (false == $extension) {
            $extension = new Extension();
            $extension->setName($tree->getName());
            $extension->setNamespace($namespace);
        }

        $form = $this->createForm(new ConfiguratorType(), $extension, ['tree' => $tree]);

        if ('POST' == $request->getMethod()) {

            $form->bind($request);

            if ($form->isValid()) {

                $this->get('logger')->info('Updating configuration. - ' . (string) $extension);

                // removing the previous config entries from the database
                $configRepository->deleteByExtension($extension->getId());

                $manager->persist($extension);
                $manager->flush($extension);
            }
        }

        return $this->render(
            'NaonedDatabaseConfigBundle::edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Check if a tree node is configuration enabled
     *
     * @param NodeInterface $arrayNode a node
     *
     * @return bool
     */
    protected function isConfiguratorEnabledNode(NodeInterface $arrayNode)
    {
        foreach ($arrayNode->getChildren() as $node) {
            if ($node->getAttribute('configurator')) {
                return true;
            } elseif ($node instanceof ArrayNode) {
                return $this->isConfiguratorEnabledNode($node);
            }
        }
    }


}
