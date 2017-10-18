<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Sequence;
use AppBundle\Entity\Sound;
use AppBundle\Repository\SequenceRepository;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Service\MessageGenerator;

class DefaultController extends FOSRestController
{
    /**
     * @Rest\Get("/sound")
     */
    public function indexAction(Request $request)
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:Sound')->findAll();
        if ($restresult === null) {
            return new View("there are no sounds", Response::HTTP_NOT_FOUND);
        }
        return $restresult;
    }

    /**
     * @Rest\Get("/sound/{id}")
     */
    public function idAction($id)
    {
        $singleresult = $this->getDoctrine()->getRepository('AppBundle:Sound')->find($id);
        if ($singleresult === null) {
            $messageGenerator = $this->container->get('app.message_generator');
            return new View("sound not found" . $messageGenerator->getHappyMessage(), Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Post("/sound")
     */
    public function postAction(Request $request)
    {
        $category = $request->get('category');
        $title = $request->get('title');
        $length = $request->get('length');
        $uploadedSound = $request->files->get('sound');
        if(empty($category) || empty($title) || empty($length)) {
            return new View("bad request", Response::HTTP_BAD_REQUEST);
        }
        $newSound = new Sound();
        $newSound->setSeq($this->getNextSequenceValue());
        $newSound->setCategory($category);
        $newSound->setTitle($title);
        $newSound->setLength($length);
        $em = $this->getDoctrine()->getManager();
        $em->persist($newSound);
        $em->flush();
        $soundPath = $this->container->getParameter('sound_path');
        $uploadedSound->move($soundPath, $newSound->getId());
        return new View("sound added. Id: " . $newSound->getId(), Response::HTTP_CREATED);
    }

    /**
     * @Rest\Delete("/sound/{id}")
     */
    public function deleteAction($id)
    {
        $sn = $this->getDoctrine()->getManager();
        $sound = $this->getDoctrine()->getRepository('AppBundle:Sound')->find($id);
        if (empty($sound)) {
            return new View("sound not found", Response::HTTP_NOT_FOUND);
        }
        else {
            $sn->remove($sound);
            $sn->flush();
        }
        return new View("deleted successfully", Response::HTTP_OK);
    }

    private function getNextSequenceValue()
    {
        $manager = $this->getDoctrine()->getManager();
        /** @var $repo SequenceRepository */
        $repo = $manager->getRepository(Sequence::class);
        return $repo->getNextValue();
    }
}
