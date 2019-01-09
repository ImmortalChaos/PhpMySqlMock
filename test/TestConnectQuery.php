<?php
declare(strict_types=1);
namespace phpMySqlMock;
require_once('src/mysqlMock.class.php');
use PHPUnit\Framework\TestCase;

final class TestConnectQuery extends TestCase
{
    /*
     * @cover addMock
     * @brief DB가 정상적으로 추가되는지 테스트합니다.
     */
    public function test_addMock()
    {
    	$answer_host = "1.2.3.4:5678";
    	$answer_userid = "rabbit";
    	$answer_pwd = "turtle";
        $this->assertEquals(mysql_countConnect(), 0);

    	$mock = mysql_addMock($answer_host, $answer_userid, $answer_pwd);

        $this->assertEquals(mysql_countConnect(), 1);
        $this->assertEquals($mock->getHost(), $answer_host);
        $this->assertEquals($mock->getUserId(), $answer_userid);
        $this->assertEquals($mock->getPassword(), $answer_pwd);

    	mysql_close($mock);
        $this->assertEquals(mysql_countConnect(), 0);
    }    

    /*
     * @cover addMock
     * @brief DB를 중복해서 등록할경우 하나만 등록되어야 한다.
     */
    public function test_addDuplicateMock()
    {
    	$answer_host = "1.2.3.4:5678";
    	$answer_userid = "rabbit";
    	$answer_pwd = "turtle";
        $this->assertEquals(mysql_countConnect(), 0);

    	$mock1 = mysql_addMock($answer_host, $answer_userid, $answer_pwd);
        $this->assertEquals(mysql_countConnect(), 1);

    	$mock2 = mysql_addMock($answer_host, $answer_userid, $answer_pwd);
        $this->assertEquals(mysql_countConnect(), 1);

    	mysql_close($mock2);
        $this->assertEquals(mysql_countConnect(), 0);
    }   

    /*
     * @cover mysqlConnect
     * @brief DB가 정상적으로 연결되는지 테스트합니다.
     */
    public function test_mysqlConnect()
    {
    	$answer_host = "1.2.3.4:5678";
    	$answer_userid = "rabbit";
    	$answer_pwd = "turtle";
    	// add Test Set
    	$mock = mysql_addMock($answer_host, $answer_userid, $answer_pwd);
        $this->assertEquals(mysql_countConnect(), 1);

        // Wrong Password
    	$conn = mysql_connect($answer_host, $answer_userid, "wrong");
        $this->assertEquals($conn, mysqlMockError::CONNECT_FAIL);
        $ret = mysql_close($conn);
        $this->assertEquals($ret, mysqlMockError::CONNECT_FAIL);

        // Valid Password
    	$conn = mysql_connect($answer_host, $answer_userid, $answer_pwd);
    	$ret = mysql_close($conn);
        $this->assertEquals(mysql_countConnect(), 0);
        $this->assertEquals($ret, mysqlMockError::NO_ERROR);
    }    
}

