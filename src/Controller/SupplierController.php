<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Entity\Supplier;
use App\Form\SupplierFormType;
use App\Controller\PartnerController;
use App\Repository\PartnerRepository;
use App\Repository\SupplierRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SupplierController  extends PartnerController
{
    protected function getPartnerType(){
        return "SUPPLIER";
    }

        /**
     * @Route("/supplier", name="supplier")
     */
    public function index(PartnerRepository $repo, Partner $partner = null, Request $request,
        ObjectManager $manager, PaginatorInterface $paginator) {
            return PartnerController::show($repo,$partner,$request,$manager, $paginator);
        } 

}
