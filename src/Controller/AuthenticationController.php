<?php

namespace App\Controller;

use App\Entity\User;
use App\Core\Notification;
use App\Core\NotificationColor;
use App\Form\ChangePasswordFormType;
use App\Form\RegistrationFormType;
use App\Form\UpdateInfoFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationController extends AbstractController
{
    #[Route('/auth', name: 'app_authentication')]
    public function index(): Response
    {
        if ($this->getUser() == null)
            return $this->redirectToRoute('app_login');
        else
            return $this->redirectToRoute('app_profile');
    }

    #[Route('/auth/register', name: 'app_registration')]
    public function registration(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security
    ): Response {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $security->login($user);

            return $this->redirectToRoute('app_authentication');
        } else
            $this->addFlash('registerNotification', new Notification('error', 'Unable to edit your profile, see errors below.', NotificationColor::DANGER));

        return $this->render('authentication/registration.html.twig', [
            "registrationForm" => $form
        ]);
    }

    #[Route('/auth/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser())
            return $this->redirectToRoute('app_profile');

        $notification = null;
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error != null && $error->getMessageKey() === 'Invalid credentials.') {
            $message = "Wrong email and password combination.";
            $notification = new Notification('error', $message, NotificationColor::WARNING);
        }

        return $this->render('authentication/login.html.twig', [
            'notification' => $notification
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $updateInfoForm = $this->createForm(UpdateInfoFormType::class, $user);
        $updateInfoForm->handleRequest($request);

        if ($updateInfoForm->isSubmitted() && $updateInfoForm->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('profileNotification', new Notification('success', 'Your profile has been modified', NotificationColor::INFO));
        } else if ($updateInfoForm->isSubmitted() && !$updateInfoForm->isValid())
            $this->addFlash('profileNotification', new Notification('error', 'Unable to edit your profile, see errors below.', NotificationColor::DANGER));

        $changePasswordForm = $this->createForm(ChangePasswordFormType::class);
        $changePasswordForm->handleRequest($request);
        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {

            if ($user->isNewPasswordValid($userPasswordHasher->hashPassword($user, $changePasswordForm["password"]->getData()), $changePasswordForm["newPassword"]->getData())) { // J'ai passer des heures a trouver pourquoi y'a du rouge... pour finalement comprendre que c'est intelephense 
                $user->setPassword($userPasswordHasher->hashPassword($user, $changePasswordForm->get('password')->getData()));
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('updatePasswordNotification', new Notification('success', 'Your password has been modified', NotificationColor::INFO));
            } else
                $this->addFlash('updatePasswordNotification', new Notification('error', 'Either your password is incorrect or your new password is identical to the current one', NotificationColor::DANGER));
        } else if ($changePasswordForm->isSubmitted() && !$changePasswordForm->isValid())
            $this->addFlash('updatePasswordNotification', new Notification('error', 'Unable to edit your password, see errors below.', NotificationColor::DANGER));

        return $this->render('profile/profile.html.twig', [
            "updateInfoForm" => $updateInfoForm,
            "changePasswordForm" => $changePasswordForm
        ]);
    }

    #[Route('/auth/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \Exception("Don't forget to activate logout in security.yaml");
    }
}
