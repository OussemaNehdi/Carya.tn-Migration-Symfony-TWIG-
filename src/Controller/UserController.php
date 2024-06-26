<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Cars;
use App\Entity\Commands;
use App\Form\LoginType;
use App\Form\SignupType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\SecurityBundle\Security;
use App\Form\ProfileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use TCPDF;
use Symfony\Component\HttpFoundation\RedirectResponse; 
use App\Form\CarType;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;

class UserController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        // Fetch data from the database using injected EntityManager
        $users = $entityManager->getRepository(Users::class)->findAll();
        $cars = $entityManager->getRepository(Cars::class)->findAll();
        $commands = $entityManager->getRepository(Commands::class)->findAll();
        $car = new Cars();
        $form = $this->createForm(CarType::class, $car);

        // Debugging: Check if data is fetched
        if (!$users || !$cars || !$commands) {
            throw $this->createNotFoundException('No data found.');
        }

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'cars' => $cars,
            'commands' => $commands,
            'bodyclass' => 'admin-dashboard',
            'form' => $form->createView()
        ]);
    }

    
    #[Route('/login', name: 'login')]
    public function login(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, AuthenticationUtils $authenticationUtils, SessionInterface $session, LoggerInterface $logger): Response
    {
        // Check for any authentication errors from previous login attempts
        $error = $authenticationUtils->getLastAuthenticationError();
        // Get the last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        $loginForm = $this->createForm(LoginType::class);
        $loginForm->handleRequest($request);
        
        
        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $formData = $loginForm->getData();
            
            $email = $formData['_username'];

            $password = $formData['_password'];
        
            $user = $entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);
            
            if ($user && $passwordHasher->isPasswordValid($user, $password)) {
                if ($user->isBanned()) {
                    $this->addFlash('error', 'Your account has been banned.');
                    return $this->redirectToRoute('login');
                }
                $session->set('user_first_name', $user->getFirstName());
                $session->set('user_id', $user->getId());

                $this->security->login($user);

                $rememberMe = $request->request->get('_remember_me');
                // This doesnt want to work for some reason, it's too advanced for me to fix it 
                // if ($rememberMe) {
                //     // Create a remember-me cookie
                //     $token = $this->container->get('security.token_storage')->getToken();
                //     $this->container->get('security.token_storage')->setToken($token);

                //     // Send remember-me cookie to the user
                //     $response = new Response();
                //     $rememberMeService = $this->container->get('security.authentication.rememberme.services.persistent.remember_me');
                //     $rememberMeService->loginSuccess($request, $response, $token);
                // }
                $this->addFlash('success', 'Welcome to Carya, Dear '. $user->getFirstName());
                return $this->redirectToRoute('my_cars');
            } else {
                // Handle invalid credentials
                // You can add an error message and display it in the login form
                $this->addFlash('error', 'Invalid email or password.');
            }
        
        }

        $signupForm = $this->createForm(SignupType::class);
        $signupForm->handleRequest($request);

        if ($signupForm->isSubmitted() && $signupForm->isValid()) {
            /** @var Users $user */
            $user = $signupForm->getData();

            // Check if email is already being used
            $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
            $this->addFlash('error', 'Email is already taken.');
            return $this->redirectToRoute('login');
            }

            // Set default values
            $user->setRoles(['ROLE_USER']);
            $user->setProfileImage('default.png');
            $user->setCountry('Germany');
            $user->setState('Bayern');
            $user->setCreationDate(new \DateTime());

            $plainPassword = $signupForm->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect or any other post-signup action
            $this->addFlash('success', 'Account created successfully. Please login.');
            return $this->redirectToRoute('login');
        }

        return $this->render('home/login.html.twig', [
            'bodyclass' => 'loginBody',
            'loginForm' => $loginForm->createView(),
            'signupForm' => $signupForm->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
    
    #[Route('/forgot-password', name: 'forgot_password')]
    public function forgotPassword(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $data['email'];

            // Check if the email belongs to a user
            $user = $entityManager->getRepository(Users::class)->findOneBy(['email' => $email]);
            if ($user) {
                // Generate a password reset token
                $token = bin2hex(random_bytes(32));
                $user->setResetToken($token);
                $user->setResetTokenExpiration(new \DateTime('+1 hour'));
                $entityManager->flush();

                // Send password reset email
                $email = (new Email())
                    ->subject('Reset your password')
                    ->from('support@demomailtrap.com')
                    ->to($user->getEmail())
                    ->html($this->renderView('emails/resetPassword.html.twig', [
                        'token' => $token,
                        'first_name' => $user->getFirstName(),
                    ]));

                $mailer->send($email);

                $this->addFlash('success', 'Password reset link sent successfully.');
            } else {
                $this->addFlash('error', 'Email not found.');
            }

            return $this->redirectToRoute('login');
        }

        return $this->render('home/forgotPassword.html.twig', [
            'bodyclass' => 'forgot-body',
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function resetPassword(Request $request, string $token, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $entityManager->getRepository(Users::class)->findOneBy(['resetToken' => $token]);

        if (!$user || $user->isResetTokenExpired()) {
            $this->addFlash('error', 'Invalid or expired password reset token.');
            return $this->redirectToRoute('login');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newPassword = $data['password'];

            // Encode the new password and set it on the user entity
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            // Clear the reset token and expiration
            $user->setResetToken(null);
            $user->setResetTokenExpiration(null);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Password has been successfully reset.');
            return $this->redirectToRoute('login');
        }

        return $this->render('home/resetPassword.html.twig', [
            'form' => $form->createView(),
            'bodyclass' => 'forgot-body',
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): Response
    {
        // Perform logout logic here
        // For example, clear session data or invalidate authentication token
        
        return $this->render('home/index.html.twig', [
            'bodyclass' => 'homeBody',
        ]);
    }

    #[Route('/profile', name: 'profile')]
    public function Profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(Users::class)->findOneByEmail($this->getUser()->getUserIdentifier());
        $userId=$user->getId();
        $form = $this->createForm(ProfileType::class, $user);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('profile');
        }

        // Fetch active renting cars logic goes here
        $activeRentingCars = $entityManager->getRepository(Commands::class)->findActiveRentingCarsByUser($userId);
        
      

        return $this->render('home/profile.html.twig', [
            'profileForm' => $form->createView(),
            'user' => $user,
            'activeRentingCars' => $activeRentingCars,
            'bodyclass' => 'profile-body',

        ]);
    }   

    //  this is the code for the profile image upload
    #[Route("/profile/upload", name: "profile_image_upload")]
    public function uploadProfileImage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(Users::class)->findOneByEmail($this->getUser()->getUserIdentifier());
        $profileImage = $request->files->get('profile_image');
        
        if ($profileImage) {
            // Validate the file type
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($profileImage->getMimeType(), $allowedMimeTypes)) {
                $this->addFlash('error', 'Invalid file type. Only JPEG, PNG, and GIF are allowed.');
                return $this->redirectToRoute('profile');
            }
    
            // Validate the file size (e.g., max 5MB)
            $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
            if ($profileImage->getSize() > $maxFileSize) {
                $this->addFlash('error', 'File size exceeds the limit of 5MB.');
                return $this->redirectToRoute('profile');
            }
    
            // Generate a safe and unique filename
            $newFilename = uniqid() . '.' . $profileImage->guessExtension();
    
            try {
                $profileImage->move(
                    $this->getParameter('profile_image_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // Handle exception if something happens during file upload
                $this->addFlash('error', 'An error occurred while uploading the profile image.');
                return $this->redirectToRoute('profile');
            }
    
            // Update the user's profile image
            $user->setProfileImage($newFilename);
            $entityManager->persist($user);
            $entityManager->flush();
    
            $this->addFlash('success', 'Profile image updated successfully.');
        }
    
        return $this->redirectToRoute('profile');
    }

    #[Route('/profile/export_rent_history', name: 'export_rent_history')]
    public function exportRentHistory(EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(Users::class)->findOneByEmail($this->getUser()->getUserIdentifier());
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to access this page.');
        }

        $commands = $entityManager->getRepository(Commands::class)->findBy(['user_id' => ($user->getId())]);

        $filename = 'rent_history_' . date('Y-m-d') . '.pdf';
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Rent History');
        $pdf->SetSubject('Rent History');
        $pdf->SetKeywords('Rent, History');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();

        $pdf->SetFont('helvetica', '', 12);

        $pdf->Cell(40, 10, 'Car Brand', 1);
        $pdf->Cell(40, 10, 'Car Model', 1);
        $pdf->Cell(40, 10, 'Start Date', 1);
        $pdf->Cell(40, 10, 'End Date', 1);
        $pdf->Cell(40, 10, 'Total Price', 1);

        foreach ($commands as $command) {
            $pdf->Ln();
            $pdf->Cell(40, 10, $command->getCarId()->getBrand(), 1);
            $pdf->Cell(40, 10, $command->getCarId()->getModel(), 1);
            $pdf->Cell(40, 10, $command->getStartDate()->format('Y-m-d'), 1);
            $pdf->Cell(40, 10, $command->getEndDate()->format('Y-m-d'), 1);
            $pdf->Cell(40, 10, $command->getCarId()->getPrice(), 1);
        }

        $pdfContent = $pdf->Output($filename, 'S');

        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');

        return $response;
    }

    #[Route('/user/{id}/ban', name: 'user_ban')]
    public function banUser(Users $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user->banUser();
        $entityManager->flush();

        $this->addFlash('success', 'User banned successfully.');

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/user/{id}/unban', name: 'user_unban')]
    public function unbanUser(Users $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user->unbanUser();
        $entityManager->flush();

        $this->addFlash('success', 'User unbanned successfully.');

        return $this->redirectToRoute('admin_dashboard');
    }
}