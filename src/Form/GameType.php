<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('category', ChoiceType::class, [
                'choices' => Game::getCategories(),
                'label' => 'Category',
                'placeholder' => 'Select a category',
                'required' => true,
            ])
            ->add('description', TextareaType::class)
            ->add('developer', TextType::class, [
                'required' => false,
                'label' => 'Developer',
            ])
            ->add('price', NumberType::class, [
                'required' => false,
                'label' => 'Price ($)',
                'help' => 'Leave empty for free games',
                'scale' => 2,
                'attr' => ['step' => '0.01', 'min' => '0'],
            ])
            ->add('capacity', IntegerType::class, [
                'required' => false,
                'label' => 'Max Capacity',
                'help' => 'Leave empty for unlimited reservations',
            ])
            ->add('coverImage', FileType::class, [
                'required' => false,
                'label' => 'Cover Image',
                'attr' => ['accept' => 'image/*'],
                'help' => 'Select an image file from your computer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
