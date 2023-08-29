<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
                'attr' => [
                    "autofocus" => true
                ]
            ])
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'First Name',
                'attr' => []
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'Last Name',
                'attr' => []
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => "Passwords must be identical",
                'constraints' => [new Assert\Length(min: 6, minMessage: "Your password name must contain {{ limit }} minimum characters")],
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => ['label' => "Password"],
                'second_options' => ['label' => "Confirm Password"]
            ])
            ->add('address', TextType::class, [
                'required' => true,
                'label' => 'Address',
                'attr' => []
            ])
            ->add('city', TextType::class, [
                'required' => true,
                'label' => 'City',
                'attr' => []
            ])
            ->add('postalCode', TextType::class, [
                'required' => true,
                'label' => 'Postal Code',
                'attr' => []
            ])
            ->add(
                'province',
                ChoiceType::class,
                [
                    'label' => 'Province',
                    'required' => true,
                    'choices' => [
                        "Alberta" => "AB",
                        "Colombie-Britannique" => "BC",
                        "Manitoba" => "MB",
                        "Nouveau-Brunswick" => "NB",
                        "Terre-Neuve-et-Labrador" => "NL",
                        "Nouvelle-Écosse" => "NS",
                        "Territoires du Nord-Ouest" => "NT",
                        "Nunavut" => "NU",
                        "Ontario" => "ON",
                        "Île-du-Prince-Édouard" => "PE",
                        "Québec" => "QC",
                        "Saskatchewan" => "SK",
                        "Yukon" => "YT"
                    ]
                ]
            )
            ->add('phone', TextType::class, [
                'required' => false,
                'label' => 'Phone',
                'attr' => []
            ])
            ->add('createbtn', SubmitType::class, [
                'label' => "Create Account",
                'row_attr' => ['class' => 'form-button'],
                'attr' => ['class' => 'btn btn-primary']
            ]);

        $builder->get('phone')->addModelTransformer(new CallbackTransformer(
            function ($phoneFromDatabase) {
                $newPhone = substr_replace($phoneFromDatabase, "-", 3, 0);
                return substr_replace($newPhone, "-", 7, 0);
            },
            function ($phoneFromView) {
                return str_replace("-", "", $phoneFromView);
            }
        ));

        $builder->get('postalCode')->addModelTransformer(new CallbackTransformer(
            function ($postalCodeFromDatabase) {
                return substr_replace($postalCodeFromDatabase, " ", 3, 0);
            },
            function ($postalCodeFromView) {
                return str_replace(" ", "", $postalCodeFromView);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
