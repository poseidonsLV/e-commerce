<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response{
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepository): Response {
        if ($request->isMethod('POST')) {

            $user = new User();
            $username = $request->get('username');
            $password = $request->get('password');
            $rPassword = $request->get('repeat-password');
            $status = $this->checkCredentials($password,$rPassword, $username, $userRepository);

            if ($status === true) {
                
                //  encode password
                $encodedPsw = $passwordEncoder->encodePassword($user, $password);
                    
                //  Set data which we will send to DB
                $user->setUsername($username);
                $user->setPassword($encodedPsw);
                $user->setAdmin(0);
                    
                //  Send data to DB
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                    
                //  redirect to login page after successful register
                return $this->redirectToRoute('app_login');
                    
            }
        };
        
        return $this->render('security/register.html.twig');
    }


    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    
    protected function checkCredentials($password, $repeatedPassword, $username, $userRepository) {
        $err = array();
        if ($password !== $repeatedPassword) {
            $this->addFlash('error', 'Passwords does not match');
            array_push($err, 'error');
        }
        if (strlen($password) < 8) {
            $this->addFlash('error', 'Password too short');
            array_push($err, 'error');
        }
        if (strlen($username) === 0) {
            $this->addFlash('error', 'Username can not be blank');
            array_push($err, 'error');
        }
        if (count($userRepository->doUsernameExists($username)) > 0) {
            $this->addFlash('error', 'Username already exists');
            array_push($err, 'error');
        }
        if (count($err) > 0) {
            return false;
        }  else {
            return true;
        }
    }
}
