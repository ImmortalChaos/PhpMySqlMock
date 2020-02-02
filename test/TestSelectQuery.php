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
        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"cat", FIELD_AGE=>3));
        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"tiger", FIELD_AGE=>3));
        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"black tiger", FIELD_AGE=>2));
        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"dog", FIELD_AGE=>2));

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

        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"white tiger", FIELD_AGE=>1));
        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"frog", FIELD_AGE=>2));

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

        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"orange", FIELD_PRICE=>1500, FIELD_COLOR=>TEXT_GREEN));
        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"melon", FIELD_PRICE=>2500, FIELD_COLOR=>TEXT_GREEN));
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

        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"orange", FIELD_PRICE=>1500, FIELD_COLOR=>TEXT_GREEN));
        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"mango", FIELD_PRICE=>1200, FIELD_COLOR=>TEXT_YELLOW));
        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"strawberry", FIELD_PRICE=>2500, FIELD_COLOR=>TEXT_RED));
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

    /*
     * @cover mysql_query
     * @brief Select 쿼리에 *기호 사용시
     */
    public function test_simple_select_where6()
    {
        $conn = basicMockConnection($this, DEFAULT_TABLENAME, getFruitPriceTableStruct(), getFruitPriceTableDatas());

        $result = mysql_query("SELECT * FROM ".DEFAULT_TABLENAME." WHERE price < 1100", $conn);

        $this->assertEquals(mysql_fetch_array($result), array(FIELD_SEQ=>2, FIELD_NAME=>"apple", FIELD_COLOR=>TEXT_GREEN, FIELD_PRICE=>1000));
        $this->assertEquals(mysql_affected_rows($conn), 1);

        mysql_close($conn);
    }

    /*
		 * like 검색시 %기호가 없는경우 테스트
     * @cover mysql_query
     * @brief Select 쿼리에 like 사용시
     */
    public function test_like_select_where()
    {
        $conn = basicMockConnection($this, DEFAULT_TABLENAME, getFruitPriceTableStruct(), getFruitPriceTableDatas());

        $result = mysql_query("SELECT name, price, color FROM ".DEFAULT_TABLENAME." WHERE name like \"strawberry\"", $conn);
				$this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"strawberry", FIELD_PRICE=>2500, FIELD_COLOR=>TEXT_RED));
        $this->assertEquals(mysql_affected_rows($conn), 1);

        mysql_close($conn);
    }

		/*
		 * like 검색시 %검색어%일경우 테스트
     * @cover mysql_query
     * @brief Select 쿼리에 like 사용시
     */
    public function test_like_select_where_all()
    {
        $conn = basicMockConnection($this, DEFAULT_TABLENAME, getFruitPriceTableStruct(), getFruitPriceTableDatas());

        $result = mysql_query("SELECT name, price, color FROM ".DEFAULT_TABLENAME." WHERE name like \"%ang%\"", $conn);
				$this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"orange", FIELD_PRICE=>1500, FIELD_COLOR=>TEXT_GREEN));
        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"mango", FIELD_PRICE=>1200, FIELD_COLOR=>TEXT_YELLOW));
        $this->assertEquals(mysql_affected_rows($conn), 2);

        mysql_close($conn);
    }

    /*
		 * like 검색시 검색어%일경우 테스트
     * @cover mysql_query
     * @brief Select 쿼리에 like 사용시
     */
    public function test_like_select_where_right()
    {
        $conn = basicMockConnection($this, DEFAULT_TABLENAME, getFruitPriceTableStruct(), getFruitPriceTableDatas());

        $result = mysql_query("SELECT name, price, color FROM ".DEFAULT_TABLENAME." WHERE name like \"a%\"", $conn);
				$this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"apple", FIELD_PRICE=>1000, FIELD_COLOR=>TEXT_GREEN));
        $this->assertEquals(mysql_affected_rows($conn), 1);

        mysql_close($conn);
    }

    /*
		 * like 검색시 %검색어일경우 테스트
     * @cover mysql_query
     * @brief Select 쿼리에 like 사용시
     */
    public function test_like_select_where_left()
    {
        $conn = basicMockConnection($this, DEFAULT_TABLENAME, getFruitPriceTableStruct(), getFruitPriceTableDatas());

        $result = mysql_query("SELECT name, price, color FROM ".DEFAULT_TABLENAME." WHERE name like \"%e\"", $conn);
				$this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"orange", FIELD_PRICE=>1500, FIELD_COLOR=>TEXT_GREEN));
        $this->assertEquals(mysql_fetch_array($result), array(FIELD_NAME=>"apple", FIELD_PRICE=>1000, FIELD_COLOR=>TEXT_GREEN));
        $this->assertEquals(mysql_affected_rows($conn), 2);

        mysql_close($conn);
    }
}
