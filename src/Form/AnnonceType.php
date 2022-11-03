<?php

namespace App\Form;

use App\Entity\Ad;
use App\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AnnonceType extends ApplicationType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class,$this->getConfiguration('Titre','Insérer un titre'))
            ->add('slug',TextType::class,$this->getConfiguration('Alias','Personnalisez un alias pour générer l\'url',['required'=>false]))
            ->add('coverImage',UrlType::class,$this->getConfiguration('Image de couverture','Insérez une image'))
            ->add('introduction',TextType::class,$this->getConfiguration('Résumé','Présentez votre bien'))
            ->add('content',TextareaType::class,$this->getConfiguration('Description détaillée','Décrivez vos services'))
            ->add('rooms',IntegerType::class,$this->getConfiguration('Nombre de chambres','Nombres de chambres'))
            ->add('price',MoneyType::class,$this->getConfiguration('Prix','Prix des chambres/nuit'))
            ->add('images',CollectionType::class,['entry_type'=>ImageType::class,'allow_add'=>true,'allow_delete'=>true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
