<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Jecoute\SurveyTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\SurveyRepository")
 * @ORM\Table(name="jecoute_survey")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     SurveyTypeEnum::LOCAL: "LocalSurvey",
 *     SurveyTypeEnum::NATIONAL: "NationalSurvey"
 * })
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"survey_list"},
 *         },
 *     },
 *     itemOperations={},
 *     subresourceOperations={
 *         "api_campaigns_survey_get_subresource": {
 *             "access_control": "is_granted('ROLE_PHONING_CAMPAIGN_MEMBER')",
 *         },
 *     },
 * )
 */
abstract class Survey
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     *
     * @SymfonySerializer\Groups({
     *     "data_survey_write",
     *     "data_survey_read",
     *     "jemarche_data_survey_read",
     *     "survey_list",
     * })
     *
     * @ApiProperty(identifier=true)
     */
    protected $uuid;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=70)
     *
     * @SymfonySerializer\Groups("survey_list")
     */
    private $name;

    /**
     * @var SurveyQuestion[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Jecoute\SurveyQuestion", mappedBy="survey", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position": "ASC"})
     *
     * @Assert\Valid
     */
    private $questions;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $published;

    public function __construct(UuidInterface $uuid = null, string $name = null, bool $published = false)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->name = $name;
        $this->published = $published;
        $this->questions = new ArrayCollection();
    }

    public function resetId(): void
    {
        $this->id = null;
    }

    public function refreshUuid(): void
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function addQuestion(SurveyQuestion $surveyQuestion): void
    {
        if (!$this->questions->contains($surveyQuestion)) {
            $surveyQuestion->setSurvey($this);
            $this->questions->add($surveyQuestion);
        }
    }

    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function setQuestions(Collection $surveyQuestions): void
    {
        $this->questions = $surveyQuestions;
    }

    public function removeQuestion(SurveyQuestion $surveyQuestion): void
    {
        $surveyQuestion->resetSurvey();
        $this->questions->removeElement($surveyQuestion);
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function questionsCount(): int
    {
        return \count($this->questions);
    }

    public function getQuestionsCount(): int
    {
        return $this->questions->count();
    }

    public function isLocal(): bool
    {
        return SurveyTypeEnum::LOCAL === $this->getType();
    }

    public function isNational(): bool
    {
        return SurveyTypeEnum::NATIONAL === $this->getType();
    }

    /**
     * @SymfonySerializer\Groups("survey_list")
     */
    abstract public function getType(): string;

    public function __clone()
    {
        if ($this->id) {
            $this->resetId();
            $this->refreshUuid();
            $this->setPublished(false);
            $this->setName($this->name.' (Copie)');

            $questions = new ArrayCollection();
            foreach ($this->getQuestions() as $surveyQuestion) {
                $clonedSurveyQuestion = clone $surveyQuestion;
                $clonedSurveyQuestion->setSurvey($this);

                $clonedQuestion = clone $surveyQuestion->getQuestion();
                $clonedSurveyQuestion->setQuestion($clonedQuestion);

                $questions->add($clonedSurveyQuestion);
            }

            $this->setQuestions($questions);
        }
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
