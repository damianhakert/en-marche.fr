<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Event\EventTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {"groups": {"event_read"}},
 *         "order": {"beginAt": "DESC"},
 *     },
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"postAddress.postalCode": "exact", "category.name": "exact"})
 */
class MunicipalEvent extends CommitteeEvent
{
    public function getType(): string
    {
        return EventTypeEnum::TYPE_MUNICIPAL;
    }
}