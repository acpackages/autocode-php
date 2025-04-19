<?php 
namespace AcWeb\Annotaions;
use Attribute;
#[Attribute(Attribute::TARGET_PARAMETER)]
class AcWebValueFromPath {
    public function __construct(public string $name) {}
}

?>