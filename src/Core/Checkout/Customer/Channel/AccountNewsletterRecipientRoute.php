<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Content\Newsletter\Aggregate\NewsletterRecipient\NewsletterRecipientCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class AccountNewsletterRecipientRoute extends AbstractAccountNewsletterRecipientRoute
{
    /**
     * @internal
     *
     * @param ChannelRepository<NewsletterRecipientCollection> $newsletterRecipientRepository
     */
    public function __construct(private readonly ChannelRepository $newsletterRecipientRepository)
    {
    }

    public function getDecorated(): AbstractAccountNewsletterRecipientRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/account/newsletter-recipient', name: 'store-api.newsletter.recipient', methods: ['GET', 'POST'], defaults: ['_loginRequired' => true, '_entity' => 'newsletter_recipient'])]
    public function load(Request $request, ChannelContext $context, Criteria $criteria, CustomerEntity $customer): AccountNewsletterRecipientRouteResponse
    {
        $criteria->addFilter(new EqualsFilter('email', $customer->getEmail()));

        $result = $this->newsletterRecipientRepository->search($criteria, $context);

        return new AccountNewsletterRecipientRouteResponse($result);
    }
}
