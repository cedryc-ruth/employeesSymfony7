<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\Project;
use App\Form\EmployeeType;
use App\Repository\EmployeeRepository;
use App\Repository\ProjectRepository;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/employee')]
class EmployeeController extends AbstractController
{
    #[Route('/', name: 'app_employee_index', methods: ['GET'])]
    public function index(EmployeeRepository $employeeRepository): Response
    {
        return $this->render('employee/index.html.twig', [
            'employees' => $employeeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_employee_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($employee);
            $entityManager->flush();

            return $this->redirectToRoute('app_employee_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('employee/new.html.twig', [
            'employee' => $employee,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_employee_show', methods: ['GET'])]
    public function show(Employee $employee, GroupRepository $groupRepo, Request $request): Response
    {  
        //Sécuriser l'accès à la fonctionnalité
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        //Autorisation d'accès au profil personnel seulement SAUF si on est administrateur
        if(!$this->getUser()->isAdmin() && $employee->getId() != $this->getUser()->getId()) {
            //Afficher un message de notification
            $this->addFlash('notice','Vous ne pouvez accéder qu\'à votre propre profil.');

            return $this->redirectToRoute('app_employee_show', ["id" => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

        //Récupérer les groupes qui ne sont pas remplis
        $availableGroups = $groupRepo->findAvailableGroups();
//dd($availableGroups);
        return $this->render('employee/show.html.twig', [
            'employee' => $employee,
            'availableGroups' => $availableGroups,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_employee_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Employee $employee, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_employee_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('employee/edit.html.twig', [
            'employee' => $employee,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_employee_delete', methods: ['POST'])]
    public function delete(Request $request, Employee $employee, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$employee->getId(), $request->request->get('_token'))) {
            $entityManager->remove($employee);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_employee_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/join', name: 'app_employee_join', methods: ['POST'])]
    public function joinGroup(Request $request, Employee $employee, EntityManagerInterface $entityManager, GroupRepository $groupRepo): Response
    {
        //Sécuriser l'accès à la fonctionnalité
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        //Autorisation d'accès au profil personnel seulement
        if($employee->getId() != $this->getUser()->getId()) {
            //Afficher un message de notification
            $this->addFlash('notice','Vous ne pouvez modifier que les données de votre propre profil.');

            return $this->redirectToRoute('app_employee_show', ["id" => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

        //Récupérer le code du groupe sélectionné
        $groupCode = $request->get('groupId');

        //Récupérer le groupe
        $group = $groupRepo->find($groupCode);

        //Vérifier si le group est plein ou pas
        $isAvailable = sizeof($group->getEmployees()->toArray()) < 3;

        if($isAvailable) {
            //Affecter le membre au groupe
            //$employee->setGroup($group);      //INCORRECT
            $group->addEmployee($employee);

            //Persister les modifications
            $entityManager->flush();

            //Afficher un message de notification
            $this->addFlash('notice','Membre affecté au groupe.');
        } else {
            //Afficher un message de notification
            $this->addFlash('notice','Ce groupe est déjà plein. Membre non affecté au groupe.');
        }

        //Rediriger vers la page de profil
        return $this->redirectToRoute('app_employee_show', ["id" => $employee->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/leave', name: 'app_employee_leave', methods: ['POST'])]
    public function leaveGroup(Request $request, Employee $employee, EntityManagerInterface $entityManager): Response
    {
        //Sécuriser l'accès à la fonctionnalité
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        //Autorisation d'accès au profil personnel seulement
        if($employee->getId() != $this->getUser()->getId()) {
            //Afficher un message de notification
            $this->addFlash('notice','Vous ne pouvez modifier que les données de votre propre profil.');

            return $this->redirectToRoute('app_employee_show', ["id" => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }
        
        //Retirer le membre du groupe
        //$employee->setGroup(null);      //INCORRECT

        $employee->getGroup()->removeEmployee($employee);

        //Persister les modifications
        $entityManager->flush();

        //Afficher un message de notification
        $this->addFlash('notice','Membre retiré du groupe.');

        //Rediriger vers la page de profil
        return $this->redirectToRoute('app_employee_show', ["id" => $employee->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/project_leave', name: 'app_employee_project_leave', methods: ['POST'])]
    public function projectLeave(Request $request, Employee $employee, ProjectRepository $projectRepo, EntityManagerInterface $entityManager): Response
    {
        //Sécuriser l'accès à la fonctionnalité
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');   //Seulement les membres connectés

        //Récupérer la value du champ de formulaire nommé 'projectId' (<button name="projectId" value="...">)
        $projectId = $request->get('projectId');

        //Récupérer le projet
        $project = $projectRepo->find($projectId);      //dd($project);

        //Autorisation d'accès : seul l'administrateur ou le leader du projet peut retirer des membres
        if(!$this->getUser()->isAdmin() && $this->getUser() != $project->getLeader()) {
            //Afficher un message de notification
            $this->addFlash('notice','Vous devez être chef de projet pour vous retirer du groupe.');

            return $this->redirectToRoute('app_employee_show', ["id" => $this->getUser()->getId()], Response::HTTP_SEE_OTHER);
        }

        //Retirer l'employé du projet
        $project->removeEmployee($employee);        //L'admin ou le chef de projet le retire du projet

        if($employee == $project->getLeader()) {    //Si l'employé était aussi le chef de projet
            $project->setLeader(null);              //Il n'est plus chef de projet
        }

        //Persister les modifications
        $entityManager->flush();

        //Afficher un message de notification
        $this->addFlash('notice','Membre retiré du projet.');

        //Rediriger vers la page de profil
        return $this->redirectToRoute('app_employee_show', ["id" => $employee->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/project_join', name: 'app_employee_project_join', methods: ['POST'])]
    public function projectJoin(Request $request, Employee $employee, ProjectRepository $projectRepo, EntityManagerInterface $entityManager): Response
    {
        //Récupérer la value du champ de formulaire nommé 'projectId' (<button name="projectId" value="...">)
        $projectId = $request->get('projectId');

        //Récupérer le projet
        $project = $projectRepo->find($projectId);      //dd($project);

        //Ajouter l'employé qu projet
        $project->addEmployee($employee);

        //Persister les modifications
        $entityManager->flush();

        //Afficher un message de notification
        $this->addFlash('notice','Membre ajouté au projet.');

        //Rediriger vers la page du projet
        return $this->redirectToRoute('app_project_show', ["id" => $project->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/project_leader', name: 'app_employee_project_leader', methods: ['POST'])]
    public function projectLeader(Request $request, Employee $employee, ProjectRepository $projectRepo, EntityManagerInterface $entityManager): Response
    {
        //Sécuriser l'accès à la fonctionnalité
        $this->denyAccessUnlessGranted('ROLE_ADMIN');   //Seulement l'administrateur

        //Récupérer la value du champ de formulaire nommé 'projectId' (<button name="projectId" value="...">)
        $projectId = $request->get('projectId');

        //Récupérer le projet
        $project = $projectRepo->find($projectId);      //dd($project);

        //Nommer l'employé chef du projet
        $project->setLeader($employee);             //Il devient chef de projet
        $project->addEmployee($employee);           //Il est ajouté au projet

        //Persister les modifications
        $entityManager->flush();

        //Afficher un message de notification
        $this->addFlash('notice','Membre ajouté et nommé chef du projet.');

        //Rediriger vers la page de profil
        return $this->redirectToRoute('app_employee_show', ["id" => $employee->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/project_add', name: 'app_employee_project_add', methods: ['POST'])]
    public function projectAdd(Request $request, Project $project, EmployeeRepository $empRepo, EntityManagerInterface $entityManager): Response
    {
        //Sécuriser l'accès à la fonctionnalité
        $this->denyAccessUnlessGranted('ROLE_ADMIN');   //Seulement l'administrateur

        //Récupérer la value du champ de formulaire nommé 'projectId' (<button name="projectId" value="...">)
        $employeeId = $request->get('employeeId');

        //Récupérer l'employé
        $employee = $empRepo->find($employeeId);      //dd($employee);

        //Ajouter l'employé au projet
        $project->addEmployee($employee);           //Il est ajouté au projet

        //Persister les modifications
        $entityManager->flush();

        //Afficher un message de notification
        $this->addFlash('notice','Membre ajouté au projet.');

        //Rediriger vers la page de profil
        return $this->redirectToRoute('app_project_show', ["id" => $project->getId()], Response::HTTP_SEE_OTHER);
    }

}
