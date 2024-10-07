<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

// #[Route('/api')]
class AuthController extends ApiController
{
    // register
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em)
    {
        $request = $this->transformJsonBody($request); 

        $user = new User();
        $user->setUsername($request->get('username')); 
        $user->setName($request->get('name'));
        $user->setSurname($request->get('surname'));
        $password = $passwordHasher->hashPassword($user, $request->get('password'));
        $user->setPassword($password);
        $user->setBloqued(false);
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);

        $em->flush();

        return $this->respondWithSuccess('Usuario creado correctamente');
        
    }

    
    public function getTokenUser(UserInterface $user, JWTTokenManagerInterface $JWTManager, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->findOneBy(array('username' => $user->getUserIdentifier()));
        return new JsonResponse(['token' => $JWTManager->create($user)]);
    }


}