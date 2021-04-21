<?php

namespace App\Controller;

use App\Entity\CategoryTresorerie;
use App\Form\CategoryTresorerieType;
use App\Entity\SousCategorieTresorerie;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\SousCategorieTresorerieType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryTresorerieRepository;
use App\Repository\SousCategorieTresorerieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    /**
     * @Route("/category", name="category")
     */
    public function index(Request $request, CategoryTresorerieRepository $repoCate, SousCategorieTresorerieRepository $repoSousCate): Response
    {
        $cate = new CategoryTresorerie();
        $sous_cate = new SousCategorieTresorerie();
        $form_sous_cate = $this->createForm(SousCategorieTresorerieType::class, $sous_cate);
        $form_cate = $this->createForm(CategoryTresorerieType::class, $cate);
        $form_cate->handleRequest($request);
        $form_sous_cate->handleRequest($request);
        if($form_cate->isSubmitted() && $form_cate->isValid()){
            $cate = $form_cate->getData();
            $exist = $repoCate->findOneByNom($form_cate->get('nom')->getData());
           if($exist){
                return $this->render('category/index.html.twig', [
                    'form_cate' => $form_cate->createView(),
                    'form_sous_cate' => $form_sous_cate->createView(),
                ]); 
           }
           else{
            $this->em->persist($cate);
            $this->em->flush();
           }
           
        }
        if($form_sous_cate->isSubmitted() && $form_sous_cate->isValid()){
            $sous_cate = $form_sous_cate->getData();
            $exist = $repoSousCate->findOneByNom($form_sous_cate->get('nom')->getData());
            if($exist){
                return $this->render('category/index.html.twig', [
                    'form_cate' => $form_cate->createView(),
                    'form_sous_cate' => $form_sous_cate->createView(),
                ]); 
            }else{
                $this->em->persist($sous_cate);
                $this->em->flush();
            }
        }
        return $this->render('category/index.html.twig', [
            'form_cate' => $form_cate->createView(),
            'form_sous_cate' => $form_sous_cate->createView(),
        ]);
    }
}
