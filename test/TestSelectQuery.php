<?php
declare(strict_types=1);
namespace phpMySqlMock;
require_once('src/mysqlMock.class.php');
require_once('test/common_util.php');
use PHPUnit\Framework\TestCase;

final class TestSelectQuery extends TestCase
{
	public function setUp()
	{
	}

	public function tearDown()
	{
	}

    /*
     * @cover mysql_addMockTable
     * @brief 단순한 테이블을 하나 생성합니다.
     */
    public function test_select()
    {
        $answer_host = "1.2.3.4:1258";
        $answer_userid = "rabbit6";
        $answer_password = getEncPassword();
        $answer_tableName = "simpletbl2";
        $answer_tableAttr = array(FIELD_SEQ=>mysql_getFSAutoIncrease(),
                                  FIELD_NAME=>mysql_getFSVarchar(20, false),
                                  FIELD_AGE=>mysql_getFSInteger(false, true));
        $answer_tableData1 = array(FIELD_NAME=>"cat", FIELD_AGE=>3);
        $answer_tableData2 = array(FIELD_NAME=>"tiger", FIELD_AGE=>3);
        $answer_tableData3 = array(FIELD_NAME=>"black tiger", FIELD_AGE=>2);
        $answer_tableData4 = array(FIELD_NAME=>"dog", FIELD_AGE=>2);

        // Success create
        $mock = mysql_addMock($answer_host, $answer_userid, $answer_password);
        mysql_addMockTable($mock, $answer_tableName, $answer_tableAttr);
        $this->assertEquals(mysql_errno($mock), MySqlMockError::NO_ERROR);
        $this->assertEquals(mysql_addMockData($mock, $answer_tableName, $answer_tableData1), MySqlMockError::NO_ERROR);
        $this->assertEquals(mysql_addMockData($mock, $answer_tableName, $answer_tableData2), MySqlMockError::NO_ERROR);
        $this->assertEquals(mysql_addMockData($mock, $answer_tableName, $answer_tableData3), MySqlMockError::NO_ERROR);
        $this->assertEquals(mysql_addMockData($mock, $answer_tableName, $answer_tableData4), MySqlMockError::NO_ERROR);

        // Valid Connection
        $conn = mysql_connect($answer_host, $answer_userid, $answer_password);
        $this->assertEquals(mysql_errno($conn), MySqlMockError::NO_ERROR);

        $result = mysql_query("SELECT name, age FROM ".$answer_tableName, $conn);
        

        mysql_close($conn);
    }
}

