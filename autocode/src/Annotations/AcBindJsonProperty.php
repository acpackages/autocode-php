<?php
namespace Autocode\Annotaions;
use Attribute;
#[Attribute(Attribute::TARGET_PROPERTY)]
class AcBindJsonProperty {
    public function __construct(public ?string $key = null,public ?string $arrayType = null,public ?bool $skipInFromJson = null,public ?bool $skipInToJson = null) {}
}

?>