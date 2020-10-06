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
    public function index(Request $request,\Swift_Mailer $mailer)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class,$contact);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();
            $this->sendMail($form,$mailer);
        }
        return $this->render('page_contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function sendMail($form,$mailer){
        $idDepartement = $form['id_departement']->getData();
        $mail = $idDepartement->getMailDepartement();
        $formContact = $form->getData();

        $message = (new \Swift_Message($formContact->getObjetContact()))
            ->setFrom('mailAddress')
            ->setTo($mail)
            ->setBody(
                $this->renderView(
                    'emails/mail.html.twig', array(
                        'name' => $formContact->getNomEnvoyeur(),
                        'message' => $formContact->getMessage()
                    )
                ),
                'text/html'
            )
        ;
        $mailer->send($message);
        $this->addFlash('message', 'le message à bien été envoyé');

    }
}
