<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\Store;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoresStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('store', EntityType::class, [
                'class' => Store::class,
                'choice_label' => 'name',
                'label'=>false,
                'attr'=>['style'=>'display:none']
                ])
            ->add('selected', CheckboxType::class, [
                'label' => false,
                'required' => false,
                'mapped'=>false
            ])
            ->add('quantity',IntegerType::class,[
                'required'=>false,
                'mapped'=>true
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
