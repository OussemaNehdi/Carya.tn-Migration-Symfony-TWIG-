<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Users;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First name',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'first name']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last name',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'last name']
            ])
            ->add('country', TextType::class, [
                'label' => 'Country',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'country']
            ])
            ->add('state', TextType::class, [
                'label' => 'State/Region',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'state']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter email']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}


