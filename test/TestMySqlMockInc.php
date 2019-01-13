<?php
declare(strict_types=1);
require_once('src/mysqlMock.class.php');
require_once('test/common_util.php');
require_once('src/mysqlMock.inc.php');
use PHPUnit\Framework\TestCase;

final class TestMySqlMockInc extends TestCase
{
	public function setUp()
	{
	}

	public function tearDown()
	{
	}

    /*
     * @cover mysql_connection, mysql_query, mysql_fetch_array, mysql_affected_rows
     * @brief Select 쿼리에 *기호 사용시
     */
    public function test_simple_select()
    {
        $conn = phpMySqlMock\basicMockConnection($this, phpMySqlMock\DEFAULT_TABLENAME, phpMySqlMock\getFruitPriceTableStruct(), phpMySqlMock\getFruitPriceTableDatas());

        $result = mysql_query("SELECT * FROM ".phpMySqlMock\DEFAULT_TABLENAME." WHERE price < 1100", $conn);

        $this->assertEquals(mysql_fetch_array($result), array(phpMySqlMock\FIELD_SEQ=>2, phpMySqlMock\FIELD_NAME=>"apple", phpMySqlMock\FIELD_COLOR=>"green", phpMySqlMock\FIELD_PRICE=>1000));
        $this->assertEquals(mysql_affected_rows($conn), 1);
        $this->assertEquals(mysql_error($conn), "");
        $this->assertEquals(mysql_errno($conn), phpMySqlMock\MySqlMockError::NO_ERROR);

        mysql_close($conn);
    }

    /*
     * @cover mysqlm_connection, mysqlm_query, mysqlm_fetch_array, mysqlm_affected_rows
     * @brief Select 쿼리에 *기호 사용시
     */
    public function test_simple_selectm()
    {
        $conn = phpMySqlMock\basicMockConnection($this, phpMySqlMock\DEFAULT_TABLENAME, phpMySqlMock\getFruitPriceTableStruct(), phpMySqlMock\getFruitPriceTableDatas());

        $result = mysqlm_query("SELECT * FROM ".phpMySqlMock\DEFAULT_TABLENAME." WHERE price = 1500", $conn);

        $this->assertEquals(mysqlm_fetch_array($result), array(phpMySqlMock\FIELD_SEQ=>1, phpMySqlMock\FIELD_NAME=>"orange", phpMySqlMock\FIELD_COLOR=>"green", phpMySqlMock\FIELD_PRICE=>1500));
        $this->assertEquals(mysqlm_affected_rows($conn), 1);
        $this->assertEquals(mysqlm_error($conn), "");
        $this->assertEquals(mysqlm_errno($conn), phpMySqlMock\MySqlMockError::NO_ERROR);

        mysqlm_close($conn);
    }    
}

