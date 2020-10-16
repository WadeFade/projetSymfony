<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SerializerInterface $serializer)
    {

        $form = $this->createFormBuilder()
            ->add("nom", TextType::class, ['label' => "Nom"])
            ->add("prenom", TextType::class, ['label' => "Prenom"])
            ->add("depense", IntegerType::class, ['label' => "depense"])
            ->add("ajouter", SubmitType::class, ["label" => "Ajouter"])
            ->getForm();

//        $productSerialized = $serializer->serialize()

        return $this->render('home/index.html.twig', [
            'formulaire' => $form->createView(),
        ]);
    }
}
