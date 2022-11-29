<?php

namespace App\Form;

use App\Entity\Comment;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating',IntegerType::class,$this->getConfiguration("Note sur 5","Veuillez indiquer une note entre 0 et 5",
                ['attr'=>   ['min'=>0,
                             'max' =>5
                            ]
                ]))
            ->add('content',TextareaType::class,$this->getConfiguration("Votre avis","Détaillez votre expérience lors de votre séjour"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
