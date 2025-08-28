<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

/**
 * This route is used to get information about the newsletter recipients
 */
#[Package('checkout')]
abstract class AbstractAccountNewsletterRecipientRoute
{
    abstract public function getDecorated(): AbstractAccountNewsletterRecipientRoute;

    abstract public function load(Request $request, ChannelContext $context, Criteria $criteria, CustomerEntity $customer): AccountNewsletterRecipientRouteResponse;
}
