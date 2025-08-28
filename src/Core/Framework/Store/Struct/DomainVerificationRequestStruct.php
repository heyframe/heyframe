<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class DomainVerificationRequestStruct extends Struct
{
    protected string $fileName;

    /**
     * @deprecated tag:v6.8.0 - reason:parameter-name-change - Parameter `filename` will be renamed to `fileName` and become a promoted property
     */
    public function __construct(
        protected string $content,
        string $filename,
    ) {
        $this->fileName = $filename;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getApiAlias(): string
    {
        return 'store_domain_verification_request';
    }
}
