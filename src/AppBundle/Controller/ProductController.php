<?php

namespace AppBundle\Controller;

use AppBundle\Form\ProductFiltersType;
use AppBundle\Services\ThumbnailService;
use AppBundle\Presenter\ThumbnailPresenter;
use AppBundle\Repository\ProductRepository;
use AppBundle\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ProductController extends Controller
{
    protected $productRepository;

    protected $thumbnailService;

    public function __construct(ProductRepository $productRepository, CategoryRepository $categoryRepository, ThumbnailService $thumbnailService)
    {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->thumbnailService = $thumbnailService;
    }

    /**
     * @Route("/listProduct",name="product_listing")
     * @return Response
     */
    public function listAction(Request $request)
    {
        $filtersForm = $this->createForm(ProductFiltersType::class, $request->query->all());
        $filters = $filtersForm->createView();
        $products = $this->paginateResults($request);
        if (!$products) {
            throw $this->createNotFoundException('The product does not exist');
        }

        $productsDecorated =  array_map(function($product) {
                return new ThumbnailPresenter($product, $this->thumbnailService);
            },
            $products->getItems()
        );
        $products->setItems($productsDecorated);

        return $this->render('productListing.html.twig', compact('products', 'filters'));
    }

    /**
     * Build the query for pagination with the filters
     * 
     * @param Request $request
     * @return type
     */
    public function buildPagination(Request $request)
    {
        /* @var $queryBuilder \Doctrine\ORM\QueryBuilder */
        $queryBuilder = $this->productRepository->createQueryBuilder('p');

        if ($request->query->getAlnum('category')) {
           $queryBuilder = $this->productRepository->findAllByCategory($queryBuilder, $request->query->getAlnum('category'));
        }

        if ($request->query->get('color')) {
            $queryBuilder = $this->productRepository->findAllByColor($queryBuilder, $request->query->get('color'));
        }
 
        if ($request->query->get('order')) {
            $sortOptions = $this->getSortingColumn($request);
            $queryBuilder = $queryBuilder->orderBy('p.'. $sortOptions->column, $sortOptions->direction);
        }
        
        return $queryBuilder;
    }

    /**
     * Get the sorting column and direction
     * 
     * @param Request $request
     * @param array $allowedColumns
     * @return \stdClass
     */
    public function getSortingColumn(Request $request, $allowedColumns = ['price','name'])
    {
        $sortOptions = new \stdClass();
        $sortOptions->column = 'id';
        $sortOptions->direction = 'ASC';
        $sort = explode(':', $request->query->get('order'));
        if (in_array($sort[0], $allowedColumns)) {
            $sortOptions->column = $sort[0];
            $sortOptions->direction = $sort[1];
        }

        return $sortOptions;
    }

    /**
     * @Route("/productDetails/{slug}", name="product_details")
     */
    public function detailViewAction($slug)
    {
        $product = $this->productRepository->findOneBySlug($slug);
        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }

        $productDecorated =  new ThumbnailPresenter($product, $this->thumbnailService);

        return $this->render('productDetails.html.twig', ['product' => $productDecorated]);
    }

    /**
     * Generates the pagination by the passed results
     * 
     * @param Request $request
     * @return \Knp\Component\Pager\Paginator
     */
    private function paginateResults(Request $request)
    {
        /* @var $paginator  \Knp\Component\Pager\Paginator */
        $paginator  = $this->get('knp_paginator');
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 5);

        return $paginator->paginate($this->buildPagination($request)->getQuery(), $page, $limit);
    }
}
