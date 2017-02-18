<?php

index();

function index()
{
        echo "+-----------------------------------------------------------------------+\n";
        echo "+ wiki              从WiKi导入数据\n";
        echo "+ txtnation         从txtnation导入数据,作为wiki的补充\n";
        echo "+-----------------------------------------------------------------------+\n";
        echo "\n即将从WiKi导入数据";
        sleepWait(5);
        wiki();
        echo "\n即将从txtnation导入数据";
        sleepWait(5);
        txtnation();
}

/**
 * sleep second
 */
function sleepWait($i)
{
    for($i=0; $i<5; $i++)
    {
        sleep(1);
        echo '. ';
    }
}

/**
 * 从WiKi抓取数据
 */

function wiki()
{
    $url = "https://en.wikipedia.org/wiki/Mobile_country_code";
    $content = file_get_contents($url);
    if(empty($content))
        exit("$wiki_url".请求失败);
    $arr = explode("<h3>",$content);
    foreach ($arr as $str)
    {
        deal($str);
    }
}

/**
 * 从txtnation导入数据, 作为wiki的补充数据
 */
function txtnation()
{
    $url = "https://clients.txtnation.com/hc/en-us/articles/218719768-MCCMNC-mobile-country-code-and-mobile-network-code-list-";
    $content = file_get_contents($url);
    if(empty($content))
        exit("$wiki_url".请求失败);
    $arr = explode("<tr>",$content);
    foreach ($arr as $str)
    {
        if(!stripos($str, 'align="right" height="20"'))
            continue;
        $str = trim(strip_tags($str));
        $str = explode("\n", $str);
        $mcc = trim($str[2]);
        $msg = ['mccmnc'=>trim($str[0]), 'mcc'=>$mcc, 'mnc'=>trim(substr($str[0],3)), 'geo'=>trim($str[6]), 'brand'=>trim($str[7]), 'operator'=>trim($str[7])];
        done($msg);
    }
}

/**
 * 格式化wiki的数据
 */
function deal($str)
{
    //首先匹配国家
    $reg = '/<span class="mw-headline" id="([a-z ]+)(.*)_-_([a-z]+)/i';
    preg_match($reg,$str,$geomatch);
    if(empty($geomatch))
        return;
    $geo = strtolower($geomatch[3]);
    $arr = explode("<tr>", $str);
    foreach($arr as $k=>$v)
    {
        //首先mcc,mnc,brand,operator
        if(stripos($v, '<td>'))
        {
            $reg = '/<td>(\d{3})<\/td>\n<td>(\d{2,3})<\/td>\n<td>(.*)<\/td>\n<td>(.*)<\/td>/';
            preg_match($reg,$v,$match);
            if(empty($match))
                continue;
            $mcc = $match[1];
            $mnc = $match[2];
            $mccmnc = $mcc.$mnc;
            $brand = htmlspecialchars_decode(strip_tags($match[3]));
            $operator = htmlspecialchars_decode(strip_tags($match[4]));
            $msg = ['mccmnc'=>$mccmnc,'mcc'=>$mcc, 'mnc'=>$mnc, 'geo'=>$geo, 'brand'=>$brand, 'operator'=>$operator];
            done($msg);
        }
    }
}

/**
 * 数据入库
 */
function done($msg)
{
    usleep(100000);
    foreach($msg as $v)
    {
        echo "$v\t";
    }
    echo PHP_EOL;
}
