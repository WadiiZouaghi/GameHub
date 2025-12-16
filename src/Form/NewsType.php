<?php

namespace App\Form;

use App\Entity\News;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'News Title',
            ])
            ->add('category', ChoiceType::class, [
                'choices' => News::getCategories(),
                'placeholder' => 'Select a category',
                'attr' => ['class' => 'form-control'],
                'label' => 'Category',
            ])
            ->add('content', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'rows' => 8],
                'label' => 'Content',
            ])
            ->add('author', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'label' => 'Author',
            ])
            ->add('image', FileType::class, [
                'required' => false,
                'label' => 'News Image',
                'attr' => ['accept' => 'image/*'],
                'data_class' => null,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }
}
