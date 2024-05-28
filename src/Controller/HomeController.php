<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Cars;
use App\Entity\Commands;
use App\Form\ContactType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;
use App\Repository\CarsRepository;


use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Form\CarType;



class HomeController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }


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
    public function rentCars(CarsRepository $CarsRepository,Request $request): Response
    {

       
        $filters = $CarsRepository->constructFilterQuery($request);       
        if (!empty($filters)) {
            $Cars = $CarsRepository->findByFilters($filters);
        } else {
            $Cars=$CarsRepository->getAllCars();
        }
        return $this->render('home/rentCars.html.twig', [
            'bodyclass' => 'rent-body',
            'cars' => $Cars,
            'brands'=>$CarsRepository-> getDistinctValues('brand'),
            'models'=>$CarsRepository->getDistinctValues( 'model'),
            'colors'=>$CarsRepository->getDistinctValues( 'color'),
            'max_km'=>$CarsRepository->getMaxValue('km'),
            'max_price'=>$CarsRepository->getMaxValue('price'),
            'filter_data' => $filters,

        ]);
    }
    ///////
    #[Route('/myCars', name: 'my_cars')]
    public function myCars(CarsRepository $CarsRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check if user is authenticated
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }

        $user = $entityManager->getRepository(Users::class)->findOneByEmail($this->getUser()->getUserIdentifier()); //current user?
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $filters = $CarsRepository->constructFilterQuery($request);
        if (!empty($filters)) {
            $Cars = $CarsRepository->findByFilters($filters);
        } else {
            $Cars = $CarsRepository->getAllCars();
        }

        $userCars = $CarsRepository->findCarsByUserId($Cars, $user->getId());

        return $this->render('home/myCars.html.twig', [
            'bodyclass' => 'listing-body',
            'cars' => $userCars,
            'brands' => $CarsRepository->getDistinctValues('brand'),
            'models' => $CarsRepository->getDistinctValues('model'),
            'colors' => $CarsRepository->getDistinctValues('color'),
            'max_km' => $CarsRepository->getMaxValue('km'),
            'max_price' => $CarsRepository->getMaxValue('price'),
            'filter_data' => $filters,
        ]);
    }

    
    #[Route('/myCarsDeleteCar/{id}', name: 'my_cars_delete_car')]
    public function myCarsDeleteCar($id, EntityManagerInterface $entityManager, Request $request): Response
    {
        
        $car = $entityManager->getRepository(Cars::class)->find($id);

        if (!$car) {
            throw $this->createNotFoundException('The car does not exist');
        }


        $entityManager->remove($car);
        $entityManager->flush();

        
        $this->addFlash('success', 'Car deleted successfully.');

        return $this->redirectToRoute('my_cars'); 
    }
    


    ////
    #[Route('/command/accept', name: 'accept_command', methods: ['POST'])]
    public function acceptCommand(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commandId = $request->request->get('command_id');
        $command = $entityManager->getRepository(Commands::class)->find($commandId);

        if ($command) {
            $command->setConfirmed(1);
            $entityManager->flush();

            $this->addFlash('success', 'Command accepted successfully.');
        }

        return $this->redirectToRoute('admin_dashboard'); // Assuming you have a route named 'command_list'
    }

    #[Route('/command/refuse', name: 'refuse_command', methods: ['POST'])]
    public function refuseCommand(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commandId = $request->request->get('command_id');
        $command = $entityManager->getRepository(Commands::class)->find($commandId);

        if ($command) {
            $command->setConfirmed(0);
            $entityManager->flush();

            $this->addFlash('success', 'Command refused successfully.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/command/cancel', name: 'cancel_command', methods: ['POST'])]
    public function cancelCommand(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commandId = $request->request->get('command_id');
        $command = $entityManager->getRepository(Commands::class)->find($commandId);

        if ($command) {
            $entityManager->remove($command);
            $entityManager->flush();

            $this->addFlash('success', 'Command canceled successfully.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/delete_car/{id}', name: 'delete_car')]
    public function deleteCar($id, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Find the car by ID
        $car = $entityManager->getRepository(Cars::class)->find($id);

        // If car is not found, throw an exception or redirect with an error message
        if (!$car) {
            throw $this->createNotFoundException('The car does not exist');
        }

        // Remove the car
        $entityManager->remove($car);
        $entityManager->flush();

        // Redirect to the car list page with a success message
        $this->addFlash('success', 'Car deleted successfully.');

        return $this->redirectToRoute('admin_dashboard'); // Adjust the route name to your car list page
    }

    #[Route('/add_car', name: 'add_car')]
    public function addCar(Request $request, EntityManagerInterface $entityManager): Response
    {
        $car = new Cars();
        $form = $this->createForm(CarType::class, $car);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('cars_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle exception if something happens during file upload
                }

                // Set the image filename in the Car entity
                $car->setImage($newFilename);
            }

            // Set the ownerId to the currently logged-in user
            $user = $this->security->getUser();
            
            $car->setOwnerId($user);
            

            $entityManager->persist($car);
            $entityManager->flush();

            return $this->redirectToRoute('admin_dashboard'); // or any route you want to redirect to
        }

        return $this->render('forms/addCar.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}