<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Address\Error;

use HeyFrame\Core\Checkout\Cart\Error\ErrorRoute;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class ShippingAddressCountryRegionMissingError extends CountryRegionMissingError
{
    protected const KEY = parent::KEY . '-shipping-address';

    public function __construct(CustomerAddressEntity $address)
    {
        $this->message = \sprintf(
            'A country region needs to be defined for the billing address "%s %s %s %s".',
            $address->getFirstName(),
            $address->getLastName(),
            $address->getZipcode(),
            $address->getCity()
        );

        $this->parameters = [
            'addressId' => $address->getId(),
        ];

        parent::__construct($this->message);
    }

    public function getId(): string
    {
        return self::KEY;
    }

    public function getRoute(): ?ErrorRoute
    {
        return new ErrorRoute(
            'frontend.account.address.edit.page',
            $this->parameters
        );
    }
}
