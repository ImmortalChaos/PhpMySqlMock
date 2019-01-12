<?php
declare(strict_types=1);
namespace phpMySqlMock;

const FIELD_SEQ = "seq";
const FIELD_NAME = "name";
const FIELD_AGE = "age";
const FIELD_PRICE = "price";
const FIELD_COLOR = "color";
const DEFAULT_TABLENAME = "sample";
const TEXT_GREEN = "green";
const TEXT_YELLOW = "yellow";
const TEXT_RED = "red";

function getDefalutHost() {
	return "1.2.3.4:3306";
}

function getDefaultUserID() {
	return "cherry";
}

function getEncPassword() {
	return "s0e2a9e8fs92fzz89f3";
}

function basicMockConnection($testCase, $tableName, $tableStruct, $tableDatas) {
	// Create Mock
    $mock = mysql_addMock(getDefalutHost(), getDefaultUserID(), getEncPassword());

    // Create Mock Table
    mysql_addMockTable($mock, $tableName, $tableStruct);
    $testCase->assertEquals(mysql_errno($mock), MySqlMockError::NO_ERROR);

    // Append Table Data
    foreach($tableDatas as $tableData) {
    	$testCase->assertEquals(mysql_addMockData($mock, $tableName, $tableData), MySqlMockError::NO_ERROR);
	}

    // Check Valid Connection
    $conn = mysql_connect(getDefalutHost(), getDefaultUserID(), getEncPassword());
    $testCase->assertEquals(mysql_errno($conn), MySqlMockError::NO_ERROR);	

    return $conn;
}

function getFruitPriceTableStruct() {
    return array(FIELD_SEQ=>mysql_getFSAutoIncrease(),
                 FIELD_NAME=>mysql_getFSVarchar(20, false),
                 FIELD_COLOR=>mysql_getFSVarchar(20, false),
                 FIELD_PRICE=>mysql_getFSInteger(false, true));
}

function getFruitPriceTableDatas() {
    return array(
                array(FIELD_NAME=>"orange", FIELD_PRICE=>1500, FIELD_COLOR=>TEXT_GREEN),
                array(FIELD_NAME=>"apple", FIELD_PRICE=>1000, FIELD_COLOR=>TEXT_GREEN),
                array(FIELD_NAME=>"mango", FIELD_PRICE=>1200, FIELD_COLOR=>TEXT_YELLOW),
                array(FIELD_NAME=>"melon", FIELD_PRICE=>2500, FIELD_COLOR=>TEXT_GREEN),
                array(FIELD_NAME=>"strawberry", FIELD_PRICE=>2500, FIELD_COLOR=>TEXT_RED),
                array(FIELD_NAME=>"banana", FIELD_PRICE=>2800, FIELD_COLOR=>TEXT_YELLOW)
            );

}