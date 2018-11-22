<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Form\PartnerFormType;
use App\Repository\PartnerRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Form;
use Knp\Component\Pager\Paginator;
use App\Controller\PartnerController;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;


abstract class PartnerController extends AbstractController
{
  

    protected abstract function getPartnerType();

     /**
     * @Route("/partner", name="partner")
     */
    public function show(PartnerRepository $repo, Partner $partner = null, Request $request,
        ObjectManager $manager, PaginatorInterface $paginator) {

            if (!$partner) {
                $partner = new Partner();
            }
    
            $formPartner = $this->createForm(PartnerFormType::class, $partner);

        $queryBuilder = $repo->createQueryBuilder('partner');
        $type = $this->getPartnerType();




        $parameters=array('type'=>$type);
        $queryBuilder
            ->where('partner.type = :type')
            ->orderBy('partner.name', 'ASC')
            ->setParameters($parameters);
       
        // request with filter    
        if($request->query->getAlnum('filter')){
            $parameters=array('name'=> '%' . $request->query->getAlnum('filter') . '%',
        'type'=>$type);
            $queryBuilder
            ->where('partner.name LIKE :name AND partner.type = :type')
            ->orderBy('partner.name', 'ASC')
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
            return $this->render('/partner/partner_table.html.twig', [
                'partners' => $pagination,
                'type' => $this->getPartnerType()
                
            ]);
        }

     
        return $this->render('/partner/partner.html.twig', [
            'partners' => $pagination,
           'formPartner' => $formPartner->createView(),
           'type' => $this->getPartnerType()
        ]);

    }

    /**
     * @Route("/partner/create", name="partner_create")
     * @Route("/partner/{id}/edit", name="partner_edit", condition="request.isXmlHttpRequest()")
     */
    public function form(Partner $partner = null, Request $request,
        ObjectManager $manager) {

        if (!$partner) {
            $partner = new Partner();
        }
    $form = $this->createForm(PartnerFormType::class, $partner);
    $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type = $this->getPartnerType();
            $partner->setType($type);
            $manager->persist($partner);
            $manager->flush();
            return $this->redirectToRoute('partner', array(
                'type' => $this->getPartnerType(),
            ));
        }

        return $this->render('/partner/partner_form.html.twig', array(
            'formPartner' => $form->createView(),
            'type' => $this->getPartnerType()
        ));
    }

    /**
     *
     * @Route("/partner/{id}/delete", name="partner_delete")
     */
    public function delete(Partner $partner, Request $request,
        ObjectManager $manager) {
        if ($partner === null) {
            return $this->redirectToRoute('partner', array(
                'type' => $this->getPartnerType(),
            ));
        }

        $manager->remove($partner);
        $manager->flush();

        return $this->redirectToRoute('partner', array(
            'type' => $this->getPartnerType(),
        ));

    }

     // Generate an array contains a key -> value with the errors where the key is the name of the form field
     protected function getErrorMessages(Form $form) 
     {
         $errors = array();
 
         foreach ($form->getErrors() as $key => $error) {
             $errors[] = $error->getMessage();
         }
 
         foreach ($form->all() as $child) {
             if (!$child->isValid()) {
                 $errors[$child->getName()] = $this->getErrorMessages($child);
             }
         }
 
         return $errors;
     }
}
