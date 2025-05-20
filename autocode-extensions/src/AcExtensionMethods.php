<?php

namespace AcExtensions;

require_once "extensions/AcArrayExtensions.php";
require_once "extensions/AcBlobExtensions.php";
require_once "extensions/AcFileExtensions.php";
require_once "extensions/AcNumberExtensions.php";
require_once "extensions/AcObjectExtensions.php";
require_once "extensions/AcStringExtensions.php";

class AcExtensionMethods {
    use AcArrayExtensions, AcBlobExtensions, AcFileExtensions, AcNumberExtensions, AcObjectExtensions, AcStringExtensions {
        AcArrayExtensions::containsKey insteadof AcObjectExtensions;
        AcArrayExtensions::isEmpty insteadof AcObjectExtensions, AcStringExtensions;
        AcArrayExtensions::isNotEmpty insteadof AcObjectExtensions, AcStringExtensions;

        AcArrayExtensions::containsKey as arrayContainsKey;
        AcArrayExtensions::difference as arrayDifference;
        AcArrayExtensions::differenceSymmetrical as arrayDifferenceSymmetrical;
        AcArrayExtensions::intersection as arrayIntersection;
        AcArrayExtensions::isEmpty as arrayIsEmpty;
        AcArrayExtensions::isNotEmpty as arrayIsNotEmpty;
        AcArrayExtensions::prepend as arrayPrepend;
        AcArrayExtensions::remove as arrayRemove;
        AcArrayExtensions::removeByIndex as arrayRemoveByIndex;
        AcArrayExtensions::union as arrayUnion;
        AcArrayExtensions::toObject as arrayToObject;

        AcBlobExtensions::toBase64 as blobToBase64;
   
        AcFileExtensions::toBlobJson as fileToBlobJson;
        AcFileExtensions::toBytesJson as fileToBytesJson;
        
        AcNumberExtensions::isEven as numberIsEven;
        AcNumberExtensions::isOdd as numberIsOdd;
        AcNumberExtensions::round as numberRound;
        
        AcObjectExtensions::changes as objectChanges;
        AcObjectExtensions::clone as objectClone;
        AcObjectExtensions::copyFrom as objectCopyFrom;
        AcObjectExtensions::copyTo as objectCopyTo;
        AcObjectExtensions::filter as objectFilter;
        AcObjectExtensions::isEmpty as objectIsEmpty;
        AcObjectExtensions::isNotEmpty as objectIsNotEmpty;
        AcObjectExtensions::isSame as objectIsSame;
        AcObjectExtensions::toQueryString as objectToQueryString;
        
        AcStringExtensions::getExtension as stringGetExtension;        
        AcStringExtensions::isEmpty as stringIsEmpty;
        AcStringExtensions::isJson as stringIsJson;
        AcStringExtensions::isNotEmpty as stringIsNotEmpty;
        AcStringExtensions::isNumeric as stringIsNumeric;
        AcStringExtensions::parseJsonToArray as stringParseJsonToArray;
        AcStringExtensions::parseJsonToObject as stringParseJsonToObject;
        AcStringExtensions::random as random;
        AcStringExtensions::regexMatch as stringRegexMatch;
        AcStringExtensions::toCapitalCase as stringToCapitalCase;
    }
    
}

?>