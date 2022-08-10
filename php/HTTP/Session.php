<?php



namespace HTTP;

use ArrayAccess;


/**
 * https://www.php.net/manual/en/class.arrayaccess.php
 * implements ArrayAccess
 * 배열접근 가능하게하는 메서드 4개
 * 7,8 버전 인터페이스 다름
 */
class Session implements ArrayAccess
{
	function __construct ()
	{
		$this->start();
		$this->close();
	}

	
	public function offsetExists($offset)
	{
		return self::get($offset) !== false ;
	}
	
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value) ;
	}

	public function offsetUnset($offset)
	{
		if ( session_status() != PHP_SESSION_ACTIVE )
			$this->start();
		unset($_SESSION[$offset]);
		$this->close();
	}

	protected function close()
	{
		session_write_close();
	}

	protected function start()
	{
		/**
		 * PHP_SESSION_DISABLED	: 0
		 * PHP_SESSION_NONE		: 1
		 * PHP_SESSION_ACTIVE	: 2
		 */
		if ( session_status() != PHP_SESSION_ACTIVE && ! headers_sent() )
			session_start();
		$this->regen();
	}


	/**
	 * 보안 / 세션 하이재킹 막을 수 있다고 함
	 */
	protected function regen()
	{
		if ( session_status() != PHP_SESSION_ACTIVE )
			$this->start();
		session_regenerate_id();
	}


	/**
	 * 세션 값 얻기
	 * @param string ... keys 키 > 하위키 ...
	 * @return mixed 해당하는 키의 값
	 */
	static public function get ()
	{
		// 파라미터 없으면 전체 리턴
		$args = func_get_args();
		if ( empty ( $args ) )
			return $_SESSION;
		
		// 맞는 키 찾아서 리턴
		$res = NULL;
		foreach ( $args as $v )
			$res = $res[$v] ?? $_SESSION[$v] ?? false ;
		return $res;
	}


	/**
	 * 세션 키에 값을 할당한다
	 * @param mixed 키
	 * @param mixed 값
	 */
	public function set($k, $v)
	{
		if ( session_status() != PHP_SESSION_ACTIVE )
			$this->start();
		$_SESSION[$k] = $v ?? false;
		$this->close();
	}


	/**
	 * 세션 키에 값을 추가한다
	 * @param string 키
	 * @param mixed 값
	 */
	public function push($k, $v)
	{
		if ( session_status() != PHP_SESSION_ACTIVE )
			$this->start();
		if ( is_null ( $k ) )
			$_SESSION[] = $v;
		else
		{
			$_SESSION[$k] = $_SESSION[$k] ?? [] ;
			$_SESSION[$k][] = $v ?? false;
		}

		$this->close();
	}


	public function destroy()
	{
		$this->start();
		return session_destroy();
	}
}