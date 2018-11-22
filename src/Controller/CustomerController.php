<?php

namespace App\Controller;

use App\Entity\Customer;
use Psr\Log\LoggerInterface;
use App\Form\CustomerFormType;
use Symfony\Component\Form\Form;
use Knp\Component\Pager\Paginator;
use App\Repository\CustomerRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CustomerController extends AbstractController
{
     /**
     * @Route("/customer", name="customer")
     */
    public function show(CustomerRepository $repo, Customer $customer = null, Request $request,
        ObjectManager $manager, PaginatorInterface $paginator) {

            if (!$customer) {
                $customer = new Customer();
            }
    
            $formCustomer = $this->createForm(CustomerFormType::class, $customer);

        $queryBuilder = $repo->createQueryBuilder('customer');

       
        $queryBuilder
            ->orderBy('customer.name', 'ASC');
       
        // request with filter    
        if($request->query->getAlnum('filter')){
            $parameters=array('name'=> '%' . $request->query->getAlnum('filter') . '%');
            $queryBuilder
            ->where('customer.name LIKE :name')
            ->orderBy('customer.name', 'ASC')
            ->setParameters($parameters);
        }
   
        $query = $queryBuilder->getQuery();
    
       // $paginator  = $this->getSubscribedServices('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $request->query->getInt('limit', 3)/*limit per page*/
        );
    
 
        if($request->isXmlHttpRequest()) {
            return $this->render('/customer/customer_table.html.twig', [
                'customers' => $pagination   
            ]);
        }

     
        return $this->render('/customer/customer.html.twig', [
            'customers' => $pagination,
           'formCustomer' => $formCustomer->createView()
        ]);

    }

    /**
     * @Route("/customer/create", name="customer_create")
     * @Route("/customer/{id}/edit", name="customer_edit", condition="request.isXmlHttpRequest()")
     */
    public function form(Customer $customer = null, Request $request,
        ObjectManager $manager) {

        if (!$customer) {
            $customer = new Customer();
        }
    $form = $this->createForm(CustomerFormType::class, $customer);
    $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($customer);
            $manager->flush();
            return $this->redirectToRoute('customer');
        }

        return $this->render('/customer/customer_form.html.twig', array(
            'formCustomer' => $form->createView()
        ));
    }

    /**
     *
     * @Route("/customer/{id}/delete", name="customer_delete")
     */
    public function delete(Customer $customer, Request $request,
        ObjectManager $manager) {
        if ($customer === null) {
            return $this->redirectToRoute('customer');
        }

        $manager->remove($customer);
        $manager->flush();

        return $this->redirectToRoute('customer');

    }

}
