<?php
namespace AcDoc\Core;

use AcDoc\Models\AcDocumentation;
use AcDoc\Models\AcParameter;
use AcDoc\Models\AcReturnType;
use AcDoc\Models\AcException;
use AcDoc\Models\AcExample;

class AcDocParser
{
    
    private function parseDocArray(array $data): AcDocumentation{
        return AcDocumentation::fromJson($data);
    }

    public function parseFile(string $filePath): array
{
    $acDocs = [];

    // Get the file contents
    $fileContents = file_get_contents($filePath);

    // Match all block comments (including multi-line comments)
    preg_match_all('/\/\*\*([^*]|\*(?!\/))*\*\//', $fileContents, $matches);

    foreach ($matches[0] as $docBlock) {
        // Remove the `@acDoc` tag and trim unnecessary spaces and line breaks
        if (preg_match('/@acDoc\s*\{(.+?)\}/s', $docBlock, $docMatch)) {
            // Clean up the docMatch by removing the `@acDoc` and leading `*` at the beginning of each line
            // Remove leading stars and spaces
            $cleanedDocBlock = preg_replace('/^\s*\*\s?/', '', $docMatch[1]);

            // Remove any remaining `*` or spaces at the start of each line
            $cleanedDocBlock = "{".preg_replace('/\n\s*\*/', '', $cleanedDocBlock)."}";
            // Decode the cleaned JSON string into an associative array
            $docData = json_decode($cleanedDocBlock, true);

            // If the decoding was successful and the data is an array
            if (is_array($docData)) {
                // Parse and add the doc data
                $acDocs[] = $this->parseDocArray($docData);
            }
        }
    }

    return $acDocs;
}

    
}

?>