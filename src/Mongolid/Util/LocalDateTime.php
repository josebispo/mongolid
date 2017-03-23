<?php

namespace Mongolid\Util;

use DateTime;
use DateTimeZone;
use MongoDB\BSON\UTCDateTime;

/**
 * This class is responsible to convert UTCDateTime from MongoDB driver in
 * local date time.
 *
 * It will be unnecessary once MongoDB driver fixes a know issue in UTCDateTime.
 * The proposal was created, for more information:
 *
 * @see https://jira.mongodb.org/browse/PHPC-760
 */
class LocalDateTime
{
    /**
     * Retrieves DateTime instance using default timezone
     *
     * @param UTCDateTime $date
     *
     * @return DateTime
     */
    public static function get(UTCDateTime $date): DateTime
    {
        return new DateTime(
            $date->toDateTime()->format('Y-m-d H:i:s'),
            new DateTimeZone(date_default_timezone_get())
        );
    }
}
