<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;

use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ["label" => "Name"])
            ->add('prix', NumberType::class, ["label" => "Price"])
            ->add('quantiteEnStock', NumberType::class, ["label" => "Quantity"])
            ->add('description', TextType::class, ["label" => "Description"])
            ->add('imagePath', FileType::class, [
                'label' => 'Product Image',
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Upload a valid image format'
                    ])
                ]
            ])
            // ->add('imagePath', TextType::class, [
            //     'required' => false
            // ])
            ->add(
                'categorie',
                EntityType::class,
                [
                    'class' => Categorie::class,
                    'choice_label' => 'categorie',
                    'label' => 'Category'
                ]
            )
            ->add('btnSave', SubmitType::class, [
                'label' => 'Save',
                'row_attr' => ['class' => 'form-button'],
                'attr' => ['class' => 'btn btn-primary col-2']
            ]);

        $builder->get('imagePath')->addModelTransformer(new CallBackTransformer(
            function ($imagePath) {
                return null;
            },
            function ($imagePath) {
                return $imagePath;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'item_collection_form'
        ]);
    }
}
