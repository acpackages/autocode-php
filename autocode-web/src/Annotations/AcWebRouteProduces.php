<?php 
namespace AcWeb\Annotaions;
use Attribute;
#[Attribute(Attribute::TARGET_METHOD)]
class AcWebRouteProduces {
    public function __construct(public string $contentType) {}
}
?>