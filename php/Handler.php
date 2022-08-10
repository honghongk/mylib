<?php

/**
 * 핸들러 모음
 */
class Handler
{
    /**
     * try catch
     */
    static function catch ( $th )
    {
        $msg = '파일: ' . $th->getFile() . ' 라인: ' . $th->getLine() . "\n" ;
        $msg .= $th->getMessage() . "\n" ;

        $d = str_repeat('-',80)."\n";

        $msg .= $d;
        // 데이터키 => prefix
        $f = [
            'file' => '파일:',
            'line' => ' 라인:',
            'class' => "\n\t",
            'type' => '',
            'function' => '',
            // 'args' => "\t",
        ];
        foreach ( $th->getTrace() as $v )
        {
            foreach ($f as $k => $p)
            {
                $a = $v[$k] ?? '' ;
                if ( is_array ( $a ) )
                    $a = "\n".$p.implode("\n".$p,explode("\n",json_encode($a,JSON_PRETTY_PRINT)));
                $msg .= $p . $a.' ';
            }
            $msg .= "\n";
        }
        $msg .= $d;
        Logger::error($msg);

        return $msg;
        

    }

    /**
     * set_error_handler
     * deprecated $errcontext
     */
    static function error($no, $str, $file = null, $line = null)
    {

        $d = str_repeat('-',80)."\n";
        $msg = '';
        $msg .= '파일: '. $file . ' 라인:' . $line."\n";
        $msg .= $str . "\n";
    
        $msg .= $d;
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $is = [
            'file' => '파일: ',
            'line' => "\t".' 라인: ',
            'class' => "\t",
            'type' => "\t",
            'function' => "\t",
        ];
        foreach ($trace as $v)
        {
            foreach ($is as $k => $m )
            {
                if ( isset($v[$k]) )
                    $msg .= $m.$v[$k];
            }
            $msg .= "\n";
            // $f = '파일:'.$v['file'].' 라인:'.$v['line'].'  '.$v['function']. implode(' ',$v['args'])."\n";
        }
        Logger::error($msg);
        echo '<hr>';
        echo '<pre>';
        echo $msg;
        echo '</pre>';
        echo '<hr>';
        exit;
    }
}
