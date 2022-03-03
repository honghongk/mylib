<?php




class Http
{
  protected $sock;
  protected $url;
  protected $header;
  protected static $methods = array('GET','POST','PUT','DELETE','OPTIONS');
  protected $method ;
  protected $version ;
  function __construct ( $url , $port = 80 , $timeout = 3 )
  {
    // host , uri 분리해야함
    $url = parse_url ( $host ) ;

    // 도메인이나 아이피
    $sock = fsockopen ( $host, $port, $errno, $errstr , $timeout ) ;
    if ( ! $sock )
      return false ;
    $this->sock = $sock ;
    $this->method ( 'GET' ) ;
    $this->version ( 'HTTP/1.1' ) ;
  }

  function __destruct()
  {
    $this->close();
  }

  function version ( $version )
  {
    $this->version = $version ;
  }

  function method ( $method )
  {
    $method = strtoupper ( $method ) ;
    if ( ! in_array ( $method , $this->methods ) )
      return FALSE ;
    $this->method = $method ;
    return $this;
  }


  //'Connection: keep-alive',
  //'Pragma: no-cache',
  //'Cache-Control: no-cache',
  //'Upgrade-Insecure-Requests: 1',
  //'Content-Type: text/html; charset=utf-8',
  function header ( $header = array() )
  {
    $this->header = array(
      $this->method . ' '.$this->url['uri'].' '.$this->version,
      'Host:'.$this->url['host'],
      'Date:'.date('D, d M Y H:i:s T'),
    );
    array_push($this->header , $header);
    return $this;
  }

  function send()
  {
    // 뒤에 \r\n\r\n 안붙이면 타임아웃 걸림
    fputs ( $fs , implode ( "\r\n" , $this->header ) . "\r\n\r\n" ) ;
    return $this;
  }

  function response ( $closure )
  {
    $res = '';
    while ( $r = fgets($this->sock) )
    {
      if ( get_class($closure) == 'closure' )
        $closure($r);
      else
        $res .= $r;
    }
    return $res;
  }


  function close()
  {
    fclose($this->sock);
  }
}
