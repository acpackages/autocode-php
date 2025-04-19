<?php 
namespace AcWeb\Annotaions;
use Attribute;
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AcWebMiddleware {
    public function __construct(public string $middlewareClass) {}
}


?>