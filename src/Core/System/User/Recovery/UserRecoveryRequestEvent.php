<?php declare(strict_types=1);

namespace HeyFrame\Core\System\User\Recovery;

use HeyFrame\Core\Content\Flow\Dispatching\Action\FlowMailVariables;
use HeyFrame\Core\Content\Flow\Dispatching\Aware\ScalarValuesAware;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\EventData\EntityType;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\EventData\MailRecipientStruct;
use HeyFrame\Core\Framework\Event\EventData\ScalarValueType;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Event\MailAware;
use HeyFrame\Core\Framework\Event\UserAware;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\User\Aggregate\UserRecovery\UserRecoveryDefinition;
use HeyFrame\Core\System\User\Aggregate\UserRecovery\UserRecoveryEntity;
use HeyFrame\Core\System\User\UserEntity;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('fundamentals@framework')]
class UserRecoveryRequestEvent extends Event implements UserAware, MailAware, ScalarValuesAware, FlowEventAware
{
    final public const EVENT_NAME = 'user.recovery.request';

    private ?MailRecipientStruct $mailRecipientStruct = null;

    public function __construct(
        private readonly UserRecoveryEntity $userRecovery,
        private readonly string $resetUrl,
        private readonly Context $context
    ) {
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getUserRecovery(): UserRecoveryEntity
    {
        return $this->userRecovery;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('userRecovery', new EntityType(UserRecoveryDefinition::class))
            ->add('resetUrl', new ScalarValueType('string'))
        ;
    }

    /**
     * @return array<string, scalar|array<mixed>|null>
     */
    public function getValues(): array
    {
        return [
            FlowMailVariables::RESET_URL => $this->resetUrl,
        ];
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if (!$this->mailRecipientStruct instanceof MailRecipientStruct) {
            /** @var UserEntity $user */
            $user = $this->userRecovery->getUser();

            $this->mailRecipientStruct = new MailRecipientStruct([
                $user->getEmail() => $user->getFirstName() . ' ' . $user->getLastName(),
            ]);
        }

        return $this->mailRecipientStruct;
    }

    public function getChannelId(): ?string
    {
        return null;
    }

    public function getResetUrl(): string
    {
        return $this->resetUrl;
    }

    public function getUserId(): string
    {
        return $this->userRecovery->getId();
    }
}
