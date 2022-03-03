<?php

class Sendmail
{
  protected $sock ;
  protected $sender ;
  protected $header ;
  protected $contents ;
  protected $body ;
  function __construct ( $sender )
  {
    // 이메일? 도메인? 정규식확인?
    $this->sender = $sender;
    $this->sock = popen ( '/usr/sbin/sendmail -t -f '.$sender, 'w' ) or exit('메일 실패');
  }

  function __destruct()
  {
    $this->close();
  }

  function header ( $sender_name , $to_address , $to_name , $subject )
  {
    $this->header = array(
      'Subject' => $subject ,
      'From' => $sender_name . ' <' . $this->sender . '>' ,
      'Sender' => $sender,
      'To' => $to_name . ' <'.$to_address.'>',
      'Reply-To' => $sender,
      'MIME-Version' => '1.0',
      'Content-Type' => 'Text/HTML; charset=euc-kr',
      'Content-Transfer-Encoding' => 'base64'
    );
    return $this;
  }

  function body ( $contents )
  {
    $this->contents = chunk_split(base64_encode($contents));
    return $this;
  }

  function send()
  {
    // 필수항목체크 추가?

    foreach($this->header as $k => $v)
      fputs($this->sock, $k.': '.$v."\n");
    fputs($this->sock, $this->contents . "\n\n");
    $this->close();
  }

  function close()
  {
    pclose ( $this->sock ) ;
  }
}
