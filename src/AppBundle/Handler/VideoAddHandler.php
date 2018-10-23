<?php
/**
 * Created by PhpStorm.
 * User: brieres
 * Date: 09/10/2018
 * Time: 22:15
 */

namespace AppBundle\Handler;

use AppBundle\Entity\Trick;
use AppBundle\Manager\TrickManager;
use AppBundle\Service\SPHandler;
use Symfony\Component\HttpFoundation\Response;


class VideoAddHandler
{
    private $trickManager;
    /**
     * @var Trick
     */
    private $trick;
    private $view;
    private $handler;

    /**
     * @param SPHandler $handler
     * @param TrickManager $trickManager
     */
    public function __construct(SPHandler $handler, TrickManager $trickManager)
    {
        $this->trickManager = $trickManager;
        $this->handler = $handler;
    }

    public function onSuccess()
    {
        $this->trickManager->addVideo($this->trick, $this->handler->formData());
        $this->handler->setFlash('success', 'La video a été ajoutée');
    }

    /**
     * @param $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return Response
     */
    public function getView()
    {
        return $this->handler->response(
            $this->view, array(
                "trick" => $this->trick
            )
        );
    }

    /**
     * @param $formType
     * @return Response
     */
    public function handle($formType)
    {
        if ($this->handler->isSubmitted($formType)) {
            $this->onSuccess();
        }
        return $this->getView();
    }

    /**
     * @param Trick $trick
     */
    public function setTrick(Trick $trick)
    {
        $this->trick = $trick;
    }
}