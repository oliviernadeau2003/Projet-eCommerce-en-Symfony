<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// class UpdateInfoFormType extends AbstractType
class UpdateInfoFormType extends RegistrationFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('updatebtn', SubmitType::class, [
                'label' => "Update Account",
                'row_attr' => ['class' => 'form-button'],
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->remove('email')
            ->remove('password')
            ->remove('createbtn');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
