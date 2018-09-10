<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Get; 
use FOS\RestBundle\Controller\Annotations\Put;// N'oublons pas d'inclure Get
use FOS\RestBundle\View\View; // Utilisation de la vue de FOSRestBundle
use FOS\RestBundle\Controller\Annotations as Rest; // alias pour toutes les annotations
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\PlaceType;
use AppBundle\Entity\Place;

class PlaceController extends Controller
{

    /**
     * @Rest\View(serializerGroups={"place"})
     * @Rest\Get("/places")
     */

    public function getPlacesAction(Request $request)
    {
        $places = $this->get('doctrine.orm.entity_manager')->getRepository('AppBundle:Place')->findAll();
        

        return $places;
    }




        /**
         * @Rest\View(serializerGroups={"place"})
         * @Rest\Get("/places/{id}")
         */

    public function getPlaceAction($id)
    {

        $place = $this->get('doctrine.orm.entity_manager')->getRepository('AppBundle:Place')->find($id);
        /* @var $place Place */

                if (empty($place)) {
            return new JsonResponse(['message' => 'Place not found'], Response::HTTP_NOT_FOUND);
        }
        



        return $place;
    }


   
    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"place"})
     * @Rest\Post("/places")
     */

    public function postPlacesAction(Request $request)
    {
        $place = new Place();
        $form = $this->createForm(PlaceType::class, $place);

        $form->submit($request->request->all()); // Validation des données

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($place);
            $em->flush();

            return $place;
        } else {
            return $form;
        }
    }





    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT, serializerGroups={"place"})
     * @Rest\Delete("/places/{id}")
     */
    public function removePlaceAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $place = $em->getRepository('AppBundle:Place')
                    ->find($request->get('id'));
        /* @var $place Place */


        if (!$place) {
            return;
        }


         foreach ($place->getPrices() as $price) {
            $em->remove($price);
        }

        $em->remove($place);
        $em->flush();
       }
    }



    /**
     * @Rest\View(serializerGroups={"place"})
     * @Rest\Put("/places/{id}")
     */

    function updatePlaceAction(Request $request)
    {
        return $this->updatePlace($request, true);
    }

    /**
     * @Rest\View(serializerGroups={"place"})
     * @Rest\Patch("/places/{id}")
     */
   function patchPlaceAction(Request $request)
    {
        return $this->updatePlace($request, false);
    }



   function updatePlace(Request $request,$clearMissing)

    {
        $place = $this->get('doctrine.orm.entity_manager')->getRepository('AppBundle:Place')->find($request->get('id'));

         // L'identifiant en tant que paramètre n'est plus nécessaire
        /* @var $place Place */


        if (empty($place)) {
            return new JsonResponse(['message' => 'Place not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(PlaceType::class, $place);

        $form->submit($request->request->all(),$clearMissing);

        if ($form->isValid())  
         {
            $em = $this->get('doctrine.orm.entity_manager');
            // l'entité vient de la base, donc le merge n'est pas nécessaire.
            // il est utilisé juste par soucis de clarté
            $em->merge($place);
            $em->flush();
            return $place;
         } 
         else {
            return $form;
        }
    }
      


