<?php

namespace App\Controller;

use App\Entity\Customer;
use Psr\Log\LoggerInterface;
use App\Form\CustomerFormType;
use Knp\Component\Pager\Paginator;
use App\Repository\CustomerRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestsController extends AbstractController
{
   
    /**
     * @Route("/customer/new", name="customer_new")
     */

    public function formAjax(Request $request, ObjectManager $manager,
        Customer $customer = null, LoggerInterface $logger) {
        $logger->error('An error occurred ffrom formAjax');

        if (!$customer) {
            $customer = new Customer();
        }

        $form = $this->createForm(CustomerFormType::class, $customer, array(
            'action' => $this->generateUrl($request->get('_route')),
        ));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid() && $form->isSubmitted()) {
                $manager->persist($customer);
                $manager->flush();
                $logger->error('Ajax submit done');
                return $this->redirectToRoute('customer', array(
                    'id' => $customer->getId(),
                ));

            }
        }

        return $this->render('customer_form_ajax.html.twig', array(
            'formCustomer' => $form->createView(),
            'editMode' => $customer->getId() !== null,
        ));
    }

    /**
     * @Route("/customer/ajax", name="ajax")
     */
    public function ajaxAction(CustomerRepository $repo, Request $request, LoggerInterface $logger)
    {

        // $logger->info('I just got the logger');
        $logger->error('An error occurred on custom/ajax');

        $customers = $repo->findAll();

        if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {
            $jsonData = array();
            $idx = 0;
            foreach ($customers as $customer) {
                $temp = array(
                    'name' => $customer->getName(),
                    'address' => $customer->getAdress(),
                );
                $jsonData[$idx++] = $temp;
            }
            return new JsonResponse($jsonData);
        } else {
            return $this->render('/ajax.html.twig');
        }
    }
}
