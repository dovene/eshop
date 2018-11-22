<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="category")
     */
    public function show(CategoryRepository $repo, Category $category = null, Request $request,
        ObjectManager $manager, PaginatorInterface $paginator) {

        //create a form for adding categorys
        if (!$category) {
            $category = new Category();
        }

        $categoryForm = $this->createForm(CategoryFormType::class, $category);

        //list all categorys
        //$categorys = $repo->findAll();

        $queryBuilder = $repo->createQueryBuilder('category');

        if ($request->query->getAlnum('filter')) {
            $queryBuilder
                ->where('category.title LIKE :title')
                ->setParameter('title', '%' . $request->query->getAlnum('filter') . '%');
        }

        $query = $queryBuilder->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1) /*page number*/,
            $request->query->getInt('limit', 3) /*limit per page*/
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('/category/category_table.html.twig', [
                'categories' => $pagination,
            ]);
        }

        return $this->render('/category/category.html.twig', [
            'categories' => $pagination,
            'categoryForm' => $categoryForm->createView(),
        ]);
    }



    /**
     * @Route("/category/create", name="category_create", condition="request.isXmlHttpRequest()")
     * @Route("/category/{id}/edit", name="category_edit", condition="request.isXmlHttpRequest()")
     */
    public function form(Category $category = null, Request $request,
        ObjectManager $manager) {

        if (!$category) {
            $category = new Category();
        }

        $categoryForm = $this->createForm(CategoryFormType::class, $category);

        $categoryForm->handleRequest($request);
        if ($categoryForm->isSubmitted() && $categoryForm->isValid()) {
            $manager->persist($category);
            $manager->flush();
            return $this->redirectToRoute('category');
        }

        return $this->render('/category/category_form.html.twig', array(
            'categoryForm' => $categoryForm->createView()
        ));
    }




     /**
     *
     * @Route("/category/{id}/delete", name="category_delete")
     */
    public function delete(Category $category, Request $request,
        ObjectManager $manager) {
        if ($category === null) {
            return $this->redirectToRoute('category');
        }
        $manager->remove($category);
        $manager->flush();
        return $this->redirectToRoute('category');
    }

}
