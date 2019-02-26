<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Category;
use AppBundle\Form\EditCategoryType;
use AppBundle\Form\CreateCategoryType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CategoryController extends Controller
{
    /**
     * @Route("/admin/listCategories", name="admin_list_categories")
     * @return type
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository(Category::class)->createQueryBuilder('category');

        $paginator  = $this->get('knp_paginator');
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 5);
        $categories = $paginator->paginate($queryBuilder->getQuery(), $page, $limit);

        return $this->render(
            'admin/listingCategories.html.twig',
            compact('categories')
        );
    }

    /**
     * @Route("/admin/createCategory", name="admin_create_category", methods="GET")
     * @return type
     */
    public function creteCategoryAction()
    {
        $category = new Category();
        $form = $this->createForm(
            CreateCategoryType::class,
            $category,
            ['action' => $this->generateUrl('admin_save_category'),
             'method' => 'POST',
            ]
        );

        return $this->render(
            'admin/createCategory.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/admin/saveCategory", name="admin_save_category",methods="POST")
     * @param Request $request
     * @return type
     */
    public function saveCategoryAction(Request $request)
    {
        $category = new Category();

        $form = $this->createForm(CreateCategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->render(
                'admin/createCategory.html.twig',
                ['form' => $form->createView()]
            );
        }

        $this->saveCategoryTransaction($category);

        return $this->redirectToRoute('admin_list_categories');
    }

    /**
     * @Route("/admin/editCategory/{id}", name="admin_edit_category")
     */
    public function editAction(Request $request, Category $category) {
        $form = $this->createForm(EditCategoryType::class, $category);
        $form->handleRequest($request);

        if(!$form->isSubmitted()) {
            return $this->render('admin/editCategory.html.twig', ['form' => $form->createView(), 'category' => $category]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->render('admin/editCategory.html.twig', ['form' => $form->createView(), 'category' => $category]);
        }
        $this->saveCategoryTransaction($category);

        return $this->redirectToRoute('admin_list_categories');
    }

    /**
     * @Route("/admin/deleteCategory/{id}", name="admin_delete_category")
     */
    public function deleteAction($id) 
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository(Category::class)->find($id);

        if (!$category) {
            throw $this->createNotFoundException('The category does not exist');
        }

        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('admin_list_categories');
    }

    /**
     * Save the category entity using transaction
     * 
     * @param type $category
     * @throws \Exception
     */
    protected function saveCategoryTransaction($category)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $em->persist($category);
            $em->flush();
            $em->getConnection()->commit();  
        } catch (\Exception $exc) {
            $em->getConnection()->rollBack();
            throw $exc;
        }
    }
}
