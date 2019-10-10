<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlaceType;
use App\Repository\PlaceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\HttpFoundation\Request;

class ConfigurationController extends AbstractController
{
    private $placeRepository;

    public function __construct(PlaceRepository $placeRepository)
    {
        $this->placeRepository = $placeRepository;
    }

    /**
     * @Route("/configuration", name="config")
     */
    public function configAction(Request $request)
    {
        // We get a Place if it already exists.
        $places = $this->placeRepository->findByUser($this->getUser());

        return $this->render('configuration/configuration.html.twig', [
            'places' => $places
        ]);
    }

    /**
     * @Route("/configuration/place/add", name="config.place.add")
     */
    public function placeAddAction(Request $request)
    {
        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $configForm = $this->createForm(PlaceType::class, null, [
            'data_class' => null,
            'user' => $this->getUser(),
        ]);

        if('POST' === $request->getMethod()) {
            $configForm->handleRequest($request);
            if ($configForm->isValid()) {
                PlaceType::handleSubmit($this->getDoctrine()->getManager(), $configForm->getData(), $this->getUser());
                $this->addFlash('success', 'La nouvelle adresse a bien été enregistrée !');

                return $this->redirectToRoute('config');
            }
        }

        return $this->render('configuration/place_form.html.twig', [
            'title' => "Ajouter une adresse",
            'form_config' => $configForm->createView()
        ]);
    }

    /**
     * @Route("/configuration/place/{id}/update", name="config.place.update")
     */
    public function placeUpdateAction(Request $request, string $id)
    {
        $place = $this->checkPlace($id);

        /** @var \Symfony\Component\Form\FormBuilder $configForm */
        $configForm = $this->createForm(PlaceType::class, $place, [
                'data_class' => null,
                'user' => $this->getUser(),
            ])
        ;

        if('POST' === $request->getMethod()) {
            $configForm->handleRequest($request);
            if ($configForm->isValid()) {
                PlaceType::handleSubmit($this->getDoctrine()->getManager(), $configForm->getData(), $this->getUser());
                $this->addFlash('success', 'Votre configuration a bien été enregistrée !');

                return $this->redirectToRoute('config');
            }
        }

        return $this->render('configuration/place_form.html.twig', [
            'title' => "Ajouter une adresse",
            'form_config' => $configForm->createView()
        ]);
    }

    /**
     * @Route("/configuration/place/{id}/delete", name="config.place.delete")
     */
    public function placeDeleteAction(Request $request, string $id)
    {
        $place = $this->checkPlace($id);

        $form = $this
            ->createFormBuilder()
            ->add('are_you_sure', Form\CheckboxType::class, [
                'label' => "Veuillez cocher cette case si vous êtes sûr de vouloir supprimer cette adresse",
                'help' => "Attention, cette action entrainera la suppression de TOUTES les données associées à cette adresse.",
                'required' => true,
            ])
            ->add('submit', Form\SubmitType::class, [
                'attr' => ['class' => 'btn btn-danger'],
                'label' => "Supprimer l'adresse et TOUTES ses données",
            ])
            ->getForm()
            ->handleRequest($request)
        ;

        if('POST' === $request->getMethod()) {
            if ($form->isValid()) {
                // ça va un peu vite nan ?
                $place = $this->placeRepository->purge($place);
                $this->addFlash('success', 'L\'adresse a bien été supprimée !');
                return $this->redirectToRoute('config');
            }
        }

        return $this->render('misc/confirmation_form.html.twig', [
            'title' => 'Supprimer une adresse',
            'form' => $form->createView(),
            'cancel' => 'config'
        ]);
    }

    private function checkPlace(string $placeId): Place
    {
        if (!$place = $this->placeRepository->find($placeId)) {
            throw new NotFoundHttpException("L'adresse cherchée n'existe pas !");
        }

        if (!$this->getUser()->canEdit($place)) {
            throw new AccessDeniedException("Vous n'êtes pas authorisé à voir les données de cette adresse.");
        }

        return $place;
    }
}
