<?php

namespace Autocode;

use Autocode\Enums\AcEnumEnvironment;

require_once 'AcEnumEnvironment.php';

class AcEnvironment {
    public static AcEnumEnvironment $environment = AcEnumEnvironment::LOCAL;
    public static array $config = [];

    public static function isDevelopment(): bool {
        return self::$environment === AcEnumEnvironment::DEVELOPMENT;
    }

    public static function isLocal(): bool {
        return self::$environment === AcEnumEnvironment::LOCAL;
    }

    public static function isProduction(): bool {
        return self::$environment === AcEnumEnvironment::PRODUCTION;
    }

    public static function isStaging(): bool {
        return self::$environment === AcEnumEnvironment::STAGING;
    }
}

?>