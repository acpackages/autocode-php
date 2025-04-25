<?php
namespace Autocode\Annotaions;
use Attribute;
#[Attribute(Attribute::TARGET_PROPERTY)]
class AcBindJsonProperty {
    public function __construct(public string $key) {}
}

?>