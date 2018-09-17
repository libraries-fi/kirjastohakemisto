<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Dev\Services;

class LibrarySearchForm extends AbstractType
{
    public function getBlockPrefix() : string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $options)
    {
        $options->setDefaults([
            'csrf_protection' => false,
            'method' => 'GET'
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cities = $options['data']['cities'] ?? [];

        $builder
            ->setMethod('GET')
            ->add('coordinates', HiddenType::class, [
                'required' => false,
            ])
            // ->add('page', HiddenType::class, [
            //     'label' => 'Page',
            //     'empty_data' => 1,
            // ])
            ->add('m', ChoiceType::class, [
                'required' => false,
                'label' => 'Municipality',
                // 'placeholder' => '- Select -',
                'choices' => array_flip($cities),
                'attr' => [
                    'data-custom' => true
                ],
            ])
            ->add('q', SearchType::class, [
                'required' => false,
                'label' => false,
            ])
            ->add('geo', CheckboxType::class, [
                'label' => 'Use geolocation',
                'required' => false,
            ])
            ->add('o', CheckboxType::class, [
                'label' => 'Only open libraries',
                'required' => false,
            ])
            ->add('a', ChoiceType::class, [
                'required' => false,
                'label' => 'Accessibility',
                'expanded' => true,
                'multiple' => true,
                'placeholder' => '',
                'choices' => [
                    'Accessible entry' => 'accessible_entry',
                    'Elevator' => 'elevator',
                    'Foo' => 'foo',
                    'Bar' => 'bar',
                    'Baz' => 'baz',
                ]
            ])
            ->add('s', ChoiceType::class, [
                'required' => false,
                'placeholder' => '',
                'label' => 'Provides service',
                'choices' => new Services,
                'attr' => [
                    'data-custom' => true
                ]
            ])
            ->add('t', ChoiceType::class, [
                'required' => false,
                'label' => 'Library type',
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    'Municipal libraries' => 'r',
                    // 'Mobile libraries' => 'c',
                    // 'Academic libraries' => 'e',
                    // 'Vocational college libraries' => 'v',
                    'Polytechnic libraries' => 'p',
                    'University libraries' => 'u',
                    'Special libraries' => 's',
                    'Others' => 'o',
                ]
            ])
            // ->add('next_page', SubmitType::class, [
            //     'label' => 'Display more results',
            // ])
            ->add('submit', SubmitType::class, [
                'label' => 'Search'
            ]);
    }
}
