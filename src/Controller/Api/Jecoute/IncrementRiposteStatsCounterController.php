<?php

namespace App\Controller\Api\Jecoute;

use App\Entity\Jecoute\Riposte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class IncrementRiposteStatsCounterController extends AbstractController
{
    public function __invoke(Riposte $riposte, string $action, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!\in_array($action, Riposte::ACTIONS)) {
            return $this->json([
                'code' => 'unknown_action',
                'message' => 'L\'action n\'est pas réconnue.',
            ], Response::HTTP_BAD_REQUEST);
        }

        switch ($action) {
            case Riposte::ACTION_VIEW:
                $riposte->incrementNbViews();

                break;
            case Riposte::ACTION_DETAIL_VIEW:
                $riposte->incrementNdDetailViews();

                break;
            case Riposte::ACTION_SOURCE_VIEW:
                $riposte->incrementNbSourceViews();

                break;
            case Riposte::ACTION_RIPOSTE:
                $riposte->incrementNbRipostes();

                break;
        }

        $entityManager->flush();

        return $this->json('OK');
    }
}
