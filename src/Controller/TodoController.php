<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TodoController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(Request $request, EntityManagerInterface $entityManager)
    {
        $user = new User();

        $userRepository = $this->getDoctrine()
        ->getRepository(User::class)
        ->findAll();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $user = $form->getData();
            $user->setCreatedAt(new \DateTime());

            $image = $user->getImage();
            $imageName = md5(uniqid()).'.'.$image->guessExtension();
            $image->move($this->getParameter('upload_files') ,
            $imageName);
            $user ->setImage($imageName);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->redirectToRoute('index');
        }
        return $this->render('todo/index.html.twig', [
           'users' => $userRepository,
           'formUser' => $form->createView()

        ]);
    }

    /**
     * @Route("/singleUser/{id}", name="singleUser")
     */
    public function singleUser($id, Request $request, EntityManagerInterface $entityManager){

        $user = $this->getDoctrine()
        ->getRepository(User::class)
        ->find($id);

        $user->setImage(new File($this->getParameter('upload_files').'/'.$user->getImage()));
        
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $user = $form->getData();

            $image = $user->getImage();
            $imageName = md5(uniqid()).'.'.$image->guessExtension();
            $image->move($this->getParameter('upload_files') ,
            $imageName);
            $user ->setImage($imageName);


            $entityManager->persist($user);
            $entityManager->flush();

            $this->redirectToRoute('index');
        }
        return $this->render('todo/singleUser.html.twig', [
           'user' => $user,
           'formUser' => $form->createView()

        ]);
    }

    /**
     * @Route("/tasks", name="tasks")
     */
    public function tasks (Request $request, EntityManagerInterface $entityManager)
    {
        $task = new Task();

        $taskRepository = $this->getDoctrine()
        ->getRepository(Task::class)
        ->findAll();

        $users = $this->getDoctrine()
        ->getRepository(User::class)
        ->findAll();

        $formTask = $this->createForm(TaskType::class, $task);
        $formTask->handleRequest($request);

        if ($formTask->isSubmitted() && $formTask->isValid()){

            $task = $formTask->getData();
            $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->find($request->request->get('userId'));
            $task->setUserId($user);
            $entityManager->persist($task);
            $entityManager->flush();

            $this->redirectToRoute('tasks');
        }

        return $this->render('todo/tasks.html.twig', [
           'tasks' => $taskRepository,
           'taskForm' => $formTask->createView(),
           'users' => $users
        ]);
    }

    /**
     * @Route("/task/remove/{id}", name="remove")
     */
    public function removeTask($id, EntityManagerInterface $entityManager){
        $task = $this->getDoctrine()->getRepository(Task::class)->find($id);

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->redirectToRoute('index');
    }

        /**
     * @Route("/task/validation/{id}", name="validation")
     */
    public function validateTask($id, EntityManagerInterface $entityManager){
        $task = $this->getDoctrine()->getRepository(Task::class)->find($id);
        $task->setEtat(true);

        $entityManager->persist($task);
        $entityManager->flush();

        return $this->redirectToRoute('index');
    }

    /**
     * @Route("/singleTask/{id}", name="singleTask")
     */
    public function singleTask($id, Request $request, EntityManagerInterface $entityManager){
        $task = $this->getDoctrine()->getRepository(Task::class)->find($id);

        $users = $this->getDoctrine()
        ->getRepository(User::class)
        ->findAll();

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            $task = $form->getData();
            $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->find($request->request->get('userId'));
            $task->setUserId($user);
            $entityManager->persist($task);
            $entityManager->flush();

            $this->redirectToRoute('tasks');
        }
        return $this->render('todo/singleTask.html.twig', [
            'taskForm' => $form->createView(),
            'users' => $users
         ]);
    }
}
