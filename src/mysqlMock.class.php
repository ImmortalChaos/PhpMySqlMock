<?php
namespace phpMySqlMock;
/**
 * Project Name : phpMySqlMock v0.1
 */

/************************************************
 * MySql Mock Utility Functions
 ************************************************/
function _array_string_push(&$arr, $txt) {
	if($txt=="") {
		return;
	}
	array_push($arr, $txt);
}

function _is_order_by($txt) {
	return $txt=="ASC" || $txt=="DESC";
}

function _is_separate_query($txt) {
	return $txt==' ' || $txt==',' || $txt=='(' || $txt==')';
}

function _is_query_operater($txt) {
	return ($txt=="=" || $txt=="!=" || $txt=="<" || $txt==">" || $txt=="<=" || $txt==">=" || $txt=="like");
}

function _is_separate_and_operator($chr) {
	return _is_separate_query($chr) || $chr=='=' || $chr=='<' || $chr=='>' || $chr=='!';
}

function _is_query_section($txt) {
	return $txt=="SELECT" || $txt=="FROM" || $txt=="INSERT" || $txt=="WHERE" || $txt=="LIMIT" || $txt=="ORDER" || $txt=="VALUES" || $txt=="INTO";
}

function _is_quote_string($txt) {
	return $txt=='"' || $txt=='\'';
}

/**
 * @brief 배열에 값을 반환합니다. 이때 해당값이 존재하지 않는다면 디폴트값을 반환합니다.
 */
function _getArrayVal($arr, $name, $defRet = "") {
	if(isset($arr[$name]) && $arr[$name]!="") {
		return $arr[$name];
	}

	return $defRet;
}

function explodeQuery($query) {
	$retArr = array();
	$len = strlen($query);
	$word = "";
	$i = 0;
	do {
		if(_is_separate_and_operator($query{$i})) {
			if(!_is_separate_query($query{$i})) {
				$word.=$query{$i};
			}
			_array_string_push($retArr, $word);
			$word = "";
			$i++;
			continue;
		} else if( _is_quote_string($query{$i}) ) {
			$endpos = strpos($query, $query{$i}, $i+1);
			if($endpos!==false) {
				$word.=substr($query, $i, $endpos - $i + 1);
			}
			$i+= strlen($word)-1;
		} else if(($i+1)!=$len) {
			$word .= $query{$i};
		}
		if(($i+1)==$len) {
			array_push($retArr, $word.$query{$i});
		}			
		$i++;
	} while($i<$len);

	return $retArr;
}

/************************************************
 * MySql Mock Class
 ************************************************/
class MySqlMockError {
	const NO_ERROR = 0;
	const CONNECT_FAIL = 1;
	const INVALID_QUERY = 2;
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

	const QUERY_LIMIT = "LIMIT";
	const QUERY_SELECT = "SELECT";
	const QUERY_INSERT = "INSERT";
	const QUERY_INTO = "INTO";
	const QUERY_VALUES = "VALUES";
	const QUERY_FROM = "FROM";
	const QUERY_WHERE = "WHERE";
	const QUERY_ORDER = "ORDER";
}

class MySqlMockParseQuery {
	public $m_query = array();
	public function __construct($query) {
		$this->parseQuery($query);
	}	

	public function getTableName() {
		if(isset($this->m_query[MySqlMockAttribute::QUERY_FROM])) {
			return $this->m_query[MySqlMockAttribute::QUERY_FROM][0];
		} else if(isset($this->m_query[MySqlMockAttribute::QUERY_INTO])) {
			return $this->m_query[MySqlMockAttribute::QUERY_INTO][0];
		}

		return "";
	}

	private function getSectionValues($section) {
		if(isset($this->m_query[$section])) {
			return $this->m_query[$section];
		}

		return array();
	}

	public function getSelectFunction() {
		$arr = $this->getSectionValues(MySqlMockAttribute::QUERY_SELECT);
		if(count($arr)!=1) {
			return "";
		} 

		return $arr[0];
	}

	public function getSelect() {
		return $this->getSectionValues(MySqlMockAttribute::QUERY_SELECT);
	}

	public function getInsert() {
		return $this->getSectionValues(MySqlMockAttribute::QUERY_INSERT);
	}

	public function getValues() {
		return $this->getSectionValues(MySqlMockAttribute::QUERY_VALUES);
	}

	public function getWhere() {
		return $this->getSectionValues(MySqlMockAttribute::QUERY_WHERE);
	}

	public function getLimit() {
		return $this->getSectionValues(MySqlMockAttribute::QUERY_LIMIT);
	}

	public function getOrder() {
		return $this->getSectionValues(MySqlMockAttribute::QUERY_ORDER);
	}

	private function parseQueryOrderBy($section, $qArr, $i) {
		if($section!=MySqlMockAttribute::QUERY_ORDER || $qArr[$i]!="BY" || !isset($qArr[$i+1])) {
			return 0;
		}

		$field = $qArr[$i+1];
		if( isset($qArr[$i+2]) && _is_order_by($qArr[$i+2]) ) {
			$this->m_query[$section] = array($field, $qArr[$i+2]);
			return 2;
		}

		$this->m_query[$section] = array($field, "ASC");
		return 1;
	}

	private function parseOperater($section, $qArr, $i) {
		if($section!=MySqlMockAttribute::QUERY_WHERE) {
			return 0;
		}

		$operater = isset($qArr[$i+1])?$qArr[$i+1]:"";
		$value = isset($qArr[$i+2])?$qArr[$i+2]:"";
		if(_is_query_operater($operater)) {
			array_push($this->m_query[$section], array($qArr[$i], $operater, $value));
			return 2;
		}
		return 0;
	}

	private function parseQuery($query) {
		$ret = explodeQuery($query);
		$section = "";
		$i = 0;

		do {
			if(_is_query_section(strtoupper($ret[$i]))) {
				$section = strtoupper($ret[$i]);
				$this->m_query[$section] = array();
			} else {
				$offset = 0;
				$offset+=$this->parseQueryOrderBy($section, $ret, $i);
				$offset+=$this->parseOperater($section, $ret, $i);
				if($offset==0) {
					if($section=="INTO" && count($this->m_query[$section])>0) {
						$section = MySqlMockAttribute::QUERY_INSERT;
					}
					array_push($this->m_query[$section], $ret[$i]);
				}
				$i+=$offset;
			}
			$i++;
		} while($i<count($ret));
	}

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

class MySqlMockFetchObject {
	public $m_offset = 0;
	public $m_queryData = array();

	public function __construct() {
	}

	public function appendData($arrData) {
		array_push($this->m_queryData, $arrData);
	}

	public function getCount() {
		return count($this->m_queryData);
	}

	public function fetchArray() {
		if(isset($this->m_queryData[$this->m_offset])) {
			return $this->m_queryData[$this->m_offset++];
		}

		return array();
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
	public $m_queryDataObj = NULL;
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

	private function isError() {
		return $this->m_errcode!=MySqlMockError::NO_ERROR;
	}

	private function isValidParameter($condition) {
		if(!isset($condition[2])) {
			$this->setError(MySqlMockError::INVALID_QUERY);
			return false;
		}

		return true;
	}

	private function checkConditionLessThan($data, $condition) {
		if(!$this->isValidParameter($condition)) {
			return false;
		}
		return $data[$condition[0]] < $condition[2];
	}

	private function checkConditionGreaterThan($data, $condition) {
		if(!$this->isValidParameter($condition)) {
			return false;
		}
		return $data[$condition[0]] > $condition[2];
	}

	private function checkConditionLessThanEqual($data, $condition) {
		if(!$this->isValidParameter($condition)) {
			return false;
		}
		return $data[$condition[0]] <= $condition[2];
	}

	private function checkConditionGreaterThanEqual($data, $condition) {
		if(!$this->isValidParameter($condition)) {
			return false;
		}
		return $data[$condition[0]] >= $condition[2];
	}

	private function checkConditionEqual($data, $condition) {
		if(!$this->isValidParameter($condition)) {
			return false;
		}
		$val = is_string($condition[2])?trim($condition[2],"'\""):$condition[2];
		return $data[$condition[0]] == $val;
	}

	private function checkConditionNotEqual($data, $condition) {
		if(!$this->isValidParameter($condition)) {
			return false;
		}
		$val = is_string($condition[2])?trim($condition[2]):$condition[2];
		return $data[$condition[0]] != $val;
	}

	private function checkConditionLike($data, $condition) {
		if(!$this->isValidParameter($condition)) {
			return false;
		}
		$vals = is_string($condition[2])?trim($condition[2],"'\""):$condition[2];
		$lp = substr($vals, 0, 1)=="%";
		$rp = substr($vals, -1)=="%";
		$val = trim($vals,"%");

		$bFound = false;
		if($lp && $rp) {
			$bFound = strpos($data[$condition[0]], $val)!==false;
		} else if($lp) {
			$bFound = (($temp = strlen($data[$condition[0]]) - strlen($val)) >= 0 && strpos($data[$condition[0]], $val, $temp) !== false);
		} else if($rp) {
			$bFound = strrpos($data[$condition[0]], $val, -strlen($data[$condition[0]])) !== false;
		} else {
			$bFound = $data[$condition[0]]==$val;
		}

		return $bFound;
	}

	private function checkCondition($data, $condition) {
		$ret = true;
		if(count($condition)==1) {
			return false;
		}

		switch($condition[1]) {
			case "<" : $ret = $this->checkConditionLessThan($data, $condition); break;
			case "<=" : $ret = $this->checkConditionLessThanEqual($data, $condition); break;
			case ">=" : $ret = $this->checkConditionGreaterThanEqual($data, $condition); break;
			case ">" : $ret = $this->checkConditionGreaterThan($data, $condition); break;
			case "=" : $ret = $this->checkConditionEqual($data, $condition); break;
			case "!=" : $ret = $this->checkConditionNotEqual($data, $condition); break;
			case "like" : $ret = $this->checkConditionLike($data, $condition); break;
			default :
				break;
		}

		return $ret;
	}

	private function checkConditions($data, $arrWhere) {
		$index = 0;
		$result = true;
		$isAnd = true;	// And(true), Or(false)
		while($index < count($arrWhere)) {
			$cond = $arrWhere[$index++];
			if(is_string($cond)) {
				if(strtoupper($cond)!="AND" && strtoupper($cond)!="OR") {
					$this->setError(MySqlMockError::INVALID_QUERY);
					return false;
				}
				$isAnd = strtoupper($cond)=="AND";
				continue;
			}

			$bool = $this->checkCondition($data, $cond);
			if($isAnd) {
				$result = $result && $bool;
			} else {
				$result = $result || $bool;
			}
		}
		return $result;
	}

	public function getLastQueryResult() {
		return $this->m_queryDataObj;
	}

	/**
	 * @return count of data  
	 */
	private function doRunSelectQuery($qryObj) {
		$this->m_queryDataObj = new MySqlMockFetchObject();
		$tableName = $qryObj->getTableName();

		foreach($this->m_tableData[$tableName] as $data) {
			$result = array();
			// process where
			if(!$this->checkConditions($data, $qryObj->getWhere())) {
				continue;
			}

			if($this->isError()) {
				return $this->getErrNo();
			}

			$func = $qryObj->getSelectFunction();
			$selectKeys = $qryObj->getSelect();
			if($func=="*") {
				$selectKeys = array_keys($this->m_tables[$tableName]);
			}

			// get select list
			foreach($selectKeys as $key) {
				$result[$key] = _getArrayVal($data,$key,NULL);
			}
			$this->m_queryDataObj->appendData($result);
		}

		return $this->m_queryDataObj;
	}

	private function doRunQueryCommand($qryObj) {
		$tableName = $qryObj->getTableName();

		// check has table
		if(!isset($this->m_tableData[$tableName])) {
			return NULL;
		}

		if(count($qryObj->getSelect())) {
			return $this->doRunSelectQuery($qryObj);
		}
		return NULL;
	}

	public function query($qry) {
		$qryObj = new MySqlMockParseQuery($qry);
		return $this->doRunQueryCommand($qryObj);
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

function mysql_query($query, $conn) {
	return $conn->query($query);
}

function mysql_fetch_array($dataObj) {
	return $dataObj->fetchArray();
}

function mysql_affected_rows($conn) {
	$dataObj = $conn->getLastQueryResult();
	return $dataObj->getCount();
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
