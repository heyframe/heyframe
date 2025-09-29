<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Command;

use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Maintenance\Channel\Command\ChannelCreateCommand;
use HeyFrame\Core\Maintenance\Channel\Service\ChannelCreator;
use HeyFrame\Core\System\Snippet\Aggregate\SnippetSet\SnippetSetCollection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @final
 */
#[AsCommand(
    name: 'channel:create:frontend',
    description: 'Creates a new frontend channel',
)]
#[Package('framework')]
class ChannelCreateFrontendCommand extends ChannelCreateCommand
{
    /**
     * @internal
     *
     * @param EntityRepository<SnippetSetCollection> $snippetSetRepository
     */
    public function __construct(
        private readonly EntityRepository $snippetSetRepository,
        ChannelCreator $channelCreator
    ) {
        parent::__construct(
            $channelCreator
        );
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'App URL for frontend')
            ->addOption('snippetSetId', null, InputOption::VALUE_REQUIRED, 'Default snippet set')
            ->addOption('isoCode', null, InputOption::VALUE_REQUIRED, 'Snippet set iso code')
        ;
    }

    protected function getTypeId(): string
    {
        return Defaults::CHANNEL_TYPE_FRONTEND;
    }

    protected function getChannelConfiguration(InputInterface $input, OutputInterface $output): array
    {
        $snippetSet = $input->getOption('snippetSetId') ?? $this->guessSnippetSetId($input->getOption('isoCode'));

        return [
            'domains' => [
                [
                    'url' => $input->getOption('url'),
                    'languageId' => $input->getOption('languageId'),
                    'snippetSetId' => $snippetSet,
                    'currencyId' => $input->getOption('currencyId'),
                ],
            ],
            'navigationNavigationDepth' => 3,
            'name' => $input->getOption('name') ?? 'Frontend',
        ];
    }

    private function guessSnippetSetId(?string $isoCode = null): string
    {
        $snippetSet = $this->getSnippetSetId($isoCode);

        if ($snippetSet === null) {
            $snippetSet = $this->getSnippetSetId();
        }

        if ($snippetSet === null) {
            throw new \InvalidArgumentException(\sprintf('Snippet set with isoCode %s cannot be found.', $isoCode));
        }

        return $snippetSet;
    }

    private function getSnippetSetId(?string $isoCode = null): ?string
    {
        $isoCode = $isoCode ?: 'zh-CN';
        $isoCode = str_replace('_', '-', $isoCode);
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('iso', $isoCode));

        return $this->snippetSetRepository->searchIds($criteria, Context::createCLIContext())->firstId();
    }
}
