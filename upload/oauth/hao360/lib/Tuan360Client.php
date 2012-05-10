<?php
/**
 * Qihoo Tuan360Client v1.0.0.1
 * @version 1.0.0.1  
 * @modifytime 2010-12-03  
 * @author tuan.360.cn
 * @contact yangtao@360.cn
 * @example 
 *      // GET request token
 *      $client = new Tuan360Client();
 *      $res = $client->send(12,"myname","http://site.com/goods.php?id=1","商品介绍","12.90",2,20101203111530);
 */

//include_once("config.php");
class Tuan360Client
{/*{{{*/
    private $api = "http://tuan.360.cn/api/deal.php" ;
    private $key;
    private $secret;
    public function __construct() 
    { /*{{{*/
        $this->key    = APP_KEY; 
        $this->secret = APP_SECRET;
    } /*}}}*/

    public function send($qid,$order_id,$order_time,$pid,$price,$number,$goods_url,$title,$desc,$spend_close_time = '',$merchant_addr = '')
    {/*{{{*/
        $params = array();
        $params['qid']       = $qid;
        $params['order_id']  = $order_id;
		$params['order_time']= $order_time;
        $params['pid']       = $pid;
        $params['price']     = $price;
        $params['number']    = $number;
		
        $params['total_price'] = $price * $number;
        $params['goods_url'] = $goods_url;  
        //$params['sale_price']= $sale_price;
		$params['title']      = $title; 
        $params['desc']      = $desc;  
        $params['spend_close_time'] = $spend_close_time;  
        $params['merchant_addr']    = $merchant_addr;  
        $params['key']       = $this->key;
        $params['secret']    = $this->secret;
        $sign = md5($this->key . "|" . $qid."|".$order_id."|".$order_time."|".$pid."|".$price."|".
					$number."|".$params['total_price']. "|" . $goods_url."|".$title."|".$desc."|".$spend_close_time.
					"|". $merchant_addr . "|" . $this->secret);
        $params['sign']      = $sign;
        return  $this->post($params);
    }/*}}}*/

    private function post($data) 
    {/*{{{*/
        // Get parts of URL
        $url = parse_url($this->api);
        if (!$url) { return "couldn't parse url"; }

        // Provide defaults for port and query string
        if (!isset($url['port']))  { $url['port'] = ""; }
        if (!isset($url['query'])) { $url['query'] = ""; }

        // Build POST string
        $encoded = "";
        foreach ($data as $k => $v) {
            $encoded .= ($encoded ? "&" : "");
            $encoded .= rawurlencode($k) . "=" . rawurlencode($v);
        }

        // Open socket on host
        $fp = @fsockopen($url['host'], $url['port'] ? $url['port'] : 80);
        if (!$fp) { return "failed to open socket to {$url['host']}"; }

        // Send HTTP 1.0 POST request to host
        fputs($fp, sprintf("POST %s%s%s HTTP/1.0\n", $url['path'], $url['query'] ? "?" : "", $url['query']));
        fputs($fp, "Host: {$url['host']}\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
        fputs($fp, "Content-length: " . strlen($encoded) . "\n");
        fputs($fp, "Connection: close\n\n");
        fputs($fp, "$encoded\n");

        // Read the first line of data, only accept if 200 OK is sent
        $line = fgets($fp, 1024);
        if (!preg_match('#^HTTP/1\\.. 200#', $line)) { return; }

        // Put everything, except the headers to $results 
        $results = ""; 
        $inheader = TRUE;
        while(!feof($fp)) {
            $line = fgets($fp, 1024);
            if ($inheader && ($line == "\n" || $line == "\r\n")) {
                $inheader = FALSE;
            }
            elseif (!$inheader) {
                $results .= $line;
            }
        }
        fclose($fp);

        // Return with data received
        return $results;
    }/*}}}*/
}/*}}}*/
?>