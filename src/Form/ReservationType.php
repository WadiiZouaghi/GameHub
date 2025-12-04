<?php

namespace App\Form;

use App\Entity\Game;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'query_builder' => function (UserRepository $repo) {
                    return $repo->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC');
                },
                'placeholder' => 'Select a user',
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a user',
                    ]),
                ],
            ])
            ->add('game', EntityType::class, [
                'class' => Game::class,
                'choice_label' => 'title',
                'query_builder' => function (GameRepository $repo) {
                    return $repo->createQueryBuilder('g')
                        ->orderBy('g.title', 'ASC');
                },
                'placeholder' => 'Select a game',
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a game',
                    ]),
                ],
            ])
            ->add('reservationDate', DateTimeType::class, [
                'label' => 'Reservation Date & Time',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a reservation date and time',
                    ]),
                    new GreaterThan([
                        'value' => 'now',
                        'message' => 'Reservation date must be in the future',
                    ]),
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Pending' => 'pending',
                    'Confirmed' => 'confirmed',
                    'Cancelled' => 'cancelled',
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'data' => 'pending', // Default value
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
