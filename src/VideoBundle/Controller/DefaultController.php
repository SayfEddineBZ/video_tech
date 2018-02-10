<?php

namespace VideoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use VideoBundle\Entity\Film;
use VideoBundle\Form\FilmType;


class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('VideoBundle:Default:index.html.twig');
    }

    public function newFilmAction(Request $request)
    {
        $film = new Film();
        $form = $this->createForm(FilmType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $film->getPhoto();
            $photoName = md5(uniqid()) . '.' . $photo->guessExtension();
            $photo->move(
                $this->get('kernel')->getProjectDir() . '/web/uploads/photos',
                $photoName
            );
            $film->setPhoto($photoName);
            $film->setDatecreat(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($film);
            $em->flush();
            $message="Un film a été ajouté dans votre application ";
            $subject="Ajout d'un film";
            $this->sendMail($subject,$message);
            $request->getSession()
                ->getFlashBag()
                ->add('notice', 'Film enregistré avec succès');
        }

        return $this->render('VideoBundle:Default:newFilm.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function editFilmAction(Request $request, $id)
    {
        $film = $this->getDoctrine()
            ->getRepository(Film::class)
            ->find($id);
        $form = $this->createForm(FilmType::class, $film);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $film->getPhoto();
            $photoName = md5(uniqid()) . '.' . $photo->guessExtension();
            $photo->move(
                $this->get('kernel')->getProjectDir() . '/web/uploads/photos',
                $photoName
            );
            $film->setPhoto($photoName);
            $em = $this->getDoctrine()->getManager();
            $em->persist($film);
            $em->flush();
            $request->getSession()
                ->getFlashBag()
                ->add('notice', 'Film modifié avec succès');
        }

        return $this->render('VideoBundle:Default:editFilm.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function deleteFilmAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $film = $em->getRepository('VideoBundle:Film')
            ->find($id);
        $category = $film->getCategorie();
        $filename = $this->get('kernel')->getProjectDir() . '/web/uploads/photos/' . $film->getPhoto();
        $filesystem = new Filesystem();
        $filesystem->remove($filename);
        $em->remove($film);
        $em->flush();
        $message="Un film a été supprimé dans votre application ";
        $subject="Suppression d'un film";
        $this->sendMail($subject,$message);
        $request->getSession()
            ->getFlashBag()
            ->add('notice', 'Film supprimé avec succès');
        return $this->redirectToRoute('film_list', array('category' => $category->getId()));
    }

    public function listFilmAction(Request $request, $category)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $dql = "SELECT a FROM VideoBundle:Film a WHERE a.categorie=".$category;
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), 10
        );


        return $this->render('VideoBundle:Default:listFilm.html.twig', array(
            'pagination' => $pagination
        ));

    }

    public function showFilmAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $film = $em->getRepository('VideoBundle:Film')
            ->find($id);

        return $this->render('VideoBundle:Default:showFilm.html.twig', array(
            'film' => $film,
        ));

    }

    public function sendMail($subject,$message){
        $message = (new \Swift_Message($subject))
            ->setFrom('video@dnl.com')
            ->setTo($this->container->getParameter('mailer_sender'))
            ->setBody(
                $message,
                'text/html'
            )
        ;

        $this->get('mailer')->send($message);

    }

    public function searchFilmAction(Request $request)
    {
        $titre=$request->request->get('titre');
        $categorie=$request->request->get('categorie');
        $datecreat=$request->request->get('datecreat');

        $conditions=array();
        if($titre)
            $conditions['titre']=$titre;
        if($categorie)
            $conditions['categorie']=$categorie;
        if($datecreat){
            $conditions['datecreat']=new \DateTime($datecreat);
        }

        if(sizeof($conditions)==0)
            $films=array();
        else{
            $em = $this->getDoctrine()->getManager();
            $films = $em->getRepository('VideoBundle:Film')
                ->findBy($conditions);
        }

        return $this->render('VideoBundle:Default:searchFilm.html.twig', array(
            'films' => $films
        ));

    }



}
