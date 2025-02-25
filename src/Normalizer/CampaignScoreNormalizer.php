<?php

namespace App\Normalizer;

use App\Entity\Phoning\Campaign;
use App\Repository\AdherentRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CampaignScoreNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const CAMPAIGN_SCORE_ALREADY_CALLED = 'CAMPAIGN_SCORE_NORMALIZER_ALREADY_CALLED';

    private $adherentRepository;
    private $tokenStorage;

    public function __construct(AdherentRepository $adherentRepository, TokenStorageInterface $tokenStorage)
    {
        $this->adherentRepository = $adherentRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Campaign $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::CAMPAIGN_SCORE_ALREADY_CALLED] = true;

        $campaign = $this->normalizer->normalize($object, $format, $context);

        $caller = $this->tokenStorage->getToken()->getUser();
        $callerId = $caller->getId();

        if (isset($context['item_operation_name']) && 'get_with_scores' === $context['item_operation_name']) {
            $campaign['nb_calls'] = $object->getCampaignHistoriesForAdherent($caller)->count();
            $campaign['nb_surveys'] = $object->getCampaignHistoriesWithDataSurveyForAdherent($caller)->count();
        } else {
            $campaign['nb_calls'] = $object->getCampaignHistories()->count();
            $campaign['nb_surveys'] = $object->getCampaignHistoriesWithDataSurvey()->count();
        }

        $callers = $this->adherentRepository->findScoresByCampaign($object);
        array_walk($callers, function (&$adherent, $key) use ($callerId) {
            $adherent['caller'] = $callerId === $adherent['id'];
            $adherent['position'] = ++$key;
            unset($adherent['id']);
        });
        $campaign['scoreboard'] = $callers;

        return $campaign;
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        return
            empty($context[self::CAMPAIGN_SCORE_ALREADY_CALLED])
            && $data instanceof Campaign
            && \in_array('phoning_campaign_read_with_score', $context['groups'] ?? [])
        ;
    }
}
