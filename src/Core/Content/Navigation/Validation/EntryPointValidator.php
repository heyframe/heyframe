<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Validation;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Category\CategoryDefinition;
use HeyFrame\Core\Content\Navigation\NavigationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Validation\PostWriteValidationEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\WriteConstraintViolationException;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @internal
 */
#[Package('discovery')]
class EntryPointValidator implements EventSubscriberInterface
{
    private const ERROR_CODE = 'CONTENT__INVALID_NAVIGATION_TYPE_AS_ENTRY_POINT';

    private const ALLOWED_TYPE_CHANGE = [
        NavigationDefinition::TYPE_PAGE,
        NavigationDefinition::TYPE_FOLDER,
    ];

    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostWriteValidationEvent::class => 'postValidate',
        ];
    }

    public function postValidate(PostWriteValidationEvent $event): void
    {
        $violationList = new ConstraintViolationList();
        foreach ($event->getCommands() as $command) {
            if (!($command instanceof InsertCommand || $command instanceof UpdateCommand)) {
                continue;
            }

            if ($command->getEntityName() !== CategoryDefinition::ENTITY_NAME) {
                continue;
            }

            if (!isset($command->getPayload()['type'])) {
                continue;
            }

            $violationList->addAll($this->checkTypeChange($command, $event));
        }

        if ($violationList->count() > 0) {
            $event->getExceptions()->add(new WriteConstraintViolationException($violationList));

            return;
        }
    }

    private function checkTypeChange(WriteCommand $command, PostWriteValidationEvent $event): ConstraintViolationListInterface
    {
        $violationList = new ConstraintViolationList();
        $payload = $command->getPayload();

        if (\in_array($payload['type'], self::ALLOWED_TYPE_CHANGE, true)) {
            return $violationList;
        }

        if ($this->isCategoryEntryPoint($command->getPrimaryKey()['id'], $event)) {
            return $violationList;
        }

        $messageTemplate = 'The type can not be assigned while category is entry point.';
        $parameters = ['{{ value }}' => $payload['type']];

        $violationList->add(new ConstraintViolation(
            str_replace(array_keys($parameters), $parameters, $messageTemplate),
            $messageTemplate,
            $parameters,
            null,
            \sprintf('%s/type', $command->getPath()),
            $payload['type'],
            null,
            self::ERROR_CODE
        ));

        return $violationList;
    }

    private function isCategoryEntryPoint(string $categoryId, PostWriteValidationEvent $event): bool
    {
        foreach ($event->getCommands() as $channelCommand) {
            if ($channelCommand->getEntityName() !== ChannelDefinition::ENTITY_NAME) {
                continue;
            }

            $payload = $channelCommand->getPayload();
            if ((isset($payload['navigation_category_id']) && $payload['navigation_category_id'] === $categoryId)
                || (isset($payload['footer_category_id']) && $payload['footer_category_id'] === $categoryId)
                || (isset($payload['service_category_id']) && $payload['service_category_id'] === $categoryId)
            ) {
                return false;
            }
        }

        $result = $this->connection->createQueryBuilder()
            ->select('id')
            ->from(ChannelDefinition::ENTITY_NAME)
            ->where('navigation_category_id = :navigation_id')
            ->orWhere('footer_category_id = :footer_id')
            ->orWhere('service_category_id = :service_id')
            ->setParameter('navigation_id', $categoryId)
            ->setParameter('footer_id', $categoryId)
            ->setParameter('service_id', $categoryId)
            ->setMaxResults(1)
            ->executeQuery();

        return !$result->fetchOne();
    }
}
