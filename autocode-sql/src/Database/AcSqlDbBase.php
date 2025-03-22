<?php

namespace AcSql\Database;

require_once __DIR__.'./../../../autocode/vendor/autoload.php';
require_once __DIR__.'./../../../autocode-data-dictionary/vendor/autoload.php';
require_once __DIR__.'./../Enums/AcEnumSqlDatabaseType.php';
require_once 'AcSqlDatabase.php';

use AcDataDictionary\AcDataDictionary;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\Models\AcDDTableFieldProperty;
use AcDataDictionary\Models\AcDDTableProperty;
use AcDataDictionary\Models\AcDDTableRowEvent;
use AcSql\Daos\AcBaseSqlDao;
use AcSql\Daos\AcMysqlDao;
use AcSql\Enums\AcEnumSqlDatabaseType;
use AcSql\Models\AcSqlConnection;
use Autocode\AcLogger;

class AcSqlDbBase {
    
    public AcDDTable $acDDTable;
    public string $databaseType = AcEnumSqlDatabaseType::UNKNOWN;
    public string $dataDictionaryName = "default";
    public AcLogger $logger;
    public ?AcBaseSqlDao $dao;
    public ?AcSqlConnection $sqlConnection;
    

    public function __construct(string $dataDictionaryName = "default") {
        $this->logger = new AcLogger();
        $this->databaseType = AcSqlDatabase::$databaseType;
        $this->dataDictionaryName = AcSqlDatabase::$dataDictionaryName;
        $this->sqlConnection = AcSqlDatabase::$sqlConnection;
        if($this->databaseType == AcEnumSqlDatabaseType::MYSQL){
            $this->dao = new AcMysqlDao();
            $this->dao->setSqlConnection($this->sqlConnection);
        }
    }
    
}
