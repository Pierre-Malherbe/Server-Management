<?php

namespace CustomRDBundle\Controller;

use RealDebrid\Auth\Token;
use RealDebrid\RealDebrid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $session = $this->get('session');

        $form = $this->createFormBuilder()
            ->add('debrid', SubmitType::class, array('label' => 'Débrider'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ( $session->get('token') ==! null)
            {
                return $this->redirectToRoute('link');
            } else
            {
                return $this->redirectToRoute('auth');
            }
        }

        return $this->render('CustomRDBundle:Default:index.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/auth", name="auth")
     */
    public function authAction(Request $request)
    {
        $session = new Session();

        $form = $this->createFormBuilder()
            ->add('token', TextType::class)
            ->add('validate', SubmitType::class, array('label' => 'Validate'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->getData('token') != null) {

            $session->set('token', $form->getData('token'));

            return $this->redirectToRoute('link');
        }

        return $this->render('CustomRDBundle:Default:auth.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/link", name="link")
     */
    public function linkAction(Request $request)
    {
        $session = $this->get('session');
        $tokenTest = $session->get('token');
        $token = new Token($tokenTest["token"]);
        $realDebrid = new RealDebrid($token);


        $form = $this->createFormBuilder()
            ->add('link', TextareaType::class)
            ->add('validate', SubmitType::class, array('label' => 'Validate'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->getData('link') != null) {

                $link = $form->getData('link');
                $link = $link["link"];

                $debrid = $realDebrid->unrestrict->link($link);

                return $this->render('CustomRDBundle:Default:decrypt.html.twig', array(
                    'link' => $debrid
                ));
            } else {
                return $this->redirectToRoute('link');
            }
        }

        return $this->render('CustomRDBundle:Default:link.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
