<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'attr' => ['class' => 'input-box', 'id' => 'regUsername', 'placeholder' => ' ']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'attr' => ['class' => 'input-box', 'id' => 'regUsername', 'placeholder' => ' ']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email address',
                'attr' => ['class' => 'input-box', 'id' => 'regEmail', 'placeholder' => ' '],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'attr' => ['class' => 'input-box', 'id' => 'regPassword', 'placeholder' => ' ']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Sign Up',
                'attr' => ['class' => 'input-submit']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
