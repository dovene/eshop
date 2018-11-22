<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
 
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('welcome/welcome.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}