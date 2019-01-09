<?php
namespace phpMySqlMock;
/**
 * Project Name : phpMySqlMock v0.1
 */


/************************************************
 * MySql Mock Class
 ************************************************/
class mysqlMockError {
	const NO_ERROR = 0;
	const CONNECT_FAIL = 1;
}

class mysqlMockList {
	public $m_list = array();
	public function __construct() {
	}

	public function getConnectCount() {
		return count($this->m_list);
	}

	public function appendConnect($host, $user, $pwd) {
		# 이미 Connection정보가 있다면 기존 Connection을 전달한다.
		foreach($this->m_list as $obj) {
			if($obj->getHost()==$host && $obj->getUserId()==$user) {
				return $obj;
			}
		}

		# Connection이 없다면 새로 생성한다.
		$newMock = new mysqlMockObject($host, $user, $pwd);
		array_push($this->m_list, $newMock);

		return $newMock;
	}

	public function getConnect($host, $user, $pwd) {
		foreach($this->m_list as $obj) {
			if($obj->getHost()==$host && $obj->getUserId()==$user && $obj->getPassword()==$pwd) {
				return $obj;
			}
		}

		return mysqlMockError::CONNECT_FAIL;
	}

	public function closeConnect($mockObj) {
		if( !is_object($mockObj) ) {
			return mysqlMockError::CONNECT_FAIL;
		} 
		for($i=0; $i<count($this->m_list);$i++) {
			if($this->m_list[$i]->getHost()==$mockObj->getHost() && $this->m_list[$i]->getUserId()==$mockObj->getUserId() && $this->m_list[$i]->getPassword()==$mockObj->getPassword()) {
				array_splice($this->m_list, $i, 1);
				return mysqlMockError::NO_ERROR;
			}
		}
		return mysqlMockError::CONNECT_FAIL;
	}
}


class mysqlMockObject {
	public $m_host;
	public $m_userid;
	public $m_pwd;

	public function __construct($host, $user, $pwd) {
		$this->m_host = $host;
		$this->m_userid = $user;
		$this->m_pwd = $pwd;
	}

	public function getHost() {
		return $this->m_host;
	}

	public function getUserId() {
		return $this->m_userid;
	}

	public function getPassword() {
		return $this->m_pwd;
	}

}

/************************************************
 * MySql Mock Functions
 ************************************************/
$mysqlMyMockList = new mysqlMockList();

function mysql_countConnect() {
	global $mysqlMyMockList;
	return $mysqlMyMockList->getConnectCount();
}

function mysql_addMock($host, $user, $pwd) {
	global $mysqlMyMockList;

	return $mysqlMyMockList->appendConnect($host, $user, $pwd);
}


function mysqli_connect($host, $user, $pwd) {
	global $mysqlMyMockList;

	return $mysqlMyMockList->getConnect($host, $user, $pwd);
}

function mysql_connect($host, $user, $pwd) {
	return mysqli_connect($host, $user, $pwd);
}

function mysqli_close($conn) {
	global $mysqlMyMockList;

	return $mysqlMyMockList->closeConnect($conn);
}

function mysql_close($conn) {
	return mysqli_close($conn);
}
