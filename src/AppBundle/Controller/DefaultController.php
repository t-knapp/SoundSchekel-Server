<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Sound;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;

class DefaultController extends FOSRestController
{
    /**
     * @Rest\Get("/sound")
     */
    public function indexAction(Request $request)
    {
        $restresult = $this->getDoctrine()->getRepository('AppBundle:Sound')->findAll();
        if ($restresult === null) {
            return new View("there are no sounds exist", Response::HTTP_NOT_FOUND);
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
            return new View("sound not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Post("/sound")
     */
    public function postAction(Request $request)
    {
        $data = new Sound();
        $seq = $request->get('seq');
        $category = $request->get('category');
        $title = $request->get('title');
        $length = $request->get('length');
        if(empty($seq) || empty($category) || empty($title) || empty($length)) {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $data->setSeq($seq);
        $data->setCategory($category);
        $data->setTitle($title);
        $data->setLength($length);
        $em = $this->getDoctrine()->getManager();
        $em->persist($data);
        $em->flush();
        return new View("Sound Added Successfully", Response::HTTP_CREATED);
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
}
