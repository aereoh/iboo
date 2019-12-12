<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\Machine;
use App\Form\MachineType;
use Symfony\Component\Security\Core\User\UserInterface;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

class MachineController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserInterface $worker
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function create(Request $request, UserInterface $worker) {

        //create form
        $machine = new Machine();

        $form = $this->createForm(MachineType::class, $machine);

        //set the form data to the object
        $form->handleRequest($request);

        //check if the form is valid and has been sent
        if($form->isSubmitted() && $form->isValid()) {
            //Load entityManger
            $entityManager = $this->getDoctrine()->getManager();

            $date_now = new \DateTime('@'.strtotime('now'));

            $machine->setWorker($worker);
            $machine->setCreatedAt($date_now);
            $machine->setUpdatedAt($date_now);

            //Save object in Doctrine --> Persist
            $entityManager->persist($machine);
            $entityManager->flush();

            //Session flash
            $session = new Session();
            $session->getFlashBag()->add('message','The machine has been saved successfully');

            return $this->redirectToRoute('show_machine', ['page' => 1]);
        }

        return $this->render('includes/create.html.twig', [
            'title' => 'Create machine',
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param UserInterface $worker
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request, UserInterface $worker) {
        $role = $worker->getRole();
        $id   = $worker->getId();
        $entityManager = $this->getDoctrine()->getManager();

        if($role == 'ROLE_ADMIN') {
            $queryBuilder = $entityManager->createQueryBuilder()
                ->select('m')
                ->from(Machine::class, 'm')
                ->orderBy('m.id', 'ASC');

            $numResults = $queryBuilder->getQuery()->getResult();

        } else {
            $queryBuilder = $entityManager->createQueryBuilder()
                ->select('m')
                ->from(Machine::class, 'm')
                ->where('m.worker = :id')
                ->setParameter('id', $id)
                ->orderBy('m.id', 'ASC');

            $numResults = $queryBuilder->getQuery()->getResult();
        }

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $maxPerPage = 5;
        $currentPage = $request->get('page');

        $pagerfanta->setMaxPerPage($maxPerPage)->setCurrentPage($currentPage);

        if(!$pagerfanta) {
            throw $this->createNotFoundException('No machines found');
        }

        if(count($numResults) == 0) {
            //Session flash
            $session = new Session();
            $session->getFlashBag()->add('message', 'No machines found');
        }

        return $this->render('machine/machine.html.twig', [
            'title' => 'Show machine',
            'my_pager' => $pagerfanta
        ]);
    }

    /**
     * @param UserInterface $worker
     * @param Machine $machine
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(UserInterface $worker, Machine $machine) {
        $role = $worker->getRole();
        //Session flash (para enviar mensaje (para enviar mensaj
        $session = new Session();

        if($role) {
            try {
                $entityManager = $this->getDoctrine()->getManager();
                //Get the machine repository
                $machine_repo = $entityManager->getRepository(Machine::class);
                //Get the machines
                $machine = $machine_repo->find($machine);
                //Save object in Doctrine --> Persist
                $entityManager->remove($machine);
                $entityManager->flush();

                $session->getFlashBag()->add('message','The machine has been deleted successfully');
            } catch(\Exception $e) {
                $session->getFlashBag()->add('message',$e->getMessage());
            }
        } else {
            $session->getFlashBag()->add('message','You do not have permissions to delete this machine');
        }

        return $this->redirectToRoute('show_machine', ['page' => 1]);
    }

    /**
     * @param Request $request
     * @param UserInterface $worker
     * @param Machine $machine
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function edit(Request $request, UserInterface $worker, Machine $machine) {
        $role = $worker->getRole();
        //Session flash
        $session = new Session();

        if($role) {
            $form = $this->createForm(MachineType::class, $machine);

            //set the form data to the object
            $form->handleRequest($request);

            //check if the form is valid and has been sent
            if ($form->isSubmitted() && $form->isValid()) {
                //Load entityManger
                $entityManager = $this->getDoctrine()->getManager();
                $date_now = new \DateTime('@' . strtotime('now'));
                $machine->setUpdatedAt($date_now);
                //Save object in Doctrine --> Persist
                $entityManager->persist($machine);
                $entityManager->flush();

                $session->getFlashBag()->add('message', 'The machine has been updated successfully');

                //para reiniciar/vaciar el formulario
                return $this->redirectToRoute('show_machine', ['page' => 1]);
            }
        } else {
            $session->getFlashBag()->add('message','You do not have permissions to edit this machine');

            return $this->redirectToRoute('show_machine', ['page' => 1]);
        }

        return $this->render('includes/create.html.twig', [
            'title' => 'Edit machine',
            'form' => $form->createView(),
        ]);
    }
}
