<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RegisterType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('role', HiddenType::class, [
                    'data' => 'ROLE_USER',
                ])
                ->add('username', TextType::class, [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Username'
                        ]
                ])
                ->add('email', EmailType::class, [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Email'
                        ]
                ])
                ->add('password', PasswordType::class, [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Password'
                        ]
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Send',
                    'attr' => [ 'class' => 'btn btn-primary']
                ]);
    }
}