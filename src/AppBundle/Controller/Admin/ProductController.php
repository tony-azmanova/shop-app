<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Product;
use AppBundle\Form\CreateProductType;
use AppBundle\Command\UploadImageComand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ProductController extends Controller
{
    /**
     * @Route("/admin/listProducts", name="admin_list_products")
     * @return type
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository(Product::class)->createQueryBuilder('products');

        $paginator  = $this->get('knp_paginator');
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 5);
        $products = $paginator->paginate($queryBuilder->getQuery(), $page, $limit);

        return $this->render('admin/listingProducts.html.twig', compact('products'));
    }

    /**
     * @Route("/admin/createProduct", name="admin_create_product", methods="GET")
     */
    public function createProductAction()
    {
        $product = new Product();
        $form = $this->createForm(
            CreateProductType::class,
            $product,[
                'action' => $this->generateUrl('admin_save_product'),
                'method' => 'POST',
            ]);

        return $this->render('admin/createProduct.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/saveProduct", name="admin_save_product", methods="POST")
     */
    public function saveProduct(Request $request)
    {
        $product = new Product();

        $form = $this->createForm(CreateProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->render('admin/createProduct.html.twig',['form' => $form->createView()]);
        }

        $this->saveProductTransaction($product);

        return $this->redirectToRoute('admin_list_products');
    }

    /**
     * @Route("/admin/editProduct/{slug}", name="admin_edit_product")
     */
    public function editAction(Request $request, Product $product)
    {
        $form = $this->createForm(CreateProductType::class, $product);
        $form->handleRequest($request);

        $thumbnails = [];

        if (!$form->isSubmitted() && $product->getImage()) {
            $thumbnails = (new \AppBundle\Services\ThumbnailService())->getThumbnails($product->getImage()->getFilePath());
        }

        if(!$form->isSubmitted()) {
            return $this->render('admin/editProduct.html.twig',['form' => $form->createView(), 'product' => $product, 'thumbnails' => $thumbnails]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->render('admin/editProduct.html.twig',['form' => $form->createView(), 'product' => $product, 'thumbnails' => $thumbnails]);
        }

        $this->saveProductTransaction($product);

        return $this->redirectToRoute('admin_list_products');
    }

    /**
     * Save the product entity using transaction
     * 
     * @param type $product
     * @throws \Exception
     */
    public function saveProductTransaction($product)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $this->handleUploadImage($product);
            $em->persist($product);
            $em->flush();
            $em->getConnection()->commit();  
        } catch (\Exception $exc) {
            $em->getConnection()->rollBack();
            throw $exc;
        }
    }

    /**
     * @Route("/admin/deleteProduct/{id}", name="admin_delete_product")
     */
    public function deleteAction($id) 
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }

        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('admin_list_products');
    }

    /**
     * Calls the handle method in the UploadImageComand
     * 
     * @param type $product
     */
    private function handleUploadImage($product)
    {
        if ($product->getImage()) {
            (new UploadImageComand($product, $this->getParameter('brochures_directory')))->handle();
        }
    }
}