<?php

namespace App\Controller;

use App\Entity\Serie;
use App\Form\SerieType;
use App\Repository\SerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/series', name: 'serie_')]
class SerieController extends AbstractController
{
    #[Route('/{page}', name: 'list', requirements: ["page" => "\d+"])]
    public function list(SerieRepository $serieRepository, int $page = 1): Response
    {
        //TODO renvoyer la liste des series

        //$series = $serieRepository->findBy([], ["popularity" => "DESC"], 50);
        //$series=$serieRepository->findBestSeries();


        $nbSeries = $serieRepository->count([]);
        $maxPage = ceil($nbSeries / Serie::MAX_RESULT);

        //gestion page inférieur à 1
        if ($page < 1) {
            return $this->redirectToRoute('serie_list');
            //gestion page trop supérieure au max
        } elseif ($page > $maxPage) {

            return $this->redirectToRoute('serie_list', ['page' => $maxPage]);

        } else {

            $series = $serieRepository->findSeriesWithPagination($page);
            //dans le cas droit
            return $this->render('serie/list.html.twig', [
                'series' => $series,
                'currentPage' => $page,
                'maxPage' => $maxPage
            ]);
        }

        return $this->render('serie/list.html.twig', [
            'series' => $series,
            'currentPage' => $page,
            'maxPage' => $maxPage
        ]);
    }

    #[Route('/detail/{id}', name: 'show', requirements: ["id" => "\d+"])]
    public function show(int $id, SerieRepository $serieRepository): Response
    {
        //TODO renvoyer le détail d'une serie
        $serie = $serieRepository->find($id);

        if (!$serie) {
            //permet de lancer une erreur 404
            throw $this->createNotFoundException("Oops ! Serie not found!");
        }

        return $this->render('serie/show.html.twig', [
            'serie' => $serie
        ]);
    }

    #[Route('/add', name: 'add')]
    public function add(SerieRepository $serieRepository, Request $request): Response
    {
        //TODO renvoyer un formulaire pour ajouter une série
        $serie = new Serie();
        //instanciation du formulaire en lui passant l'instance de série
        $serieform = $this->createForm(SerieType::class, $serie);

        //permet d'extraire les données de la requête
        $serieform->handleRequest($request);

        if ($serieform->isSubmitted() && $serieform->isValid()) {

            //traitement de la donnée
            //$genres = $request->query->get('genres');
            $genres = $serieform->get('genres')->getData();
            $serie->setGenres(implode('/', $genres));
            $serie->setDateCreated(new \DateTime());

            //enregistre la serie en BDD
            $serieRepository->save($serie, true);

            //redirige vers la page de détail
            $this->addFlash('success', 'Serie added !');
            return $this->redirectToRoute('serie_show', ['id' => $serie->getId()]);

        }


        return $this->render('serie/add.html.twig', [
            'serieForm' => $serieform->createView()
        ]);
    }

    #[Route('/updade/{id}', name:'update', requirements:['id' => '\d+'])]
    public function update(int$id, SerieRepository $serieRepository){
        $serie = $serieRepository->find($id);

        $serieForm = $this->createForm(SerieType::class, $serie);

        return $this->render('serie/update.html.twig', [
            'serieForm'=>$serieForm->createView()
        ]);
    }


    #[Route('/delete/{id}', name:'delete', requirements:['id' => '\d+'])]
    public function delete(int$id, SerieRepository $serieRepository){

        $serie = $serieRepository->find($id);

        //suppression de la série
        $serieRepository->remove($serie,true);

        $this->addFlash('success', $serie->getName()." has been removed");

        return $this->redirectToRoute('main_home');

    }
}