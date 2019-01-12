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
     * @cover mysql_query
     * @brief 단순한 문장에 대해서 쿼리합니다.
     */
    public function test_simple_select()
    {
        $tableAttr = array(FIELD_SEQ=>mysql_getFSAutoIncrease(),
                           FIELD_NAME=>mysql_getFSVarchar(20, false),
                           FIELD_AGE=>mysql_getFSInteger(false, true));
        $tableData = array(
                            array(FIELD_NAME=>"cat", FIELD_AGE=>3),
                            array(FIELD_NAME=>"tiger", FIELD_AGE=>3),
                            array(FIELD_NAME=>"black tiger", FIELD_AGE=>2),
                            array(FIELD_NAME=>"dog", FIELD_AGE=>2)
                        );

        $conn = basicMockConnection($this, DEFAULT_TABLENAME, $tableAttr, $tableData);

        $result = mysql_query("SELECT name, age FROM ".DEFAULT_TABLENAME, $conn);
        $this->assertEquals(mysql_fetch_array($result), array("cat", 3));
        $this->assertEquals(mysql_fetch_array($result), array("tiger", 3));
        $this->assertEquals(mysql_fetch_array($result), array("black tiger", 2));
        $this->assertEquals(mysql_fetch_array($result), array("dog", 2));

        mysql_close($conn);
    }

    /*
     * @cover mysql_query
     * @brief 단순한 문장에 대해서 쿼리합니다.
     */
    public function test_simple_select_where1()
    {
       $tableAttr = array(FIELD_SEQ=>mysql_getFSAutoIncrease(),
                           FIELD_NAME=>mysql_getFSVarchar(20, false),
                           FIELD_AGE=>mysql_getFSInteger(false, true));
        $tableData = array(
                            array(FIELD_NAME=>"rabbit", FIELD_AGE=>3),
                            array(FIELD_NAME=>"white tiger", FIELD_AGE=>1),
                            array(FIELD_NAME=>"frog", FIELD_AGE=>2),
                            array(FIELD_NAME=>"lion", FIELD_AGE=>4)
                        );

        $conn = basicMockConnection($this, DEFAULT_TABLENAME, $tableAttr, $tableData);

        $result = mysql_query("SELECT name, age FROM ".DEFAULT_TABLENAME." WHERE age < 3", $conn);

        $this->assertEquals(mysql_fetch_array($result), array("white tiger", 1));
        $this->assertEquals(mysql_fetch_array($result), array("frog", 2));

        mysql_close($conn);
    }

    /*
     * @cover mysql_query
     * @brief 쿼리 구문 오류가 있는 경우
     */
    public function test_simple_select_where2()
    {
        $conn = basicMockConnection($this, DEFAULT_TABLENAME, getFruitPriceTableStruct(), getFruitPriceTableDatas());

        $result = mysql_query("SELECT name FROM ".DEFAULT_TABLENAME." WHERE price > 1000 xxx color = \"green\"", $conn);

        $this->assertEquals(mysql_errno($conn), MySqlMockError::INVALID_QUERY);
        $this->assertEquals(mysql_affected_rows($conn), 0);

        mysql_close($conn);
    }

    /*
     * @cover mysql_query
     * @brief 단순한 문장에 대해서 쿼리합니다.
     */
    public function test_simple_select_where3()
    {
        $conn = basicMockConnection($this, DEFAULT_TABLENAME, getFruitPriceTableStruct(), getFruitPriceTableDatas());

        $result = mysql_query("SELECT name, price, color FROM ".DEFAULT_TABLENAME." WHERE price > 1000 and color = \"green\"", $conn);

        $this->assertEquals(mysql_fetch_array($result), array("orange", 1500, TEXT_GREEN));
        $this->assertEquals(mysql_fetch_array($result), array("melon", 2500, TEXT_GREEN));
        $this->assertEquals(mysql_affected_rows($conn), 2);

        mysql_close($conn);
    }

    /*
     * @cover mysql_query
     * @brief 단순한 문장에 대해서 쿼리합니다.
     */
    public function test_simple_select_where4()
    {
        $conn = basicMockConnection($this, DEFAULT_TABLENAME, getFruitPriceTableStruct(), getFruitPriceTableDatas());

        $result = mysql_query("SELECT name, price, color FROM ".DEFAULT_TABLENAME." WHERE price > 1000 and price < 2000 or color = \"red\"", $conn);

        $this->assertEquals(mysql_fetch_array($result), array("orange", 1500, TEXT_GREEN));
        $this->assertEquals(mysql_fetch_array($result), array("mango", 1200, TEXT_YELLOW));
        $this->assertEquals(mysql_fetch_array($result), array("strawberry", 2500, TEXT_RED));
        $this->assertEquals(mysql_affected_rows($conn), 3);

        mysql_close($conn);
    }

    /*
     * @cover mysql_query
     * @brief 조건에 맞는 데이터가 없는경우
     */
    public function test_simple_select_where5()
    {
        $conn = basicMockConnection($this, DEFAULT_TABLENAME, getFruitPriceTableStruct(), getFruitPriceTableDatas());

        $result = mysql_query("SELECT name FROM ".DEFAULT_TABLENAME." WHERE price < 500", $conn);

        $this->assertEquals(mysql_affected_rows($conn), 0);

        mysql_close($conn);
    }


}

