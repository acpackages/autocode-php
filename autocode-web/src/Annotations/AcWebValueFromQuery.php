<?php 
namespace AcWeb\Annotaions;
use Attribute;
#[Attribute(Attribute::TARGET_PARAMETER)]
class AcWebValueFromQuery {
    public function __construct(public ?string $key = null) {}
}
?>