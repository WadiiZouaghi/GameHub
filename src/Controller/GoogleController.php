<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connect(ClientRegistry $registry): RedirectResponse
    {
        return $registry->getClient('google')->redirect(['email', 'profile']);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheck(ClientRegistry $registry, EntityManagerInterface $em, Security $security): Response
    {
        try {
            $googleUser = $registry->getClient('google')->fetchUser();
            $email = $googleUser->getEmail();
            $googleId = $googleUser->getId();

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $user = (new User())
                    ->setEmail($email)
                    ->setUsername($googleUser->getName() ?? explode('@', $email)[0])
                    ->setGoogleId($googleId)
                    ->setRoles(['ROLE_USER'])
                    ->setPassword(bin2hex(random_bytes(32)));

                $em->persist($user);
            } elseif (!$user->getGoogleId()) {
                $user->setGoogleId($googleId);
            }

            $em->flush();
            $security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('app_home');

        } catch (IdentityProviderException $e) {
            $this->addFlash('error', 'Unable to authenticate with Google. Please try again.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'OAuth error: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_login');
    }
}
