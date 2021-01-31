<?php

namespace App\Form;

use App\Entity\Episode;
use App\Entity\Season;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EpisodeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", TextType::class)
            ->add("number", NumberType::class)
            ->add("synopsis", TextareaType::class)
            ->add("season", EntityType::class, [
                'class' => Season::class,
                'choice_label' => 'number',
                'multiple' => false,
                'expanded' => false,
                'by_reference' => false
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Episode::class,
        ]);
    }

}
