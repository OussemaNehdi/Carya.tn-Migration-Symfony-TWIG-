<?php

namespace App\Form;

use App\Entity\Cars;
use App\Entity\Commands;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rental_date', null, [
                'widget' => 'single_text',
            ])
            ->add('start_date', null, [
                'widget' => 'single_text',
            ])
            ->add('end_date', null, [
                'widget' => 'single_text',
            ])
            ->add('rental_period')
            ->add('confirmed')
            ->add('car_id', EntityType::class, [
                'class' => Cars::class,
                'choice_label' => 'id',
            ])
            ->add('user_id', EntityType::class, [
                'class' => Users::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commands::class,
        ]);
    }
}
