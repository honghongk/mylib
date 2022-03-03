<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    protected static $mail;
    function __construct()
    {
        // 네이버로 세팅할때
        // https://tonhnegod.tistory.com/26
        

        //Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);

        // 서버세팅
        $mail->CharSet = "utf-8";   //한글이 안깨지게 CharSet 설정
        $mail->Encoding = "base64";
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'porgreany@gmail.com';                     //SMTP username
        $mail->Password   = 'pwd';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 465;
        $mail->SMTPSecure = "ssl"; // SSL을 사용함

        self::$mail = $mail; 
    }

    // 무슨파일인지 모름
    function locale ( $lang, $file )
    {
        self::$mail->setLanguage ( $lang, $file);
        return $this;
        //To load the French version
        //$mail->setLanguage ( 'fr', '/optional/path/to/language/directory/');
    }

    // 첨부파일
    function attach ( ... $files )
    {
        foreach ( $files as $v )
        {
            if ( ! is_file ( $v ) )
                throw new Exception('첨부파일 없음');
            self::$mail->addAttachment($v);
        }
        return $this;
    }

    function sender ( $addr , $name )
    {
        self::$mail->setFrom ( $addr, $name ) ;
        return $this;
    }

    // 답장
    function reply ( $addr , $name )
    {
        self::$mail->addReplyTo($addr, $name);
        return $this;
    }

    // 받는사람
    function receiver ( array $receiver )
    {
        foreach ( $receiver as $addr => $name)
            if ( empty ( $name ) )
                self::$mail->addAddress($addr);
            else
                self::$mail->addAddress($addr,$name);
        return $this;
    }

    function cc ( ... $cc )
    {
        foreach ( $cc as $v )
            self::$mail->addCC ( $v ) ;
        return $this;
    }

    function bcc ( ... $bcc )
    {
        foreach ($bcc as $v)
            self::$mail->addBCC($v);
        return $this;
    }


    function content ( $subject , $body , $altbody = '' )
    {
        //Set email format to HTML
        self::$mail->isHTML(true);
        foreach ( array( 'subject' , 'body' , 'altbody' ) as $v )
        {
            if ( empty ( $$v ) )
                continue ;
            self::$mail->(ucfirst($v)) = $$v ;
        }
        return $this;
    }
    function send()
    {
        return self::$mail->send();
    }


}