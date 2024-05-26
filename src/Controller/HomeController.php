<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Users;
use App\Entity\Cars;
use App\Entity\Commands;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('home/index.html.twig', [
            'bodyclass' => 'homeBody',
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig', [
            'bodyclass' => 'aboutBody',
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, MailerInterface $mailer, LoggerInterface $logger): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // Get the form data
            $formData = $form->getData();

            // Prepare the email message
            $email = (new Email())
                ->from('support@demomailtrap.com') // you need to put from something@demomailtrap.com cause thats the domain we have in mailtrap
                ->to('caryatnwebsite@gmail.com')
                ->subject('Contact Form Submissions')
                ->text('Name: ' . $formData['name'] . "\n\n" . 'Email: ' . $formData['email'] . "\n\n" . 'Message: ' . $formData['message']);

            // Send the email
            try {
                // Send the email
                $mailer->send($email);
                $this->addFlash('success', 'Your message has been sent successfully. We will get back to you soon!');
                $logger->info('Email sent successfully');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Oops! There was an error sending your message. Please try again later.');
                $logger->error('Error sending email: ' . $e->getMessage());
            }

            return $this->redirectToRoute('contact');
        }
        
        return $this->render('home/contact.html.twig', [
            'bodyclass' => 'contactBody',
            'form' => $form->createView(),
        ]);
    }


    #[Route('/rentCars', name: 'rent_cars')]
    public function rentCars(): Response
    {
        return $this->render('home/rentCars.html.twig', [
            'bodyclass' => 'rentCarsBody',
        ]);
    }

    #[Route('/myCars', name: 'my_cars')]
    public function myCars(): Response
    {
        // Check if the user is authenticated
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }

        return $this->render('home/myCars.html.twig', [
            'bodyclass' => 'myCarsBody',
        ]);
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        // Fetch data from the database using injected EntityManager
        $users = $entityManager->getRepository(Users::class)->findAll();
        $cars = $entityManager->getRepository(Cars::class)->findAll();
        $commands = $entityManager->getRepository(Commands::class)->findAll();

        // Debugging: Check if data is fetched
        if (!$users || !$cars || !$commands) {
            throw $this->createNotFoundException('No data found.');
        }

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'cars' => $cars,
            'commands' => $commands,
            'bodyclass' => 'dashboardBody',
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->render('home/login.html.twig', [
            'bodyclass' => 'loginBody',
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
    public function profile(): Response
    {
        return $this->render('home/profile.html.twig', [
            'bodyclass' => 'profileBody',
        ]);
    }
}
