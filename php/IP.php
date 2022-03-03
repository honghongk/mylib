<?php



class IP
{
    /**
     * 서브넷마스크 아이피 범위 계산 원본
     * https://stackoverflow.com/questions/15961557/calculate-ip-range-using-php-and-cidr
     */
    static function range ( $cidr )
    {
        $range = array();
        $cidr = explode('/', $cidr);
        $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
        $range[1] = long2ip((ip2long($range[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
        return $range;
    }

  
}
