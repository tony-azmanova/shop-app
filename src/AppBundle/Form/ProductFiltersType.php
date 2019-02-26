<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use AppBundle\Repository\ProductRepository;
use AppBundle\Repository\CategoryRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProductFiltersType extends AbstractType
{
    protected $productRepository;
    
    protected $categoryRepository;

    public function __construct(ProductRepository $productRepository, CategoryRepository $categoryRepository) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function getBlockPrefix()
    {
        return null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('category', ChoiceType::class, [
                'placeholder' => 'select.all',
                'choices' => $this->getCategoryValues(),
                'required' => false,
            ])
            ->add('color', ChoiceType::class, [
                'placeholder' => 'select.all',
                'choices' => $this->getColorValues(),
                'required' => false,
            ])
            ->add('order', ChoiceType::class, [
                'choices' => [
                    'price.desc' =>'price:desc',
                    'price.asc' =>'price:asc',
                    'name.asc' =>'name:asc',
                    'name.desc' =>'name:desc',
                ],
            ]);
    }
    
    private function getCategoryValues()
    {
        $values = $this->categoryRepository->findAll();
        $results = [];
        foreach($values as $value) {
           $results[$value->getName()] = $value->getId();
        }

        return $results;
    }

    private function getColorValues()
    {
        $values = $this->productRepository->findAllColors();
        $results = [];
        foreach($values as $value) {
            $results[$value['color']] = $value['color'];
        }

        return $results;
    }
}