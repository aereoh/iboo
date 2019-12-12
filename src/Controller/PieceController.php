<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\Piece;
use App\Entity\Machine;
use App\Form\PieceType;
use Symfony\Component\Security\Core\User\UserInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class PieceController extends AbstractController
{
    /**
     * @param Request $request
     * @param UserInterface $worker
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function create(Request $request, UserInterface $worker) {
        $piece = new Piece();

        $id = $request->get('id');
        $role = $request->get('role');

        $form = $this->createForm(PieceType::class, $piece, array(
            'user_id' => $id,
            'role' => $role
        ));

        //set the form data to the object
        $form->handleRequest($request);
        $machine = $form->get('machine')->getData();

        //Session flash
        $session = new Session();

        //check if the form is valid and has been sent
        if($form->isSubmitted() && $form->isValid()) {
            //Load entityManger
            $entityManager = $this->getDoctrine()->getManager();
            $date_now = new \DateTime('@'.strtotime('now'));
            $id = $machine->getId();
            $machine_id = $entityManager->getRepository(Machine::class)->find($id);
            $piece->setWorker($worker);
            $piece->setCreatedAt($date_now);
            $piece->setUpdatedAt($date_now);
            $piece->setMachine($machine_id);

            //Save object in Doctrine --> Persist
            $entityManager->persist($piece);
            $entityManager->flush();

            $session->getFlashBag()->add('message','The piece has been saved successfully');

            return $this->redirectToRoute('show_piece', ['page' => 1]);
        }

        return $this->render('includes/create.html.twig', [
            'title' => 'Create piece',
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
                ->select('p')
                ->from(Piece::class, 'p')
                ->orderBy('p.id', 'ASC');

            $numResults = $queryBuilder->getQuery()->getResult();

        } else {
            $queryBuilder = $entityManager->createQueryBuilder()
                ->select('p')
                ->from(Piece::class, 'p')
                ->where('p.worker = :id')
                ->setParameter('id', $id)
                ->orderBy('p.id', 'ASC');

            $numResults = $queryBuilder->getQuery()->getResult();
        }

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $maxPerPage = 5;
        $currentPage = $request->get('page');

        $pagerfanta->setMaxPerPage($maxPerPage)->setCurrentPage($currentPage);

        if(!$pagerfanta) {
            throw $this->createNotFoundException('No pieces found');
        }

        if(count($numResults) == 0) {
            //Session flash
            $session = new Session();
            $session->getFlashBag()->add('message', 'No pieces found');
        }

        return $this->render('piece/piece.html.twig', [
            'title' => 'Show piece',
            'my_pager' => $pagerfanta
        ]);
    }

    /**
     * @param UserInterface $worker
     * @param Piece $piece
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(UserInterface $worker, Piece $piece) {
        $role = $worker->getRole();
        $session = new Session();

        if($role) {
            try {
                $entityManager = $this->getDoctrine()->getManager();
                //Get the machine repository
                $piece_repo = $entityManager->getRepository(Piece::class);
                $piece = $piece_repo->find($piece);
                //Save object in Doctrine --> Persist
                $entityManager->remove($piece);
                $entityManager->flush();

                $session->getFlashBag()->add('message','The piece has been deleted successfully');
            } catch(\Exception $e) {
                $session->getFlashBag()->add('message',$e->getMessage());
            }

        } else {
            $session->getFlashBag()->add('message','You do not have permissions to delete this piece');
        }

        return $this->redirectToRoute('show_piece', ['page' => 1]);
    }
}
