<?php

namespace App\Controller;

use App\Entity\Training;
use App\Form\TrainingType;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/training')]
final class TrainingController extends AbstractController
{
    #[Route(name: 'app_training_index', methods: ['GET'])]
    public function index(TrainingRepository $trainingRepository): Response
    {
        return $this->render('training/index.html.twig', [
            'trainings' => $trainingRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_training_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $training = new Training();
        $form = $this->createForm(TrainingType::class, $training);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($training);
            $entityManager->flush();

            return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training/new.html.twig', [
            'training' => $training,
            'form' => $form,
        ]);
    }

    #[Route('/{code}', name: 'app_training_show', methods: ['GET'], requirements: ['code' => '.{3,4}'])]
    public function show(Training $training): Response
    {
        return $this->render('training/show.html.twig', [
            'training' => $training,
        ]);
    }

    #[Route('/{code}/edit', name: 'app_training_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Training $training, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TrainingType::class, $training);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training/edit.html.twig', [
            'training' => $training,
            'form' => $form,
        ]);
    }

    #[Route('/{code}', name: 'app_training_delete', methods: ['POST'])]
    public function delete(Request $request, Training $training, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$training->getCode(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($training);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/my_trainings', name: 'app_training_my_trainings', methods: ['GET'])]
    public function my_trainings(TrainingRepository $trainingRepository): Response
    {
        //Empêcher l'accès aux visiteurs non connectés
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        //Récupérer les formations auxquelles le membre est déjà inscrits
        $myTrainings = $trainingRepository->findByEmpNo($user->getId());    //dump($myTrainings);

        //Récupérer les formations disponibles (Il n'y est pas encore inscrit et il reste de la place)
        $availableTrainings = $trainingRepository->findAvailableTrainings($user->getId());  //dump($availableTrainings);

        $trainings = array_merge($myTrainings,$availableTrainings);    //dd($trainings);

        //Tri de la liste des formations par code
        usort($trainings, function ($a, $b)
        {
            if ($a['code'] == $b['code']) {
                return 0;
            }
            return ($a['code'] < $b['code']) ? -1 : 1;
        });

        $subscribableTraining = [];

        foreach($availableTrainings as $training) {
            $subscribableTraining[] = $training['code'];
        }

        return $this->render('training/my_trainings.html.twig', [
            'trainings' => $trainings,
            'subscribableTraining' => $subscribableTraining,
        ]);
    }

    #[Route('/{code}/subscribe', name: 'app_training_subscribe', methods: ['POST'])]
    public function subscribeForTraining(Request $request, Training $training, EntityManagerInterface $entityManager): Response
    {
        //Empêcher l'accès aux visiteurs non connectés
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        //Traiter l'action si la requête vient bien du formulaire de notre application (attaque CSRF)
        if ($this->isCsrfTokenValid('signin4training'.$training->getCode(), $request->get('_token'))) {
            //Inscrire
            $training->addEmployee($user);

            $entityManager->flush();

            $this->addFlash('notice','Inscription effectuée avec succès.');
        } else {
            $this->addFlash('notice','Une erreur est survenue lors de l\'inscription.');
        }

        return $this->redirectToRoute('app_training_my_trainings', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{code}/unsubscribe', name: 'app_training_unsubscribe', methods: ['POST'])]
    public function unsubscribeForTraining(Request $request, Training $training, EntityManagerInterface $entityManager): Response
    {
        //Empêcher l'accès aux visiteurs non connectés
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        //Traiter l'action si la requête vient bien du formulaire de notre application (attaque CSRF)
        if ($this->isCsrfTokenValid('signin4training'.$training->getCode(), $request->get('_token'))) {
            //Désinscrire
            $training->removeEmployee($user);

            $entityManager->flush();

            $this->addFlash('notice','Désinscription effectuée avec succès.');
        } else {
            $this->addFlash('notice','Une erreur est survenue lors de la désinscription.');
        }

        return $this->redirectToRoute('app_training_my_trainings', [], Response::HTTP_SEE_OTHER);
    }
}
