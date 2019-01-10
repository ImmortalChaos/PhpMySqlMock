<?php
declare(strict_types=1);
namespace phpMySqlMock;
require_once('src/mysqlMock.class.php');
require_once('test/common_util.php');
use PHPUnit\Framework\TestCase;

final class TestAnalyzeQuery extends TestCase
{
	public function setUp()
	{
	}

	public function tearDown()
	{
	}

    /*
     * @cover explodeQuery
     * @brief DB Query를 가장 작은 단위로 분해합니다.
     */
    public function test_explodeQuery1()
    {
        $ret = explodeQuery("SELECT * FROM tableA");
        
        $this->assertEquals($ret, array("SELECT", "*", "FROM", "tableA"));
    }    

    /*
     * @cover explodeQuery
     * @brief DB Query를 가장 작은 단위로 분해합니다.
     */
    public function test_explodeQuery2()
    {
        $ret = explodeQuery("SELECT seq, name FROM table WHERE name = \"Black Tiger, K\" and age < 21 and sex = 1 LIMIT 15, 10");
        $answer = array("SELECT", "seq", "name", "FROM", "table", "WHERE", "name", "=", "\"Black Tiger, K\"", "and", "age", "<", "21", "and", "sex", "=", "1", "LIMIT", "15", "10");
        
        $this->assertEquals($ret, $answer);
    }    

    /*
     * @cover explodeQuery
     * @brief DB Query를 가장 작은 단위로 분해합니다.
     */
    public function test_explodeQuery3()
    {
        $ret = explodeQuery("INSERT INTO tableB (field1, field2) VALUES ('VAL1', 2)");
        
        $this->assertEquals($ret, array("INSERT", "INTO", "tableB", "field1", "field2", "VALUES", "'VAL1'", "2"));
    }    

    /*
     * @cover MySqlMockParseQuery
     * @brief DB Query를 분석합니다.
     */
    public function test_selectQuery1()
    {
        $qryObj = new MySqlMockParseQuery("SELECT seq, name FROM address WHERE name = \"Black 'Tiger', K\" and age < 21 and sex = 1 LIMIT 15, 10");

        $this->assertEquals($qryObj->getTableName(), "address");
        $this->assertEquals($qryObj->getSelect(), array("seq", "name"));
        $this->assertEquals($qryObj->getWhere(), array(array("name", "=", "\"Black 'Tiger', K\""), "and", array("age", "<", "21"), "and", array("sex","=","1")));
        $this->assertEquals($qryObj->getLimit(), array("15", "10"));
    } 

    /*
     * @cover MySqlMockParseQuery
     * @brief DB Query를 분석합니다.
     */
    public function test_selectQuery2()
    {
        $qryObj = new MySqlMockParseQuery("SELECT score FROM scoreboard WHERE name = 'Black Smith, \"J\"' LIMIT 100");

        $this->assertEquals($qryObj->getTableName(), "scoreboard");
        $this->assertEquals($qryObj->getSelect(), array("score"));
        $this->assertEquals($qryObj->getWhere(), array(array("name", "=", "'Black Smith, \"J\"'")));
        $this->assertEquals($qryObj->getLimit(), array("100"));
    }  

    /*
     * @cover MySqlMockParseQuery
     * @brief DB Query를 분석합니다.
     */
    public function test_selectQuery3()
    {
        $qryObj = new MySqlMockParseQuery("SELECT price FROM money WHERE date = \"20180109\" ORDER BY date ASC");

        $this->assertEquals($qryObj->getTableName(), "money");
        $this->assertEquals($qryObj->getSelect(), array("price"));
        $this->assertEquals($qryObj->getWhere(), array(array("date", "=", "\"20180109\"")));
        $this->assertEquals($qryObj->getOrder(), array("date", "ASC"));
    }

    /*
     * @cover MySqlMockParseQuery
     * @brief DB Query를 분석합니다.
     */
    public function test_insertQuery1()
    {
        $qryObj = new MySqlMockParseQuery("INSERT INTO addbook (name, age) VALUES ('KAY', 19)");

        $this->assertEquals($qryObj->getTableName(), "addbook");
        $this->assertEquals($qryObj->getInsert(), array("name", "age"));
        $this->assertEquals($qryObj->getValues(), array("'KAY'", "19"));
    }  
}

