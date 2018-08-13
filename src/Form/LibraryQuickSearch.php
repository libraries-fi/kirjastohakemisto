<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibraryQuickSearch extends AbstractType
{
    public function configureOptions(OptionsResolver $options)
    {
        $options->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('coordinates', HiddenType::class)
            ->add('tags', SearchType::class, [
                'label' => 'Search by name or municipality or service...',
            ])
            ->add('geo', ButtonType::class, [
                'label' => 'G',
            ])
            ->add('go', SubmitType::class, [
                'label' => 'Search'
            ]);
    }
}
