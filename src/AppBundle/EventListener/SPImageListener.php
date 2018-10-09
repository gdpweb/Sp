<?php

namespace AppBundle\EventListener;

use AppBundle\Service\SPFileSystem;
use AppBundle\Service\SPResizeImage;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Event\LifecycleEventArgs;
use AppBundle\Entity\Image;


class SPImageListener implements EventSubscriber
{
    private $fileSystem;
    private $resizeImage;
    private $targetDirectory;


    public function __construct(SPFileSystem $fileSystem, SPResizeImage $resizeImage, $target_directory)
    {
        $this->fileSystem = $fileSystem;
        $this->resizeImage = $resizeImage;
        $this->fileSystem->setPathDirectory($target_directory);
        $this->targetDirectory = $target_directory;
    }

    public function getSubscribedEvents()
    {

        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::postPersist,
            Events::preRemove
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Image) {
            return;
        }
        $this->setFileUpload($entity);
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Image) {
            return;
        }
        $this->setFileUpload($entity);
        $this->uploadFile($entity);
    }

    /**
     * @param LifecycleEventArgs $args
     */

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->uploadFile($entity);

    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Image) {
            return;
        }
        $filename = $this->targetDirectory . '/' . $entity->getId() . '.' . $entity->getExt();
        $fileResize = $this->targetDirectory . '/mini/' . $entity->getId() . '.' . $entity->getExt();

        if (file_exists($filename)) {

            $this->fileSystem->remove($filename);
            $this->fileSystem->remove($fileResize);

        }
    }

    public function setFileUpload(Image $entity)
    {
        $file = $entity->getFile();
        if ($file instanceof UploadedFile) {
            $entity->setExt($file->getClientOriginalExtension());
            $entity->setAlt(basename($file->getClientOriginalName(), '.' . $entity->getExt()));
        }
    }

    public function uploadFile($entity)
    {

        if (!$entity instanceof Image) {
            return;
        }
        $file = $entity->getFile();
        if ($file instanceof UploadedFile) {

            $this->fileSystem->upload(
                $file,
                $entity->getId() . '.' . $entity->getExt()
            );
        }

        $filename = $this->targetDirectory . '/' . $entity->getId() . "." . $entity->getExt();
        $fileResize = $this->targetDirectory . '/mini/' . $entity->getId() . '.' . $entity->getExt();

        $this->fileSystem->resizeThumbnail($filename, $fileResize, $entity->getExt());
    }


}