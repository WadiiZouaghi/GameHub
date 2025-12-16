<?php

namespace App\Form;

use App\Entity\Game;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
            ->add('platform', TextType::class, [
                'required' => false,
                'label' => 'Platform',
                'help' => 'e.g., PC, PlayStation, Xbox, Nintendo',
            ])
            ->add('languages', TextareaType::class, [
                'required' => false,
                'label' => 'Languages',
                'help' => 'Comma-separated list of supported languages',
                'attr' => ['rows' => 3],
            ])
            ->add('gallery', FileType::class, [
                'required' => false,
                'label' => 'Gallery Images',
                'multiple' => true,
                'attr' => ['accept' => 'image/*'],
                'help' => 'Select multiple screenshot/artwork images',
                'data_class' => null,
            ])
            ->add('coverImage', FileType::class, [
                'required' => false,
                'label' => 'Cover Image',
                'attr' => ['accept' => 'image/*'],
                'help' => 'Select an image file from your computer',
                'data_class' => null,
            ])

            // Minimum System Requirements
            ->add('minOs', ChoiceType::class, [
                'label' => 'Minimum OS',
                'choices' => [
                    'Windows 7' => 'Windows 7',
                    'Windows 10' => 'Windows 10',
                    'Windows 11' => 'Windows 11',
                    'macOS 10.7' => 'Mac OS X 10.7',
                    'macOS 10.12' => 'Mac OS X 10.12',
                    'Ubuntu 12.04' => 'Ubuntu 12.04',
                    'Ubuntu 18.04' => 'Ubuntu 18.04',
                ],
                'required' => false,
                'placeholder' => 'Select OS',
                'mapped' => false,
            ])
            ->add('minProcessor', ChoiceType::class, [
                'label' => 'Minimum Processor',
                'choices' => [
                    'Intel Core i5 @ 2.5 GHz' => 'Intel Core i5 @ 2.5 GHz',
                    'Intel Core i7 @ 3.0 GHz' => 'Intel Core i7 @ 3.0 GHz',
                    'AMD Ryzen 5 1600' => 'AMD Ryzen 5 1600',
                    'AMD Ryzen 7 1700' => 'AMD Ryzen 7 1700',
                ],
                'required' => false,
                'placeholder' => 'Select Processor',
                'mapped' => false,
            ])
            ->add('minMemory', ChoiceType::class, [
                'label' => 'Minimum RAM',
                'choices' => [
                    '4 GB' => '4 GB',
                    '8 GB' => '8 GB',
                    '16 GB' => '16 GB',
                    '32 GB' => '32 GB',
                ],
                'required' => false,
                'placeholder' => 'Select RAM',
                'mapped' => false,
            ])
            ->add('minGraphics', ChoiceType::class, [
                'label' => 'Minimum Graphics',
                'choices' => [
                    'NVIDIA GeForce GTX 960' => 'NVIDIA GeForce GTX 960',
                    'NVIDIA GeForce GTX 1060' => 'NVIDIA GeForce GTX 1060',
                    'AMD Radeon R9 290' => 'AMD Radeon R9 290',
                    'AMD Radeon RX 580' => 'AMD Radeon RX 580',
                ],
                'required' => false,
                'placeholder' => 'Select GPU',
                'mapped' => false,
            ])
            ->add('minStorage', ChoiceType::class, [
                'label' => 'Minimum Storage',
                'choices' => [
                    '30 GB available space' => '30 GB available space',
                    '50 GB available space' => '50 GB available space',
                    '70 GB available space' => '70 GB available space',
                    '100 GB available space' => '100 GB available space',
                ],
                'required' => false,
                'placeholder' => 'Select Storage',
                'mapped' => false,
            ])

            // Recommended System Requirements
            ->add('recOs', ChoiceType::class, [
                'label' => 'Recommended OS',
                'choices' => [
                    'Windows 10' => 'Windows 10',
                    'Windows 11' => 'Windows 11',
                    'macOS 10.12' => 'Mac OS X 10.12',
                    'macOS 11' => 'macOS 11',
                    'Ubuntu 18.04' => 'Ubuntu 18.04',
                    'Ubuntu 20.04' => 'Ubuntu 20.04',
                ],
                'required' => false,
                'placeholder' => 'Select OS',
                'mapped' => false,
            ])
            ->add('recProcessor', ChoiceType::class, [
                'label' => 'Recommended Processor',
                'choices' => [
                    'Intel Core i7 @ 3.5 GHz or better' => 'Intel Core i7 @ 3.5 GHz or better',
                    'Intel Core i9 @ 3.6 GHz' => 'Intel Core i9 @ 3.6 GHz',
                    'AMD Ryzen 7 2700' => 'AMD Ryzen 7 2700',
                    'AMD Ryzen 9 3900X' => 'AMD Ryzen 9 3900X',
                ],
                'required' => false,
                'placeholder' => 'Select Processor',
                'mapped' => false,
            ])
            ->add('recMemory', ChoiceType::class, [
                'label' => 'Recommended RAM',
                'choices' => [
                    '8 GB' => '8 GB',
                    '16 GB' => '16 GB',
                    '32 GB' => '32 GB',
                    '64 GB' => '64 GB',
                ],
                'required' => false,
                'placeholder' => 'Select RAM',
                'mapped' => false,
            ])
            ->add('recGraphics', ChoiceType::class, [
                'label' => 'Recommended Graphics',
                'choices' => [
                    'NVIDIA GeForce RTX 2070' => 'NVIDIA GeForce RTX 2070',
                    'NVIDIA GeForce RTX 3080' => 'NVIDIA GeForce RTX 3080',
                    'AMD Radeon RX 5700 XT' => 'AMD Radeon RX 5700 XT',
                    'AMD Radeon RX 6800 XT' => 'AMD Radeon RX 6800 XT',
                ],
                'required' => false,
                'placeholder' => 'Select GPU',
                'mapped' => false,
            ])
            ->add('recStorage', ChoiceType::class, [
                'label' => 'Recommended Storage',
                'choices' => [
                    'SSD with 50 GB free space' => 'SSD with 50 GB free space',
                    'SSD with 60 GB free space' => 'SSD with 60 GB free space',
                    'SSD with 80 GB free space' => 'SSD with 80 GB free space',
                    'SSD with 100 GB free space' => 'SSD with 100 GB free space',
                ],
                'required' => false,
                'placeholder' => 'Select Storage',
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Game::class,
        ]);
    }
}
