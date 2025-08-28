<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Telemetry\Metrics\Metric;

/**
 * @phpstan-type MetricTypeValues = 'histogram'|'gauge'|'counter'|'updown_counter'
 */
enum Type: string
{
    case HISTOGRAM = 'histogram';

    case GAUGE = 'gauge';

    case COUNTER = 'counter';

    case UPDOWN_COUNTER = 'updown_counter';
}
