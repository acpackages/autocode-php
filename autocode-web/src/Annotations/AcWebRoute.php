<?php
namespace AcWeb\Annotaions;
use Attribute;
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class AcWebRoute {
    public function __construct(public string $path, public string $method = 'GET') {}
}

?>