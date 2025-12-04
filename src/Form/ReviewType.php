<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
    'label' => 'Rating',
    'choices' => [
        '⭐⭐⭐⭐⭐' => 5,
        '⭐⭐⭐⭐' => 4,
        '⭐⭐⭐' => 3,
        '⭐⭐' => 2,
        '⭐' => 1,
    ],
    'expanded' => true,
    'multiple' => false,
    'attr' => [
        'class' => 'rating-input'
    ],
    'constraints' => [
        new NotBlank([
            'message' => 'Please select a rating',
        ]),
        new Range([
            'min' => 1,
            'max' => 5,
            'notInRangeMessage' => 'Rating must be between {{ min }} and {{ max }} stars',
        ]),
    ],
])

            ->add('comment', TextareaType::class, [
                'label' => 'Your Review',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Share your thoughts about this game...'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please write a review',
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Your review should be at least {{ limit }} characters',
                        'max' => 1000,
                        'maxMessage' => 'Your review cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
