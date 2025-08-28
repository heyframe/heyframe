<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Content\Newsletter\Aggregate\NewsletterRecipient\NewsletterRecipientCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<AccountNewsletterRecipientResult>
 */
#[Package('checkout')]
class AccountNewsletterRecipientRouteResponse extends StoreApiResponse
{
    /**
     * @param EntitySearchResult<NewsletterRecipientCollection> $newsletterRecipients
     */
    public function __construct(EntitySearchResult $newsletterRecipients)
    {
        $firstNewsletterRecipient = $newsletterRecipients->getEntities()->first();
        if ($firstNewsletterRecipient) {
            $accNlRecipientResult = new AccountNewsletterRecipientResult($firstNewsletterRecipient->getStatus());
            parent::__construct($accNlRecipientResult);

            return;
        }
        $accNlRecipientResult = new AccountNewsletterRecipientResult();
        parent::__construct($accNlRecipientResult);
    }

    public function getAccountNewsletterRecipient(): AccountNewsletterRecipientResult
    {
        return $this->object;
    }
}
