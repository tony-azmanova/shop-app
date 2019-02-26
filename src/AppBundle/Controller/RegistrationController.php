<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User as User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends Controller
{

    /**
     * @Route("/register", methods="GET")
     * 
     */
    public function index()
    {
        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return new RedirectResponse('/');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        return $this->render(
            'register.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/register", methods="POST") 
     */
    public function registerationAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return new RedirectResponse('/');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->render(
              'register.html.twig',
              ['form' => $form->createView()]
            );
        }

        $this->encodePassword($user, $passwordEncoder);
        $user->setRoles(['ROLE_USER']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        $this->addFlash('success', 'Welcome '.$user->getUsername());

        return $this->get('security.authentication.guard_handler')
            ->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $this->get('app.security.login_form_authenticator'),
                'main'
            );
    }

    /**
     * Encode the password of the user and set the password in db
     * 
     * @param type $user
     * @param type $passwordEncoder
     */
    public function encodePassword($user, $passwordEncoder)
    {
        $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);
    }
}
