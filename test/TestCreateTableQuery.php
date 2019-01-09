<?php
declare(strict_types=1);
namespace phpMySqlMock;
require_once('src/mysqlMock.class.php');
require_once('test/common_util.php');
use PHPUnit\Framework\TestCase;

final class TestCreateTableQuery extends TestCase
{
	public function setUp()
	{
	}

	public function tearDown()
	{
	}

    /*
     * @cover mysql_addMockTable
     * @brief 테이블 생성 오류 여부를 테스트합니다.
     */
    public function test_createTableError()
    {
    	$answer_host = "1.2.3.4:1357";
    	$answer_userid = "rabbit4";
    	$answer_password = getEncPassword();
        $answer_tableName = "sample4";
        $answer_tableAttr = array(FIELD_SEQ=>array(MySqlMockAttribute::ATTR_FIELDTYPE=>MySqlMockAttribute::FIELD_INTEGER));

        // Error : Wrong Parameter
        $this->assertEquals(mysql_addMockTable("MOCK", $answer_tableName, $answer_tableAttr), MySqlMockError::ERROR);
        $this->assertEquals(mysql_addMockTable(1, $answer_tableName, $answer_tableAttr), MySqlMockError::ERROR);

    	// Error : Empty Table Name
    	$mock = mysql_addMock($answer_host, $answer_userid, $answer_password);
        mysql_addMockTable($mock, "", $answer_tableAttr);
        $this->assertEquals(mysql_errno($mock), MySqlMockError::ERROR);

        // Error : Empty Table Name
        mysql_addMockTable($mock, $answer_tableName, array());
        $this->assertEquals(mysql_errno($mock), MySqlMockError::ERROR);

        // Success create
        mysql_addMockTable($mock, $answer_tableName, $answer_tableAttr);
        $this->assertEquals(mysql_errno($mock), MySqlMockError::NO_ERROR);

    	mysql_close($mock);
    }

    /*
     * @cover mysql_addMockTable
     * @brief 단순한 테이블을 하나 생성합니다.
     */
    public function test_createTableExample1()
    {
        $answer_host = "1.2.3.4:1257";
        $answer_userid = "rabbit5";
        $answer_password = getEncPassword();
        $answer_tableName = "simpletbl1";
        $answer_tableAttr = array(FIELD_SEQ=>mysql_getFSAutoIncrease(),
                                  FIELD_NAME=>mysql_getFSVarchar(8, false),
                                  FIELD_AGE=>mysql_getFSInteger(false, true));
        $answer_tableData1 = array(FIELD_NAME=>"cat", FIELD_AGE=>3);
        $answer_tableData2 = array(FIELD_NAME=>"tiger", FIELD_AGE=>-1);
        $answer_tableData3 = array(FIELD_NAME=>"black sheep", FIELD_AGE=>2);
        $answer_tableData4 = array(FIELD_NAME=>"dog", FIELD_AGE=>2);

        // Success create
        $mock = mysql_addMock($answer_host, $answer_userid, $answer_password);
        mysql_addMockTable($mock, $answer_tableName, $answer_tableAttr);
        $this->assertEquals(mysql_errno($mock), MySqlMockError::NO_ERROR);

        $this->assertEquals(mysql_addMockData($mock, $answer_tableName, $answer_tableData1), MySqlMockError::NO_ERROR);
        $this->assertEquals(mysql_addMockData($mock, $answer_tableName, $answer_tableData2), MySqlMockError::ERROR);
        $this->assertEquals(mysql_addMockData($mock, $answer_tableName, $answer_tableData3), MySqlMockError::ERROR);
        $this->assertEquals(mysql_addMockData($mock, $answer_tableName, $answer_tableData4), MySqlMockError::NO_ERROR);

        mysql_close($mock);
    }
}

