<?php

namespace App\ManagedUsers\Filter;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Subscription\SubscriptionTypeEnum;

class DeputyFilterFactory extends AbstractFilterFactory
{
    public function support(string $spaceCode): bool
    {
        return AdherentSpaceEnum::DEPUTY === $spaceCode;
    }

    protected function getSubscriptionType(): string
    {
        return SubscriptionTypeEnum::DEPUTY_EMAIL;
    }
}
