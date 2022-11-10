<?php

namespace App\Form;

use App\Entity\Booking;
use App\Form\ApplicationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\DataTransformer\FrToDatetimeTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BookingType extends ApplicationType
{
    private $tansformer;

    public function __construct(FrToDatetimeTransformer $transformer){
        $this->transformer = $transformer;

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate',TextType::class,$this->getConfiguration("Date d'arrivée","La date de votre arrivée"))
            ->add('endDate',TextType::class,$this->getConfiguration("Date de départ","La date de votre départ"))
            ->add('comment',TextareaType::class,$this->getConfiguration(false,"Ajouter un commentaire pour votre séjour",['required'=>false]))
        ;

        $builder->get('startDate')->addModelTransformer($this->transformer);
        $builder->get('endDate')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
