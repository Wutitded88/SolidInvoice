<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\MailerBundle\Form\Type\TransportConfig;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @see \SolidInvoice\MailerBundle\Tests\Form\Type\TransportConfig\SmtpTransportConfigTypeTest
 */
final class SmtpTransportConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'host',
            null,
            [
                'constraints' => new NotBlank(['groups' => 'smtp']),
            ]
        );

        $builder->add(
            'port',
            IntegerType::class,
            [
                'constraints' => new Type(['groups' => ['smtp'], 'type' => 'integer']),
                'required' => false,
            ]
        );

        $builder->add(
            'user',
            null,
            [
                'constraints' => new NotBlank(['groups' => 'smtp']),
                'required' => false,
            ]
        );

        $builder->add(
            'password',
            PasswordType::class,
            [
                'constraints' => new NotBlank(['groups' => 'smtp']),
                'required' => false,
            ]
        );
    }
}
