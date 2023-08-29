<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'required' => true
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => "Passwords must be identical",
                'constraints' => [new Assert\Length(min: 6, minMessage: "Your password name must contain {{ limit }} minimum characters")],
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => ['label' => "New Password"],
                'second_options' => ['label' => "Confirm Password"]
            ])
            ->add('updatebtn', SubmitType::class, [
                'label' => "Update Your Password",
                'row_attr' => ['class' => 'form-button'],
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    // public function configureOptions(OptionsResolver $resolver): void
    // {
    //     $resolver->setDefaults([
    //         'data_class' => User::class,
    //     ]);
    // }
}
