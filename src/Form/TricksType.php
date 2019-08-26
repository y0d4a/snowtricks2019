<?php

namespace App\Form;

use App\Entity\Tricks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TricksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['label' => 'Nom du Tricks'])
            ->add('description', null, ['label' => 'Description du Tricks'])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Choisissez la catégorie' => [
                        'Catégorie 1' => 'Catégorie 1',
                        'Catégorie 2' => 'Catégorie 2',
                        'Catégorie 3' => 'Catégorie 3',
                        'Catégorie 4' => 'Catégorie 4'
                    ]
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tricks::class,
        ]);
    }
}
