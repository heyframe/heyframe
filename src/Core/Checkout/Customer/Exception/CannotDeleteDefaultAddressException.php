<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Exception;

use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class CannotDeleteDefaultAddressException extends CustomerException
{
    public function __construct(string $id)
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            self::CUSTOMER_ADDRESS_IS_DEFAULT,
            'Customer address with id "{{ addressId }}" is a default address and cannot be deleted.',
            ['addressId' => $id]
        );
    }
}
