<?php 
namespace AcWeb\Annotaions;
use Attribute;
#[Attribute(Attribute::TARGET_PARAMETER)]
class AcWebValueFromHeader {
    public function __construct(public string $header) {}
}

?>