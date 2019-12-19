<?php

namespace App\Controller;

use App\Entity\Worker;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Form\RegisterType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class WorkerController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     * @throws \Exception
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $worker = new worker();

        //create the registration form
        $form = $this->createForm(RegisterType::class, $worker);

        //set the form values ​​to the worker object
        $form->handleRequest($request);

        //check if the form is valid and has been sent
        if ($form->isSubmitted() && $form->isValid()) {

            //Load entityManager
            $entityManager = $this->getDoctrine()->getManager();

            $date_now = new \DateTime('@' . strtotime('now'));

            $encoded = $encoder->encodePassword($worker, $worker->getPassword());
            $worker->setPassword($encoded);
            $worker->setCreatedAt($date_now);
            $worker->setUpdatedAt($date_now);

            //Session flash
            $session = new Session();

            try {
                //save object in Doctrine --> Persist....
                $entityManager->persist($worker);
                $entityManager->flush();

                $session->getFlashBag()->add('message', 'The worker has been saved successfully');
            } catch (UniqueConstraintViolationException $e) {
                $session->getFlashBag()->add('message', 'This email already exists.');
            }
        }

        return $this->render('worker/register.html.twig', [
            'title' => 'Register worker',
            'form' => $form->createView()
        ]);
    }

    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        //las username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('worker/login.html.twig', [
            'title' => 'Login Worker',
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @return Response
     */
    public function index()
    {
        return $this->render('worker/index.html.twig', [
            'title' => 'Worker panel',
        ]);
    }

    /**
     * @param Request $request
     * @param UserInterface $worker
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function edit(Request $request, UserInterface $worker, UserPasswordEncoderInterface $encoder) {
        $role = $worker->getRole();
        $id_loged = $worker->getId();
        $id_request = $request->get('id');

        $entityManager = $this->getDoctrine()->getManager();

        if($id_loged == $id_request) {
            $form = $this->createForm(RegisterType::class, $worker);

            //set the form data to the object
            $form->handleRequest($request);

        } else {
            //Get the worker repository
            $worker_repo = $entityManager->getRepository(Worker::class);
            $worker = $worker_repo->find($id_request);
            $form = $this->createForm(RegisterType::class, $worker);

            //set the form data to the object
            $form->handleRequest($request);
        }

        //check if the form is valid and has been sent
        if($form->isSubmitted() && $form->isValid()) {
            $date_now = new \DateTime('@'.strtotime('now'));
            $encoded = $encoder->encodePassword($worker, $worker->getPassword());

            $worker->setRole($role);
            $worker->setPassword($encoded);
            $worker->setUpdatedAt($date_now);
            //Save object in Doctrine --> Persist
            $entityManager->persist($worker);
            $entityManager->flush();

            //Session flash
            $session = new Session();
            $session->getFlashBag()->add('message','The worker has been updated successfully');

            //para reiniciar/vaciar el formulario
            return $this->redirectToRoute('profile', ['id' => $worker->getId()]);
        }

        return $this->render('worker/profile.html.twig', [
            'title' => 'Edit worker',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param UserInterface $worker
     * @return Response
     */
    public function show(Request $request, UserInterface $worker) {
        $role = $worker->getRole();
        $id   = $worker->getId();
        $machines = $worker->getMachines();
        $entityManager = $this->getDoctrine()->getManager();

        if($role == 'ROLE_ADMIN') {
            $queryBuilder = $entityManager->createQueryBuilder()
                ->select('w')
                ->from(Worker::class, 'w')
                ->orderBy('w.id', 'ASC');
            $numResults = $queryBuilder->getQuery()->getResult();
        } else {
            $queryBuilder = $entityManager->createQueryBuilder()
                ->select('w')
                ->from(Worker::class, 'w')
                ->where('w.id = :id')
                ->setParameter('id', $id);

            $numResults = $queryBuilder->getQuery()->getResult();
        }

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $maxPerPage = 5;
        $currentPage = $request->get('page');

        $pagerfanta->setMaxPerPage($maxPerPage)->setCurrentPage($currentPage);

        if(!$pagerfanta) {
            throw $this->createNotFoundException('No posts found');
        }

        if(count($numResults) == 0) {
            $session = new Session();
            $session->getFlashBag()->add('message', 'No workers found');
        }

        return $this->render('worker/worker.html.twig', [
            'title' => 'Show worker',
            'my_pager' => $pagerfanta,
            'machines' => $machines
        ]);
    }

    /**
     * @param Request $request
     * @param UserInterface $worker
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Request $request, UserInterface $worker) {
        $role = $worker->getRole();
        $id = $request->get('id');
        //Session flash
        $session = new Session();

        if($role == 'ROLE_ADMIN') {
            try {
                $entityManager = $this->getDoctrine()->getManager();

                //Get the machine repository
                $worker_repo = $entityManager->getRepository(Worker::class);
                $worker = $worker_repo->find($id);

                //Save object in Doctrine --> Persist
                $entityManager->remove($worker);
                $entityManager->flush();

                $session->getFlashBag()->add('message','The worker has been deleted successfully');
            } catch(\Exception $e) {
                $session->getFlashBag()->add('message',$e->getMessage());
            }
        } else {
            $session->getFlashBag()->add('message','You do not have permissions to delete this worker');
        }

        return $this->redirectToRoute('show_workers', ['page' => 1]);
    }

    public function testv2()
    {

    }
}
