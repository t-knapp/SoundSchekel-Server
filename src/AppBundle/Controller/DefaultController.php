<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Sequence;
use AppBundle\Entity\Sound;
use AppBundle\Repository\SequenceRepository;
use AppBundle\Shared\SoundFileMetadata;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Service\SoundFileManipulatorFFMPEG;

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
            return new View("sound not found", Response::HTTP_NOT_FOUND);
        }
        return $singleresult;
    }

    /**
     * @Rest\Post("/sound")
     */
    public function postAction(Request $request, SoundFileManipulatorFFMPEG $ffmpeg)
    {
        $category = $request->get('category');
        $title = $request->get('title');
        /** @var UploadedFile $uploadedSound */
        $uploadedSound = $request->files->get('sound');

        if(empty($category) || empty($title) || is_null($uploadedSound) || !$uploadedSound->isValid()) {
            return new View("bad request", Response::HTTP_BAD_REQUEST);
        }

        $uploadedSoundFilePath = $uploadedSound->getPathname();
        $normalizedSoundFileName = $uploadedSound->getPathname() . ".mp3";
        $metadataAppliedSoundFilePath = $this->getMetadataAppliedSoundFilePath();

        $getDurationResult = $ffmpeg->getDuration($uploadedSoundFilePath);
        if(!$getDurationResult->isSuccessful())
            return new View("length processing of sound failed", Response::HTTP_BAD_REQUEST);
        $length = $getDurationResult->getOutput();

        $ffmpeg->normalize($uploadedSoundFilePath, $normalizedSoundFileName);

        $metaData = new SoundFileMetadata($category, $title);
        $ffmpeg->setMetadata($normalizedSoundFileName, $metaData, $metadataAppliedSoundFilePath);

        $newSound = $this->createSound($category, $title, $length, $this->getNextSequenceValue());
        $em = $this->getDoctrine()->getManager();
        $em->persist($newSound);
        $em->flush();
        $newSoundId = $newSound->getId();

        rename($metadataAppliedSoundFilePath, $this->getFinalSoundFilePath($newSoundId));
        unlink($normalizedSoundFileName);
        unlink($uploadedSoundFilePath);

        return new View("sound added. Id: {$newSoundId}", Response::HTTP_CREATED);
    }

    private function createSound($category, $title, $length, $sequence) {
        $sound = new Sound();
        $sound->setCategory($category);
        $sound->setTitle($title);
        $sound->setLength($length);
        $sound->setSeq($sequence);
        return $sound;
    }

    private function getMetadataAppliedSoundFilePath() {
        $soundStoragePath = $this->container->getParameter('sound_path');
        $fileName = "temp.mp3";
        return "{$soundStoragePath}{$fileName}";
    }

    private function getFinalSoundFilePath($id) {
        $soundStoragePath = $this->container->getParameter('sound_path');
        return "{$soundStoragePath}{$id}";
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
