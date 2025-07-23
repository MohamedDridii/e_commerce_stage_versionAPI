<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\Store;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\StoreStockType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $stockData = $options['stock_data'] ?? [];

        $builder
            ->add('name')
            ->add('price')
            ->add('image')
            ->add('description')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'required'=>true
            ])
            ->add('stock_data',CollectionType::class,[
                'required'=>true,
                'mapped'=>false,
                'label'=>false,
                'entry_type'=>StoresStockType::class,
                'entry_options' => [],
                'allow_add'=>false,
                'allow_delete'=>false,
                'data'=>$options['stock_data']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'stock_data'=>[] 
        ]);
    }
}
