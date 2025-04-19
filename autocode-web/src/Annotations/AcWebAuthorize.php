<?php 
namespace AcWeb\Annotaions;
use Attribute;
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class AcWebAuthorize {
    public function __construct(public ?array $roles = null) {}
}

?>