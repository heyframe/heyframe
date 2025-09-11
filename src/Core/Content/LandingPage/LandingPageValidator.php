<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\LandingPage;

use HeyFrame\Core\Content\LandingPage\Aggregate\LandingPageChannel\LandingPageChannelDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Validation\PostWriteValidationEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\WriteConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
#[Package('discovery')]
class LandingPageValidator implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PostWriteValidationEvent::class => 'preValidate',
        ];
    }

    public function preValidate(PostWriteValidationEvent $event): void
    {
        $writeException = $event->getExceptions();
        $commands = $event->getCommands();
        $violationList = new ConstraintViolationList();

        foreach ($commands as $command) {
            if (!($command instanceof InsertCommand) || $command->getEntityName() !== LandingPageDefinition::ENTITY_NAME) {
                continue;
            }

            if (!$this->hasAnotherValidCommand($commands, $command)) {
                $violationList->addAll(
                    $this->validator->startContext()
                        ->atPath($command->getPath() . '/channels')
                        ->validate(null, [new NotBlank()])
                        ->getViolations()
                );
                $writeException->add(new WriteConstraintViolationException($violationList));
            }
        }
    }

    /**
     * @param WriteCommand[] $commands
     */
    private function hasAnotherValidCommand(array $commands, WriteCommand $command): bool
    {
        $isValid = false;
        foreach ($commands as $searchCommand) {
            if ($searchCommand->getEntityName() === LandingPageChannelDefinition::ENTITY_NAME && $searchCommand instanceof InsertCommand) {
                $searchPrimaryKey = $searchCommand->getPrimaryKey();
                $searchLandingPageId = $searchPrimaryKey['landing_page_id'] ?? null;

                $currentPrimaryKey = $command->getPrimaryKey();
                $currentLandingPageId = $currentPrimaryKey['id'] ?? null;

                if ($searchLandingPageId === $currentLandingPageId) {
                    $isValid = true;
                }
            }
        }

        return $isValid;
    }
}
