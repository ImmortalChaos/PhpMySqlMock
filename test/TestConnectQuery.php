<?php
declare(strict_types=1);
namespace phpMySqlMock;
require_once('src/mysqlMock.class.php');
require_once('test/common_util.php');
use PHPUnit\Framework\TestCase;

final class TestConnectQuery extends TestCase
{
	public function setUp()
	{
	}

	public function tearDown()
	{
	}

    /*
     * @cover addMock
     * @brief DB가 정상적으로 추가되는지 테스트합니다.
     */
    public function test_addMock()
    {
    	$answer_host = "1.2.3.4:1234";
    	$answer_userid = "rabbit1";
    	$answer_password = getEncPassword();
        $this->assertEquals(mysql_countConnect(), 0);

    	$mock = mysql_addMock($answer_host, $answer_userid, $answer_password);

        $this->assertEquals(mysql_countConnect(), 1);
        $this->assertEquals($mock->getHost(), $answer_host);
        $this->assertEquals($mock->getUserId(), $answer_userid);
        $this->assertEquals($mock->getPassword(), $answer_password);

    	mysql_close($mock);
        $this->assertEquals(mysql_countConnect(), 0);
    }    

    /*
     * @cover addMock
     * @brief DB를 중복해서 등록할경우 하나만 등록되어야 한다.
     */
    public function test_addDuplicateMock()
    {
    	$answer_host = "1.2.3.4:2345";
    	$answer_userid = "rabbit2";
    	$answer_password = getEncPassword();
        $this->assertEquals(mysql_countConnect(), 0);

    	mysql_addMock($answer_host, $answer_userid, $answer_password);
        $this->assertEquals(mysql_countConnect(), 1);

    	$mock2 = mysql_addMock($answer_host, $answer_userid, $answer_password);
        $this->assertEquals(mysql_countConnect(), 1);

    	mysql_close($mock2);
        $this->assertEquals(mysql_countConnect(), 0);
    }   

    /*
     * @cover mysql_connect
     * @brief DB가 정상적으로 연결되는지 테스트합니다.
     */
    public function test_mysqlConnect()
    {
    	$answer_host = "1.2.3.4:5678";
    	$answer_userid = "rabbit3";
    	$answer_password = getEncPassword();
    	// add Test Set
    	mysql_addMock($answer_host, $answer_userid, $answer_password);
        $this->assertEquals(mysql_countConnect(), 1);

        // Wrong Password
    	$conn = mysql_connect($answer_host, $answer_userid, "wrong");
        $this->assertEquals($conn, MySqlMockError::CONNECT_FAIL);
        $ret = mysql_close($conn);
        $this->assertEquals($ret, MySqlMockError::CONNECT_FAIL);

        // Valid Password
    	$conn = mysql_connect($answer_host, $answer_userid, $answer_password);
    	$ret = mysql_close($conn);
        $this->assertEquals(mysql_countConnect(), 0);
        $this->assertEquals($ret, MySqlMockError::NO_ERROR);
    }    

    /*
     * @cover mysql_error
     * @brief 오류가 없는 최초 상태에서 오류코드값을 테스트합니다.
     */
    public function test_mysqlNoError()
    {
    	$answer_host = "1.2.3.4:5678";
    	$answer_userid = "rabbit3";
    	$answer_password = getEncPassword();
    	// add Test Set
    	mysql_addMock($answer_host, $answer_userid, $answer_password);

    	// Invalid Connection
    	$conn = mysql_connect($answer_host, $answer_userid, "wrong");
        $this->assertEquals(mysql_errno($conn), MySqlMockError::CONNECT_FAIL);
        $this->assertEquals(mysql_error($conn), "ERROR : DB Connection Fail!");

    	// Valid Connection
    	$conn = mysql_connect($answer_host, $answer_userid, $answer_password);
        $this->assertEquals(mysql_errno($conn), MySqlMockError::NO_ERROR);
        $this->assertEquals(mysql_error($conn), "");

    	mysql_close($conn);
    }

}

