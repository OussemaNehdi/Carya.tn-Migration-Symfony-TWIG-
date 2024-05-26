<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // Get the form data
            $formData = $form->getData();

            // Prepare the email message
            $email = (new Email())
                ->from('caryatnwebsite@gmail.com')
                ->to('caryatnwebsite@gmail.com')
                ->subject('Contact Form Submission')
                ->text('Name: ' . $formData['name'] . "\n\n" . 'Email: ' . $formData['email'] . "\n\n" . 'Message: ' . $formData['message']);

            // Send the email
            $success = $mailer->send($email);

            if ($success) {
                $this->addFlash('success', 'Your message has been sent successfully. We will get back to you soon!');
            } else {
                $this->addFlash('error', 'Oops! There was an error sending your message. Please try again later.');
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
        return $this->render('home/myCars.html.twig', [
            'bodyclass' => 'myCarsBody',
        ]);
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function adminDashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'bodyclass' => 'adminDashboardBody',
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
