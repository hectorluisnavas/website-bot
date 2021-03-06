<?php

namespace App\Form;

use App\Entity\Agent;
use App\Entity\Faction;
use App\Entity\MapGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nickname')
            ->add(
                'real_name',
                null,
                [
                    'label' => 'Real Name',
                ]
            )
            ->add(
                'faction',
                EntityType::class,
                [
                    'class'        => Faction::class,
                    'choice_label' => 'name',
                ]
            )
            ->add('telegramName')
            ->add('recursions')
            ->add('customMedals')
            ->add(
                'lat',
                NumberType::class,
                [
                    'required' => false,
                    'scale'    => 7,
                    'attr'     => [
                        'min'  => -90,
                        'max'  => 90,
                        'step' => 0.0000001,
                    ],
                ]
            )
            ->add(
                'lon',
                NumberType::class,
                [
                    'required' => false,
                    'scale'    => 7,
                    'attr'     => [
                        'min'  => -90,
                        'max'  => 90,
                        'step' => 0.0000001,
                    ],
                ]
            )
            ->add(
                'mapGroup',
                EntityType::class,
                [
                    'class'        => MapGroup::class,
                    'choice_label' => 'name',
                    'required'     => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Agent::class,
            ]
        );
    }
}
