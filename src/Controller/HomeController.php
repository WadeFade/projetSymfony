<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists("../public/data/data.json")) {
            $finder = new Finder();
            $finder->files()->in('../public/data/');
            $counter = 0;
            foreach ($finder as $file) {
                $contents = $file->getContents();
                $counter++;
            }
            if ($counter > 1) {
                echo "Problem occurred";
                die;
            }
            $dataToShow = json_decode($contents, true);
        }

//TODO faire les calculs etc...

        return $this->render('home/index.html.twig', [
            'data' => $dataToShow,
        ]);
    }


    /**
     * @Route("/ajouter", name="ajouter_personne")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajouter(Request $request)
    {

        $form = $this->createFormBuilder()
            ->add("nom", TextType::class, ['label' => "Nom"])
            ->add("prenom", TextType::class, ['label' => "Prénom"])
            ->add("depense", IntegerType::class, ['label' => "Dépense"])
            ->add("ajouter", SubmitType::class, ["label" => "Ajouter"])
            ->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();


            $filesystem = new Filesystem();

            if (!$filesystem->exists("../public/data/data.json")) {
                $data_encoded = json_encode($data, true);
                $filesystem->dumpFile('../public/data/data.json', $data_encoded);

            } else {
                $finder = new Finder();
                $finder->files()->in("../public/data/");
                $counter = 0;
                foreach ($finder as $file) {
                    $contents = $file->getContents();
                    $dataDecoded = json_decode($contents, true);
                    $counter++;
                }

                if ($counter > 1) {
                    echo 'Problem occurred';
                    die;
                }

                $extra = array(
                    "nom" => $data['nom'],
                    "prenom" => $data['prenom'],
                    "depense" => $data['depense'],
                );
                $dataDecoded[] = $extra;
                $finalData = json_encode($dataDecoded, true);
                file_put_contents("../public/data/data.json", $finalData);
            }

        }


        return $this->render('home/ajouter.html.twig', [
            'formulaire' => $form->createView(),
        ]);
    }
}
