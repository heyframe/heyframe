<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Twig\Extension;

use HeyFrame\Core\Content\Media\MediaEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\UrlEncoder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

#[Package('framework')]
class UrlEncodingTwigFilter extends AbstractExtension
{
    /**
     * @return list<TwigFilter>
     */
    public function getFilters()
    {
        return [
            new TwigFilter('sw_encode_url', $this->encodeUrl(...)),
            new TwigFilter('sw_encode_media_url', $this->encodeMediaUrl(...)),
        ];
    }

    public function encodeUrl(?string $mediaUrl): ?string
    {
        return UrlEncoder::encodeUrl($mediaUrl);
    }

    public function encodeMediaUrl(?MediaEntity $media): ?string
    {
        if ($media === null || !$media->hasFile()) {
            return null;
        }

        return $media->getUrl();
    }
}
