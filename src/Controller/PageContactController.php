<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Departement;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;



class PageContactController extends AbstractController
{
    /**
     * @Route("/contact", name="page_contact")
     */
    public function index(Request $request, \Swift_Mailer $mailer)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class,$contact);

        $form->handleRequest($request);
        //Je vérifie que le formulaire à été soumis et si il est valide.
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            //Je garde le contact en base de données pour garder une trace.
            $em->persist($contact);
            $em->flush();
            //On récupère les informations du département voulu par l'utilisateur
            $idDepartement = $form['id_departement']->getData();
            $mail = $idDepartement->getMailDepartement();
            $formContact = $form->getData();

            //On envoie le mail
            $message = (new \Swift_Message($formContact->getObjetContact()))
                ->setFrom('adresseMail')//Adresse mail d'envoi à mettre
                ->setTo($mail)
                ->setBody(
                    $this->renderView(
                        'emails/mail.html.twig', array(
                            'nom' => $formContact->getNomEnvoyeur(),
                            'message' => $formContact->getMessage()
                        )
                    ),
                    'text/html'
                )
            ;
            $mailer->send($message);
            $this->addFlash('message', 'le message à bien été envoyé');

        }

        return $this->render('page_contact/index.html.twig', [
            'formulaire' => $form->createView()
        ]);
    }
}
