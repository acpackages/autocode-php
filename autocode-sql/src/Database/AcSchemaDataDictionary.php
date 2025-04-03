<?php

namespace AcSql\Database;
require_once __DIR__ . './../../../autocode-data-dictionary/vendor/autoload.php';
use AcDataDictionary\Enums\AcEnumDDFieldType;
use AcDataDictionary\Enums\AcEnumDDFieldProperty;
use AcDataDictionary\Models\AcDDTable;
use AcDataDictionary\Models\AcDDTableField;
use AcDataDictionary\AcDataDictionary;
use AcDataDictionary\Models\AcDDTableFieldProperty; 
class AcSchemaManagerTables {
    const SCHEMA_DETAILS = "_ac_schema_details";
    const SCHEMA_LOGS = "_ac_schema_logs";
}

class TblSchemaDetails {
    const AC_SCHEMA_DETAIL_ID = "ac_schema_detail_id";
    const AC_SCHEMA_DETAIL_KEY = "ac_schema_detail_key";
    const AC_SCHEMA_DETAIL_STRING_VALUE = "ac_schema_detail_string_value";
    const AC_SCHEMA_DETAIL_NUMERIC_VALUE = "ac_schema_detail_numeric_value";
}

class TblSchemaLogs {
    const AC_SCHEMA_LOG_ID = "ac_schema_log_id";
    const AC_SCHEMA_OPERATION = "ac_schema_operation";
    const AC_SCHEMA_ENTITY_TYPE = "ac_schema_entity_type";
    const AC_SCHEMA_ENTITY_NAME = "ac_schema_entity_name";
    const AC_SCHEMA_OPERATION_STATEMENT = "ac_schema_operation_statement";
    const AC_SCHEMA_OPERATION_RESULT = "ac_schema_operation_result";
    const AC_SCHEMA_OPERATION_TIMESTAMP = "ac_schema_operation_timestamp";
}

class SchemaDetails {
    const KEY_CREATED_ON = "CREATED_ON";
    const KEY_DATA_DICTIONARY_VERSION = "DATA_DICTIONARY_VERSION";
    const KEY_LAST_UPDATED_ON = "LAST_UPDATED_ON";
}

class AcSMDataDictionary {

    const DATA_DICTIONARY_NAME = "_ac_schema";
    const DATA_DICTIONARY = [
        AcDataDictionary::KEY_VERSION => 1,
        AcDataDictionary::KEY_TABLES => [
            AcSchemaManagerTables::SCHEMA_DETAILS => [
                AcDDTable::KEY_TABLE_NAME => AcSchemaManagerTables::SCHEMA_DETAILS,
                AcDDTable::KEY_TABLE_FIELDS => [
                    TblSchemaDetails::AC_SCHEMA_DETAIL_ID => [
                        AcDDTableField::KEY_FIELD_NAME => TblSchemaDetails::AC_SCHEMA_DETAIL_ID,
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::AUTO_INCREMENT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => [
                            AcEnumDDFieldProperty::PRIMARY_KEY => [
                                AcDDTableFieldProperty::KEY_PROPERTY_NAME => AcEnumDDFieldProperty::PRIMARY_KEY,
                                AcDDTableFieldProperty::KEY_PROPERTY_VALUE => true,
                            ]
                        ]
                    ],
                    TblSchemaDetails::AC_SCHEMA_DETAIL_KEY => [
                        AcDDTableField::KEY_FIELD_NAME => TblSchemaDetails::AC_SCHEMA_DETAIL_KEY,
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::STRING,
                        AcDDTableField::KEY_FIELD_PROPERTIES => [
                            AcEnumDDFieldProperty::CHECK_IN_SAVE => [
                                AcDDTableFieldProperty::KEY_PROPERTY_NAME => AcEnumDDFieldProperty::CHECK_IN_SAVE,
                                AcDDTableFieldProperty::KEY_PROPERTY_VALUE => true,
                            ]
                        ]
                    ],
                    TblSchemaDetails::AC_SCHEMA_DETAIL_STRING_VALUE => [
                        AcDDTableField::KEY_FIELD_NAME => TblSchemaDetails::AC_SCHEMA_DETAIL_STRING_VALUE,
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TEXT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    TblSchemaDetails::AC_SCHEMA_DETAIL_NUMERIC_VALUE => [
                        AcDDTableField::KEY_FIELD_NAME => TblSchemaDetails::AC_SCHEMA_DETAIL_NUMERIC_VALUE,
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::DOUBLE,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ]
                ]
            ],
            AcSchemaManagerTables::SCHEMA_LOGS => [
                AcDDTable::KEY_TABLE_NAME => AcSchemaManagerTables::SCHEMA_LOGS,
                AcDDTable::KEY_TABLE_FIELDS => [
                    TblSchemaLogs::AC_SCHEMA_LOG_ID => [
                        AcDDTableField::KEY_FIELD_NAME => TblSchemaLogs::AC_SCHEMA_LOG_ID,
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::AUTO_INCREMENT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => [
                            AcEnumDDFieldProperty::PRIMARY_KEY => [
                                AcDDTableFieldProperty::KEY_PROPERTY_NAME => AcEnumDDFieldProperty::PRIMARY_KEY,
                                AcDDTableFieldProperty::KEY_PROPERTY_VALUE => true,
                            ]
                        ]
                    ],
                    TblSchemaLogs::AC_SCHEMA_OPERATION => [
                        AcDDTableField::KEY_FIELD_NAME => TblSchemaLogs::AC_SCHEMA_OPERATION,
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::STRING,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE => [
                        AcDDTableField::KEY_FIELD_NAME => TblSchemaLogs::AC_SCHEMA_ENTITY_TYPE,
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TEXT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    TblSchemaLogs::AC_SCHEMA_ENTITY_NAME => [
                        AcDDTableField::KEY_FIELD_NAME => TblSchemaLogs::AC_SCHEMA_ENTITY_NAME,
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TEXT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT => [
                        AcDDTableField::KEY_FIELD_NAME => TblSchemaLogs::AC_SCHEMA_OPERATION_STATEMENT,
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TEXT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT => [
                        AcDDTableField::KEY_FIELD_NAME => TblSchemaLogs::AC_SCHEMA_OPERATION_RESULT,
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TEXT,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ],
                    TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP => [
                        AcDDTableField::KEY_FIELD_NAME => TblSchemaLogs::AC_SCHEMA_OPERATION_TIMESTAMP,
                        AcDDTableField::KEY_FIELD_TYPE => AcEnumDDFieldType::TIMESTAMP,
                        AcDDTableField::KEY_FIELD_PROPERTIES => []
                    ]
                ]
            ]
        ]
    ];
}

?>