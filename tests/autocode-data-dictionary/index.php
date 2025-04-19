Autocode SQL
<br>
<?php 
require '../../autocode-data-dictionary/vendor/autoload.php';
use AcDataDictionary\Models\AcDataDictionary;
$dataDictionaryJson = file_get_contents('../assets/data_dictionary.json');
AcDataDictionary::registerDataDictionaryJsonString($dataDictionaryJson);
$dataDictionary = AcDataDictionary::fromJsonString($dataDictionaryJson);
// print_r($dataDictionary->toJson());
$table = $dataDictionary->getTable('companies');
print_r($table->toJson());
?>