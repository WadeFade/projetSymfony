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

        if (!is_null($dataToShow)) {
            $i = 0;
            $somme = 0;
            foreach ($dataToShow as $arr) {
                $somme += $arr['depense'];
                $i++;
            }
            $equilibre = $somme / $i;

            $j = 0;
            foreach ($dataToShow as $arr) {
                $doit = $equilibre - $arr['depense'];
                $dataToShow[$j]['doit'] = $doit;
                $j++;
            }
            $dataEncoded = json_encode($dataToShow, true);
            file_put_contents("../public/data/data.json", $dataEncoded);
        }

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
                if (!is_null($dataDecoded)) {
                    $dataReform = array_values($dataDecoded);
                    $dataReform[] = $extra;
                    $finalData = json_encode($dataReform, true);

                } else {
                    $dataDecoded[] = $extra;
                    $finalData = json_encode($dataDecoded, true);
                }
                file_put_contents("../public/data/data.json", $finalData);
            }
            return $this->redirectToRoute("home");
        }


        return $this->render('home/ajouter.html.twig', [
            'formulaire' => $form->createView(),
        ]);
    }

    /**
     * @Route("/supprimer/{nom}-{prenom}", name="supprimer_personne")
     * @param $nom
     * @param $prenom
     * @param Request $request
     * @param $dataDecoded
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function supprimer($nom, $prenom, Request $request)
    {

        $form = $this->createFormBuilder()
            ->add("ok", SubmitType::class, ["label" => "Supprimer"])
            ->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            $contents = file_get_contents("../public/data/data.json");
            $dataDecoded = json_decode($contents, true);
            $dataReform = array_values($dataDecoded);

            $key = array_search($prenom, array_column($dataReform, 'prenom'));
            $key2 = array_search($nom, array_column($dataReform, 'nom'));


            if ($key === $key2) {
                unset($dataReform[$key]);
            } else {
                echo 'Person not found';
            }

            $finalData = json_encode($dataReform, true);
            file_put_contents("../public/data/data.json", $finalData);

            return $this->redirectToRoute("home");
        }

        return $this->render('home/supprimer.html.twig', [
            'formulaire' => $form->createView(),
            'nom' => $nom,
            'prenom' => $prenom,
        ]);
    }


}
