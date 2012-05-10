<?php
/**
 * Tuan2345Client v1.0.0.1
 * @version 1.0.0.1  
 * @modifytime 2011-03-11  
 * @author xiongxiaoming
 * @contact xiongxm.9991.com@gmail.com
 * @example 
 *      // GET request token
 *      $client = new Tuan2345Client();
 *      $res = $client->send(团购2345用户ID,订单ID,订单时间,商品ID,商品原价格,商品团购价,商品折扣,商品图片地址,商品所属网站,商品购买数量,订单总价,商品URL地址,商品标题,商品消费截止时间);
 *     
 */

//include_once("config.php");
class Tuan2345Client
{/*{{{*/
    private $api;
    private $key;
    private $secret;
    public function __construct() 
    { /*{{{*/
        $this->key    = APP_KEY; 
        $this->secret = APP_SECRET;
    } /*}}}*/

    public function send($qid,$order_id,$order_time,$pid,$price,$tprice,$rebate,$proimg,$website,$number,$total_price,$goods_url,$title,$spend_close_time)
    {/*{{{*/
        $params = array();
        $params['qid']       = $qid;
		$params['order_id']  = $order_id;  
        $params['order_time']= $order_time;
		$params['pid']       = $pid;
		$params['price']     = $price;
        $params['tprice']     = $tprice;
        $params['rebate']     = $rebate;
        $params['proimg']     = $proimg;
        $params['website']     = $website;
		$params['number']    = $number;
		$params['total_price']= $total_price;
        $params['goods_url'] = $goods_url;  
        $params['title']      = $title;  
        $params['spend_close_time']  = $spend_close_time;  
        $params['key']       = $this->key;
        $sign = md5($qid."|".$order_id."|".$order_time."|".$pid."|".$price."|".$tprice."|".$rebate."|".$proimg."|".$website."|".$number."|".$total_price."|".$goods_url."|".$title."|".$spend_close_time."|".$this->key."|".$this->secret);
        $params['sign']      = $sign;
        return  $this->post($params,"http://api.tuan.2345.com/api/deal.php");
    }/*}}}*/
    public function get_userinfo($uid){
		$params = array();
        $params['uid']       = $uid;
	    return $this->post($params,"http://api.tuan.2345.com/api/getUserInfo.php");
	}
    private function post($data,$url) 
    {/*{{{*/
        // Get parts of URL
        $url = parse_url($url);
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
