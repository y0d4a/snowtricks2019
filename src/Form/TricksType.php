<?php

namespace App\Form;

use App\Entity\Tricks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TricksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['label' => 'Nom du Tricks'])
            ->add('description', null, ['label' => 'Description du Tricks'])
            ->add('category', null, ['label' => 'Catégorie du Tricks'])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Choisissez le statut' => [
                        'Publié' => 'Publié',
                        'Brouillon' => 'Brouillon'
                    ],
                ],
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
