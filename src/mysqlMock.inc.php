<?php
/**
 * Project Name : phpMySqlMock v0.1
 */

/************************************************
 * MySql Mock Functions Type-A
 ************************************************/
function _has_phpMySqlMock_class() {
	return function_exists("mysql_addMockData") || __NAMESPACE__=="phpMySqlMock";
}

function mysqlm_connect($host, $user, $pwd) {
	if(_has_phpMySqlMock_class()) {
		phpMySqlMock\mysql_connect($host, $user, $pwd);
	}
	return mysql_connect($host, $user, $pwd);
}

function mysqlm_query($query, $conn) {
	if(_has_phpMySqlMock_class()) {
		phpMySqlMock\mysql_query($query, $conn);
	}
	return mysql_query($query, $conn);
}

function mysqlm_fetch_array($ref) {
	if(_has_phpMySqlMock_class()) {
		phpMySqlMock\mysql_fetch_array($ref);
	}
	return mysql_fetch_array($ref);
}

function mysqlm_affected_rows($conn) {
	if(_has_phpMySqlMock_class()) {
		phpMySqlMock\mysql_affected_rows($conn);
	}
	return mysql_affected_rows($conn);
}

function mysqlm_error($conn) {
	if(_has_phpMySqlMock_class()) {
		phpMySqlMock\mysql_error($conn);
	}
	return mysql_error($conn);
}

function mysqlm_errno($conn) {
	if(_has_phpMySqlMock_class()) {
		phpMySqlMock\mysql_errno($conn);
	}
	return mysql_errno($conn);
}

function mysqlm_close($conn) {
	if(_has_phpMySqlMock_class()) {
		phpMySqlMock\mysql_close($conn);
	}
	return mysql_close($conn);
}

/************************************************
 * MySql Mock Functions Type-B
 ************************************************/
if( !function_exists("mysql_connect") ) {
	function mysql_connect($host, $user, $pwd) {
		return phpMySqlMock\mysql_connect($host, $user, $pwd);
	}
}

if( !function_exists("mysql_query") ) {
	function mysql_query($query, $conn) {
		return phpMySqlMock\mysql_query($query, $conn);
	}
}

if( !function_exists("mysql_fetch_array")) {
	function mysql_fetch_array($conn) {
		return phpMySqlMock\mysql_fetch_array($conn);
	}
}

if( !function_exists("mysql_affected_rows")) {
	function mysql_affected_rows($conn) {
		return phpMySqlMock\mysql_affected_rows($conn);
	}
}

if( !function_exists("mysql_error")) {
	function mysql_error($conn) {
		return phpMySqlMock\mysql_error($conn);
	}
}

if( !function_exists("mysql_errno")) {
	function mysql_errno($conn) {
		return phpMySqlMock\mysql_errno($conn);
	}
}

if( !function_exists("mysql_close")) {
	function mysql_close($conn) {
		return phpMySqlMock\mysql_close($conn);
	}
}
