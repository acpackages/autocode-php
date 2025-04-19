<?php 
namespace AcWeb\Annotaions;
use Attribute;
#[Attribute(Attribute::TARGET_METHOD)]
class AcWebRouteMetaParemeter{
    public function __construct(
        public string $description = '',        
        public string $in = '',
        public string $name = '',
        public string $required = '',
        public string $explode = '',
        public array $schema = []
    ) {}
}

?>