<?php 
namespace AcWeb\Annotaions;
use Attribute;
#[Attribute(Attribute::TARGET_METHOD)]
class AcWebRouteMeta {
    public function __construct(
        public ?string $summary = '',
        public ?string $description = '',
        public array $parameters = [],
        public array $tags = []
    ) {}
}

?>