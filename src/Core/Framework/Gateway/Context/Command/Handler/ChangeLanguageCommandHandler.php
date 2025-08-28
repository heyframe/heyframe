<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway\Context\Command\Handler;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Gateway\Context\Command\AbstractContextGatewayCommand;
use HeyFrame\Core\Framework\Gateway\Context\Command\ChangeLanguageCommand;
use HeyFrame\Core\Framework\Gateway\GatewayException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Language\LanguageCollection;

/**
 * @extends AbstractContextGatewayCommandHandler<ChangeLanguageCommand>
 *
 * @internal
 */
#[Package('framework')]
class ChangeLanguageCommandHandler extends AbstractContextGatewayCommandHandler
{
    /**
     * @internal
     *
     * @param EntityRepository<LanguageCollection> $languageRepository
     */
    public function __construct(
        private readonly EntityRepository $languageRepository,
    ) {
    }

    public function handle(AbstractContextGatewayCommand $command, ChannelContext $context, array &$parameters): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('locale.code', $command->iso));

        $languageId = $this->languageRepository->searchIds($criteria, $context->getContext())->firstId();

        if ($languageId === null) {
            throw GatewayException::handlerException('Language with iso code {{ isoCode }} not found', ['isoCode' => $command->iso]);
        }

        $parameters['languageId'] = $languageId;
    }

    public static function supportedCommands(): array
    {
        return [ChangeLanguageCommand::class];
    }
}
