<?php
namespace phpMySqlMock;
/**
 * Project Name : phpMySqlMock v0.1
 */


/************************************************
 * MySql Mock Class
 ************************************************/
class MySqlMockError {
	const NO_ERROR = 0;
	const CONNECT_FAIL = 1;
	const ERROR = 999;
}

class MySqlMockAttribute {
	const FIELD_INTEGER = 1;
	const FIELD_VARCHAR = 4;

	const ATTR_AUTOINC = "auto_increase";
	const ATTR_ISNULL = "is_null";
	const ATTR_ISUNSIGNED = "is_unsigned";
	const ATTR_FIELDTYPE = "type";
	const ATTR_FIELDSIZE = "size";
}

class MySqlMockList {
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
		$newMock = new MySqlMockObject($host, $user, $pwd);
		array_push($this->m_list, $newMock);

		return $newMock;
	}

	public function getConnect($host, $user, $pwd) {
		foreach($this->m_list as $obj) {
			if($obj->getHost()==$host && $obj->getUserId()==$user && $obj->getPassword()==$pwd) {
				return $obj;
			}
		}

		return MySqlMockError::CONNECT_FAIL;
	}

	public function closeConnect($mockObj) {
		if( !is_object($mockObj) ) {
			return MySqlMockError::CONNECT_FAIL;
		} 
		for($i=0; $i<count($this->m_list);$i++) {
			if($this->m_list[$i]->getHost()==$mockObj->getHost() && $this->m_list[$i]->getUserId()==$mockObj->getUserId() && $this->m_list[$i]->getPassword()==$mockObj->getPassword()) {
				array_splice($this->m_list, $i, 1);
				return MySqlMockError::NO_ERROR;
			}
		}
		return MySqlMockError::CONNECT_FAIL;
	}

}


class MySqlMockObject {
	public $m_host;
	public $m_userid;
	public $m_pwd;
	public $m_autoincrease = 0;
	public $m_errcode = MySqlMockError::NO_ERROR;
	public $m_tables = array();
	public $m_tableData = array();
	public function __construct($host, $user, $pwd) {
		$this->m_host = $host;
		$this->m_userid = $user;
		$this->m_pwd = $pwd;
	}

	public function createTable($tableName, $tableStruct) {
		if($tableName=="" || !is_array($tableStruct) || count($tableStruct)==0) {
			return $this->setError(MySqlMockError::ERROR);
		}
		$this->m_tables[$tableName] = $tableStruct;
		$this->m_tableData[$tableName] = array();

		return $this->setError(MySqlMockError::NO_ERROR);
	}

	public function appendData($tableName, $tableData) {
		array_push($this->m_tableData[$tableName], $tableData);
	}

	public function getTable($tableName) {
		return $this->m_tables[$tableName]; 
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

	public function auto_increase() {
		$this->m_autoincrease++;
		return $this->m_autoincrease;
	}

	public function getErrStr() {
		if($this->m_errcode==MySqlMockError::NO_ERROR) {
			return "";
		}

		return "Error";
	}

	public function getErrNo() {
		return $this->m_errcode;
	}

	public function setError($errno) {
		$this->m_errcode = $errno;
		return $this->m_errcode;
	}
}

/************************************************
 * MySql Mock Functions
 ************************************************/
$mysqlMyMockList = new MySqlMockList();

function mysql_countConnect() {
	global $mysqlMyMockList;
	return $mysqlMyMockList->getConnectCount();
}

function mysql_addMock($host, $user, $pwd) {
	global $mysqlMyMockList;

	return $mysqlMyMockList->appendConnect($host, $user, $pwd);
}

function mysql_addMockData($conn, $tableName, $rowData) {
	if(!is_object($conn)) {
		return MySqlMockError::ERROR;
	}

	$tbls = $conn->getTable($tableName);

	foreach(array_keys($tbls) as $keyName) {
		$bAutoInc = isset($tbls[$keyName][MySqlMockAttribute::ATTR_AUTOINC]) && $tbls[$keyName][MySqlMockAttribute::ATTR_AUTOINC];
		if(isset($rowData[$keyName])) {
			if(!mysql_check_field($tbls[$keyName], $rowData[$keyName])) {
				return MySqlMockError::ERROR; 
			}
		} else if($bAutoInc) {
			$rowData[$keyName] = $conn->auto_increase();
		}
	}

	$conn->appendData($tableName, $rowData);

	return MySqlMockError::NO_ERROR;
}

function mysql_addMockTable($conn, $tableName, $tableStruct) {
	if(!is_object($conn)) {
		return MySqlMockError::ERROR;
	}
	return $conn->createTable($tableName, $tableStruct);
}

function mysql_check_field_integer($fieldAttr, $data) {
	if(isset($fieldAttr[MySqlMockAttribute::ATTR_ISUNSIGNED]) && $fieldAttr[MySqlMockAttribute::ATTR_ISUNSIGNED] && $data<0) {
		return false;
	}
	return true;
}

function mysql_check_field_varchar($fieldAttr, $data) {
	if(!isset($fieldAttr[MySqlMockAttribute::ATTR_FIELDSIZE])) {
		return false;
	}

	if($fieldAttr[MySqlMockAttribute::ATTR_FIELDSIZE]<strlen($data)) {
		return false;
	}
	return true;
}

function mysql_check_field($fieldAttr, $data) {
	switch($fieldAttr[MySqlMockAttribute::ATTR_FIELDTYPE]) {
		case MySqlMockAttribute::FIELD_INTEGER:
			return mysql_check_field_integer($fieldAttr, $data);
		case MySqlMockAttribute::FIELD_VARCHAR:
			return mysql_check_field_varchar($fieldAttr, $data);
		default:
			return false;
	}
}

/**
 * Auto Increaase를 사용하는 Array타입의 Field Struct를 반환
 */ 
function mysql_getFSAutoIncrease() {
	return array(MySqlMockAttribute::ATTR_FIELDTYPE=>MySqlMockAttribute::FIELD_INTEGER, MySqlMockAttribute::ATTR_AUTOINC=>true);
}

function mysql_getFSVarchar($fieldSize, $isNull = true) {
	return array(MySqlMockAttribute::ATTR_FIELDTYPE=>MySqlMockAttribute::FIELD_VARCHAR, MySqlMockAttribute::ATTR_FIELDSIZE=>$fieldSize,
				MySqlMockAttribute::ATTR_ISNULL=>$isNull);

}

function mysql_getFSInteger($isNull = true, $isUnsigned = false) {
	return array(MySqlMockAttribute::ATTR_FIELDTYPE=>MySqlMockAttribute::FIELD_INTEGER, 
		         MySqlMockAttribute::ATTR_ISNULL=>$isNull, MySqlMockAttribute::ATTR_ISUNSIGNED=>$isUnsigned);
}

function mysqli_connect($host, $user, $pwd) {
	global $mysqlMyMockList;

	return $mysqlMyMockList->getConnect($host, $user, $pwd);
}

function mysql_connect($host, $user, $pwd) {
	return mysqli_connect($host, $user, $pwd);
}

function mysql_error($conn) {
	if(is_integer($conn) && $conn==MySqlMockError::CONNECT_FAIL) {
		return "ERROR : DB Connection Fail!";
	}
	return $conn->getErrStr();
}

function mysql_errno($conn) {
	if(is_integer($conn)) {
		return $conn;
	}
	return $conn->getErrNo();
}

function mysqli_close($conn) {
	global $mysqlMyMockList;

	return $mysqlMyMockList->closeConnect($conn);
}

function mysql_close($conn) {
	return mysqli_close($conn);
}
