<?php
namespace AcWeb\DataDictionary;
require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../../../autocode-data-dictionary/vendor/autoload.php';
require_once __DIR__.'./../../../autocode-extensions/vendor/autoload.php';
use AcDataDictionary\AcDataDictionary;
use AcExtensions\AcArrayExtensions;
use AcExtensions\AcExtensionMethods;
use AcWeb\Core\AcWeb;
use AcWeb\Core\AcWebPath;
use AcWeb\Models\AcWebPathHandlers;

use Autocode\Enums\AcEnumHttpMethod;
class AcDataDictionaryAutoApi {
    public AcWeb $acWeb;
    public string $dataDictionaryName = "";    
    public mixed $excludeTables = [];
    public mixed $includeTables = [];
    public string $pathPrefixForDelete = "delete";
    public string $pathPrefixForInsert = "add";
    public string $pathPrefixForSave = "save";
    public string $pathPrefixForSelect = "get";
    public string $pathPrefixForSelectDistinct = "get-unique";
    public string $pathPrefixForUpdate = "update";
    public string $urlPrefix = "";
    public AcDataDictionary $acDataDictionary;

    public function __construct(AcWeb $acWeb,string $dataDictionaryName = "default") {
        $this->acWeb = $acWeb;
        $this->dataDictionaryName = $dataDictionaryName;
        $this->acDataDictionary = AcDataDictionary::getInstance(dataDictionaryName: $dataDictionaryName);
    }

    public function excludeTable(string $tableName,?bool $delete,?bool $insert,?bool $save,?bool $select,?bool $selectDistinct,?bool $update){
        if($delete ==null && $insert == null && $save == null && $select == null && $selectDistinct == null && $update == null){
            $delete = true;
            $insert = true;
            $save = true;
            $select = true;
            $selectDistinct = true;
            $update = true;
        }
        else{
            if($delete == null){
                $delete = false;
            }
            if($insert == null){
                $insert = false;
            }
            if($save == null){
                $save = false;
            }
            if($select == null){
                $select = false;
            }
            if($selectDistinct == null){
                $selectDistinct = false;
            }
            if($update == null){
                $update = false;
            }
        }        
        $this->excludeTables[$tableName] = [
            "delete" => $delete,
            "insert" => $insert,
            "save" => $save,
            "select" => $select,
            "select_distinct" => $selectDistinct,
            "update" => $update
        ];
    }

    public function includeTable(string $tableName,?bool $delete,?bool $insert,?bool $save,?bool $select,?bool $selectDistinct,?bool $update){
        if($delete ==null && $insert == null && $save == null && $select == null && $selectDistinct == null && $update == null){
            $delete = true;
            $insert = true;
            $save = true;
            $select = true;
            $selectDistinct = true;
            $update = true;
        }
        else{
            if($delete == null){
                $delete = false;
            }
            if($insert == null){
                $insert = false;
            }
            if($save == null){
                $save = false;
            }
            if($select == null){
                $select = false;
            }
            if($selectDistinct == null){
                $selectDistinct = false;
            }
            if($update == null){
                $update = false;
            }
        }        
        $this->includeTables[$tableName] = [
            "delete" => $delete,
            "insert" => $insert,
            "save" => $save,
            "select" => $select,
            "select_distinct" => $selectDistinct,
            "update" => $update
        ];
    }

    public function generate(){
        foreach (AcDataDictionary::getTables(dataDictionaryName:$this->dataDictionaryName) as $acTable) {
            $continueOperation = false;
            $delete = true;
            $insert = true;
            $save = true;
            $select = true;
            $selectDistinct = true;
            $update = true;
            if(sizeof($this->includeTables)>0 && sizeof($this->excludeTables)>0){
                $continueOperation = true;
            }
            else{
                if(sizeof($this->includeTables)>0) {
                    if(isset($this->includeTables[$acTable->tableName])) {
                      $continueOperation = true;
                      $delete = $this->includeTables[$acTable->tableName]["delete"];
                      $insert = $this->includeTables[$acTable->tableName]["insert"];
                      $save = $this->includeTables[$acTable->tableName]["save"];
                      $select = $this->includeTables[$acTable->tableName]["select"];
                      $selectDistinct = $this->includeTables[$acTable->tableName]["select_distinct"];
                      $update = $this->includeTables[$acTable->tableName]["update"];
                    }
                }
                else if(!isset($this->excludeTables[$acTable->tableName])) {
                    $continueOperation = true;
                }
                else if(isset($this->excludeTables[$acTable->tableName])) {
                    $continueOperation = true;
                    $delete = !$this->excludeTables[$acTable->tableName]["delete"];
                    $insert = !$this->excludeTables[$acTable->tableName]["insert"];
                    $save = !$this->excludeTables[$acTable->tableName]["save"];
                    $select = !$this->excludeTables[$acTable->tableName]["select"];
                    $selectDistinct = !$this->excludeTables[$acTable->tableName]["select_distinct"];
                    $update = !$this->excludeTables[$acTable->tableName]["update"];
                }
                if($continueOperation){
                    if($delete){
                        $apiUrl = $this->urlPrefix.'/'.$this->pathPrefixForDelete.'-'.$acTable->getSingularName();
                        
                        $this->acWeb->get($apiUrl,function($params):AcWebPath{
                            $acTable = $params['ac_table'];
                            print_r($acTable);
                            $deleteApiPath = new AcDataDictionaryAutoDeleteApiPath();
                            $deleteApiPath->tableName = $acTable->tableName;
                            return $deleteApiPath;
                        },createParameters:['ac_table'=>$acTable]);                        
                    }
                    if($insert){
                        $apiUrl = $this->urlPrefix.'/'.$this->pathPrefixForInsert.'-'.$acTable->getSingularName();
                        $this->acWeb->get($apiUrl,function($params):AcWebPath{
                            $acTable = $params['ac_table'];
                            $deleteApiPath = new AcDataDictionaryAutoInsertApiPath();
                            $deleteApiPath->tableName = $acTable->tableName;
                            return $deleteApiPath;
                        },createParameters:['ac_table'=>$acTable]);                        
                    }
                    if($save){
                        $apiUrl = $this->urlPrefix.'/'.$this->pathPrefixForSave.'-'.$acTable->getSingularName();
                        $this->acWeb->get($apiUrl,function($params):AcWebPath{
                            $acTable = $params['ac_table'];
                            $deleteApiPath = new AcDataDictionaryAutoSaveApiPath();
                            $deleteApiPath->tableName = $acTable->tableName;
                            return $deleteApiPath;
                        },createParameters:['ac_table'=>$acTable]);                        
                    }
                    if($select){
                        $apiUrl = $this->urlPrefix.'/'.$this->pathPrefixForSelect.'-'.$acTable->getPluralName();
                        $this->acWeb->get($apiUrl,function($params):AcWebPath{
                            $acTable = $params['ac_table'];
                            $deleteApiPath = new AcDataDictionaryAutoSelectApiPath();
                            $deleteApiPath->tableName = $acTable->tableName;
                            return $deleteApiPath;
                        },createParameters:['ac_table'=>$acTable]);                        
                    }
                    if($selectDistinct){
                        $apiUrl = $this->urlPrefix.'/'.$this->pathPrefixForSelectDistinct.'-'.$acTable->getPluralName();
                        $this->acWeb->get($apiUrl,function($params):AcWebPath{
                            $acTable = $params['ac_table'];
                            $deleteApiPath = new AcDataDictionaryAutoSelectDistinctApiPath();
                            $deleteApiPath->tableName = $acTable->tableName;
                            return $deleteApiPath;
                        },createParameters:['ac_table'=>$acTable]);                        
                    }
                    if($update){
                        $apiUrl = $this->urlPrefix.'/'.$this->pathPrefixForUpdate.'-'.$acTable->getSingularName();
                        $this->acWeb->get($apiUrl,function($params):AcWebPath{
                            $acTable = $params['ac_table'];
                            $deleteApiPath = new AcDataDictionaryAutoUpdateApiPath();
                            $deleteApiPath->tableName = $acTable->tableName;
                            return $deleteApiPath;
                        },createParameters:['ac_table'=>$acTable]);                        
                    }
                }
            }
        }
    }
    
}

?>