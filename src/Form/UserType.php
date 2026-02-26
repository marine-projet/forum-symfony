<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Pseudo : ',
                'constraints' => [
                    new NotBlank(message: 'Merci de saisir un pseudo.'),
                    new Length(
                        min: 3,
                        max: 50,
                        minMessage: 'Votre pseudo doit comporter au moins {{ limit }} caractères.',
                        maxMessage: 'Votre pseudo ne peut pas dépasser {{ limit }} caractères.'
                    ),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email : ',
                'constraints' => [
                    new NotBlank(message: 'Merci de saisir une adresse email.'),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label'       => 'Mot de passe : ',
                'mapped'      => false,              // 👈 pas stocké tel quel
                'attr'        => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(message: 'Merci de saisir un mot de passe.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
