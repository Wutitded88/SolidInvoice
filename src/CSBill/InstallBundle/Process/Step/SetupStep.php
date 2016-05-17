<?php

/*
 * This file is part of CSBill project.
 *
 * (c) 2013-2016 Pierre du Plessis <info@customscripts.co.za>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace CSBill\InstallBundle\Process\Step;

use CSBill\CoreBundle\CSBillCoreBundle;
use CSBill\CoreBundle\Repository\VersionRepository;
use CSBill\InstallBundle\Form\Step\SystemInformationForm;
use CSBill\UserBundle\Entity\User;
use CSBill\UserBundle\Repository\UserRepository;
use RandomLib\Factory;
use Sylius\Bundle\FlowBundle\Process\Context\ProcessContextInterface;
use Sylius\Bundle\FlowBundle\Process\Step\ControllerStep;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class SetupStep extends ControllerStep
{
    /**
     * {@inheritdoc}
     */
    public function displayAction(ProcessContextInterface $context)
    {
        $form = $this->getForm($context->getRequest());

        return $this->render(
            'CSBillInstallBundle:Flow:setup.html.twig',
            array(
                'form' => $form->createView(),
                'userCount' => $this->getUserCount(),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function forwardAction(ProcessContextInterface $context)
    {
        $request = $context->getRequest();
        $form = $this->getForm($context->getRequest());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if (0 === $this->getUserCount()) {
                $this->createAdminUser($data);
            }

            $this->saveCurrentVersion();
            $this->saveConfig($data);

            return $this->complete();
        }

        return $this->render(
            'CSBillInstallBundle:Flow:setup.html.twig',
            array(
                'form' => $form->createView(),
                'userCount' => $this->getUserCount(),
            )
        );
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\Form\Form
     */
    private function getForm(Request $request)
    {
        $options = array(
            'action' => $this->generateUrl(
                'sylius_flow_forward',
                array(
                    'scenarioAlias' => 'install',
                    'stepName' => 'setup',
                )
            ),
        );

        return $this->createForm(new SystemInformationForm($request, $this->getUserCount()), null, $options);
    }

    /**
     * @param array $data
     */
    private function createAdminUser(array $data)
    {
        $user = new User();

        /** @var PasswordEncoderInterface $encoder */
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);

        $password = $encoder->encodePassword($data['password'], $user->getSalt());

        $user->setUsername($data['username'])
            ->setEmail($data['email_address'])
            ->setPassword($password)
            ->setEnabled(true)
            ->setSuperAdmin(true);

        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $entityManager->persist($user);
        $entityManager->flush();
    }

    /**
     * Saves the current app version in the database.
     */
    private function saveCurrentVersion()
    {
        $version = CSBillCoreBundle::VERSION;

        $entityManager = $this->container->get('doctrine')->getManager();

        /** @var VersionRepository $repository */
        $repository = $entityManager->getRepository('CSBillCoreBundle:Version');

        $repository->updateVersion($version);
    }

    /**
     * @param array $data
     *
     * @throws \RuntimeException
     */
    protected function saveConfig(array $data)
    {
        $factory = new Factory();

        $time = new \DateTime('NOW');

        $config = array(
            'locale' => $data['locale'],
            'currency' => $data['currency'],
            'base_url' => $data['base_url'],
            'installed' => $time->format(\DateTime::ISO8601),
            'secret' => $factory->getMediumStrengthGenerator()->generateString(32),
        );

        $this->get('csbill.core.config_writer')->dump($config);
    }

    /**
     * @return int
     */
    private function getUserCount()
    {
        static $userCount;

        if (null === $userCount) {
            $entityManager = $this->container->get('doctrine.orm.entity_manager');

            /** @var UserRepository $repository */
            $repository = $entityManager->getRepository('CSBillUserBundle:User');

            $userCount = $repository->getUserCount();
        }

        return $userCount;
    }
}
