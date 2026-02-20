<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'placeholder' => 'Выберите услугу',
                'constraints' => [new Assert\NotBlank(message: 'Выберите услугу')],
                'choice_attr' => static fn(Service $s) => [
                    'data-price' => (string)$s->getPrice(),
                ],
            ])
            ->add('email', EmailType::class,
                [
                    'constraints' => [
                        new Assert\NotBlank(message: 'Укажите почту'),
                        new Assert\Email(message: 'Введите корректный email'),
                    ],
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
