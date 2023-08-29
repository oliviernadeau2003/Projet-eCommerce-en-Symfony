<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\States;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'state',
                EnumType::class,
                [
                    'label' => false,
                    'class' => States::class,
                    'choice_label' => fn ($choice) => match ($choice) {
                        States::InPreperation => 'In Preperation',
                        States::Sent => 'Sended',
                        States::InTransit => 'In Transit',
                        States::Delivered => 'Delivered',
                        // case InPreperation = "In Preperation";
                        // case Sent = "Sended";
                        // case InTransit = "In Transit";
                        // case Delivered = "Delivered";
                    },
                ]
            );

        // $builder->addEventListener(
        //     FormEvents::PRE_SET_DATA,
        //     function (FormEvent $event) {
        //         $form = $event->getForm();

        //         $state = $event->getData();

        //         $form->add(
        //             'state',
        //             EnumType::class,
        //             [
        //                 'label' => false,
        //                 'class' => States::class,
        //                 'choice_label' => fn ($choice) => match ($choice) {
        //                     States::InPreperation => 'In Preperation',
        //                     States::Sent => 'Sended',
        //                     States::InTransit => 'In Transit',
        //                     States::Delivered => 'Delivered',
        //                     // case InPreperation = "In Preperation";
        //                     // case Sent = "Sended";
        //                     // case InTransit = "In Transit";
        //                     // case Delivered = "Delivered";
        //                 },
        //             ]
        //         );
        //     }
        // );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'item_collection_form'
        ]);
    }
}
