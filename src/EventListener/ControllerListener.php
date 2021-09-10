<?php

namespace App\EventListener;

use App\Test\ClassA;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ControllerListener implements EventSubscriberInterface
{
    protected $om;

    public function __construct(ObjectManager $om, ClassA $a)
    {
        $this->om = $om;
        $this->a = $a;
    }

    /**
     * Transforms controller of the KernelEvent to an array if this is not the case.
     * It's done for the compatibility of ApiPlatformBundle.
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!\is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
        }

        $event->setController($controller);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
