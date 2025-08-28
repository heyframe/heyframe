<?php declare(strict_types=1);

namespace HeyFrame\Core;

/**
 * System-wide defaults that are fixed for performance reasons.
 *
 * @codeCoverageIgnore
 */
final class Defaults
{
    /**
     * Don't depend on this being zh-CN, the underlying language can be overwritten by the installer!
     */
    public const LANGUAGE_SYSTEM = '2fbb5fe2e29a4d70aa5854ce7ce3e20b';
    public const LIVE_VERSION = '0fa91ce3e96a4bc2be4bd9ce752c3425';
    /**
     * Don't depend on this being CNY, the underlying currency can be overwritten by the installer!
     */
    public const CURRENCY = 'b7d2554b0ce847cd82f3ac9bd1c0dfca';
    public const STORAGE_DATE_TIME_FORMAT = 'Y-m-d H:i:s.v';
    /**
     * Do not use STORAGE_DATE_FORMAT for createdAt fields, use STORAGE_DATE_TIME_FORMAT instead
     */
    public const STORAGE_DATE_FORMAT = 'Y-m-d';
}
