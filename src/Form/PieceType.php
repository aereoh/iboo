<?php

namespace App\Form;

use App\Entity\Machine;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PieceType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user_id = $options['user_id'];
        $role = $options['role'];

        if($role == 'ROLE_ADMIN') {
            $builder->add('machine', EntityType::class, [
                'class' => Machine::class,
                'label' => false,
                'choice_label' => 'type',
                'mapped' => false
            ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Send',
                    'attr' => [ 'class' => 'btn btn-primary']
                ]);
        } else {
            $builder->add('machine', EntityType::class, [
                'class' => Machine::class,
                'query_builder' => function (EntityRepository $er) use ($user_id) {
                    return $er->createQueryBuilder('m')
                        ->where('m.worker = :id')
                        ->setParameter('id', $user_id);
                },
                'label' => false,
                'choice_label' => 'type',
                'mapped' => false
            ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Send',
                    'attr' => [ 'class' => 'btn btn-primary']
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'user_id' => null,
            'role' => null
        ));
    }
}