<?php

namespace DB\Mysql;


/**
 * 백업받기
 */
class Dump extends Mysql
{
    /**
     * 테이블 얻기
     */
    static function table ( $name = '' )
    {
        $name = self::escape($name);
        $sql = 'SHOW FULL TABLES';
        if ( ! empty ( $name ) )
            $sql .= ' LIKE \''.$name.'\'';

        $res = [];
        $data = self::query($sql);
        foreach ($data as $r)
        {
            $name = array_shift($r);
            if ( $r['Table_type'] == 'BASE TABLE' )
            {
                $r = self::queryOne('SHOW CREATE TABLE `'.$name.'`');
                $t = preg_replace(
                    '/AUTO_INCREMENT\s{0,}=\s{0,}[0-9]{0,}/','',
                    htmlspecialchars_decode($r['Create Table'])
                );
                $res[] = $t;
            }
            else
            {
                $r = self::queryOne('SHOW CREATE VIEW '. $name);
                $t = preg_replace(
                    '/DEFINER\s{0,}=\s{0,}`?[^`]{0,}`?@`?[^`]{0,}`?/','',
                    htmlspecialchars_decode($r['Create View'])
                );
                $res[] = $t;
            }
        }
        return implode("\n\n",$res);
    }


    static function event ( $name = '' )
    {
        $name = self::escape($name);
        $sql = 'SHOW EVENTS';
        if ( ! empty ( $name ) )
            $sql .= ' LIKE \''.$name.'\'';
        $data = self::query($sql);
        $res = [];
        foreach ($data as $r)
        {
            $r = self::queryOne('SHOW CREATE EVENT '.$r['Name']);
            $t = 'DELIMITER ;;'."\n";
            $t .= preg_replace(
                '/DEFINER\s{0,}=\s{0,}`?[^`]{0,}`?@`?[^`]{0,}`?/','',
                htmlspecialchars_decode($r['Create Event'])
            );
            $t .= ' ;; ' . "\n".'DELIMITER ;';
            $res[] = $t;
        }

        return implode("\n\n",$res);
    }


    static function procedure ( $name = '' )
    {
        $name = self::escape($name);
        $sql = 'SHOW PROCEDURE STATUS WHERE DB = (SELECT DATABASE())';
        if ( ! empty ( $name ) )
            $sql .= ' AND name = \''.$name.'\'';
        $data = self::query($sql);
        $res = [];
        foreach ($data as $r)
        {
            $t = 'DELIMITER ;;'."\n";
            $r = self::queryOne('SHOW CREATE PROCEDURE '.$r['Name']);
            $t .= preg_replace(
                '/DEFINER\s{0,}=\s{0,}`?[^`]{0,}`?@`?[^`]{0,}`?/','',
                htmlspecialchars_decode($r['Create Procedure'])
            );
            $t .= ' ;; ' . "\n".'DELIMITER ;';
            $res[] = $t;
        }
        return implode("\n\n",$res);
    }
    
    
    static function trigger ( $name = '' )
    {
        $name = self::escape($name);
        $sql = 'SHOW TRIGGERS';
        if ( ! empty ( $name ) )
            $sql .= ' LIKE \''.$name.'\'';
        $list = self::query($sql);

        $res = [];
        foreach ($list as $r)
        {
            $t = 'DELIMITER ;;'."\n";
            $t .= 'CREATE TRIGGER ' . $r['Trigger'] . ' ' . $r['Timing'] . ' ' . $r['Event'] . "\n"
                . ' ON ' . $r['Table'] . "\n"
                . ' FOR EACH ROW ' . htmlspecialchars_decode($r['Statement']);
            $t .= ' ;; '."\n";
            $t .= 'DELIMITER ;';
            $res[] = $t;
        }
        return implode("\n\n",$res);
    }


    static function all ( $file )
    {
        $f = fopen($file,'a');
        // table view 먼저
        foreach (['table','event','procedure','trigger'] as $v)
        {
            $dump = self::$v();
            if ( ! empty ($dump ) )
                fwrite($f,$dump);
        }
        fclose($f);
    }

}


