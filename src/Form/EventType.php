<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Game;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Event Title',
            ])
            ->add('game', EntityType::class, [
                'class' => Game::class,
                'choice_label' => 'title',
                'label' => 'Game',
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Event Date & Time',
                'widget' => 'single_text',
            ])
            ->add('maxPlayers', IntegerType::class, [
                'label' => 'Max Players',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
