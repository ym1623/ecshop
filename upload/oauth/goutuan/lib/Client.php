<?php
/**
 * Goutuan Client
 * @version 1.0.0.1  
 * @modifytime 2011-03-09  
 * @author www.goutuan.net
 * @contact biran@movo.tv
 * @example 
 *      $client = new Client();
 *      $res = $client->send(12,"myname","http://site.com/goods.php?id=1","商品介绍","12.90",2,20101203111530);
 */

include_once("config.php");
class Client{
	public $key;
	public $secret;
	public $redirect_uri;
	public $authorization_uri;
	public $access_token_uri;
	public $order_uri;

	public function __construct() {
		$this->key    			= APP_KEY;
		$this->secret 			= APP_SECRET;
		$this->redirect_uri 	= APP_REDIRECT_URI;
		$this->authorization_uri= APP_AUTHORIZATION_URI;
		$this->access_token_uri = APP_ACCESS_TOKEN_URI;
		$this->order_uri 		= APP_ORDER_URI;
	}

	public function send($data){
		$data['key']       = $this->key;
		$data['secret']    = $this->secret;
		$sign = md5($data['user_id']."|".$data['pid']."|".$data['goods_url']."|".$data['order_time']."|".$this->key."|".$this->secret);
		$data['sign']      = $sign;
		return  $this->post($this->order_uri,$data);
	}
	

	public function post($url,$postfield,$proxy=""){
		$encoded = "";
		if(is_array($postfield)){
		foreach ($postfield as $k => $v) {
			$encoded .= ($encoded ? "&" : "");
		    $encoded .= rawurlencode($k) . "=" . rawurlencode($v);
		}
		}
		$proxy=trim($proxy);
		$user_agent ="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
		$ch = curl_init(); // 初始化CURL句柄
		if(!empty($proxy)){
			curl_setopt($ch, CURLOPT_PROXY, $proxy);//设置代理服务器
		}
		curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
		//curl_setopt($ch, CURLOPT_FAILONERROR, 1); // 启用时显示HTTP状态码，默认行为是忽略编号小于等于400的HTTP信息
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//启用时会将服务器服务器返回的“Location:”放在header中递归的返回给服务器
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);// 设为TRUE把curl_exec()结果转化为字串，而不是直接输出
		curl_setopt($ch, CURLOPT_POST, 1);//启用POST提交
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfield); //设置POST提交的字符串
		//curl_setopt($ch, CURLOPT_PORT, 80); //设置端口
		curl_setopt($ch, CURLOPT_TIMEOUT, 25); // 超时时间
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);//HTTP请求User-Agent:头
		//curl_setopt($ch,CURLOPT_HEADER,1);//设为TRUE在输出中包含头信息
		//$fp = fopen("example_homepage.txt", "w");//输出文件
		//curl_setopt($ch, CURLOPT_FILE, $fp);//设置输出文件的位置，值是一个资源类型，默认为STDOUT (浏览器)。
		curl_setopt($ch,CURLOPT_HTTPHEADER,array(
		'Accept-Language: zh-cn',
		'Connection: Keep-Alive',
		'Cache-Control: no-cache'
		));//设置HTTP头信息
		$document = curl_exec($ch); //执行预定义的CURL
		$info=curl_getinfo($ch); //得到返回信息的特性
		//print_r($info);
		if($info[http_code]=="405"){
			echo "bad proxy {$proxy}\n"; //代理出错
			exit;
		}
		//curl_close($ch);
		return $document;
	}
	  /**
	   * Returns the Current URL.
	   *
	   * @return
	   *   The current URL.
	   */
	  public function getCurrentUri() {
	    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'
	      ? 'https://'
	      : 'http://';
	    $current_uri = $protocol . $_SERVER['HTTP_HOST'] . $this->getRequestUri();
	    $parts = parse_url($current_uri);
	    $query = '';
	    if (!empty($parts['query'])) {
	      $params = array();
	      parse_str($parts['query'], $params);
	      $params = array_filter($params);
	      if (!empty($params)) {
	        $query = '?' . http_build_query($params, NULL, '&');
	      }
	    }
	    // Use port if non default.
	    $port = isset($parts['port']) &&
	      (($protocol === 'http://' && $parts['port'] !== 80) || ($protocol === 'https://' && $parts['port'] !== 443))
	      ? ':' . $parts['port'] : '';
	    // Rebuild.
	    return $protocol . $parts['host'] . $port . $parts['path'] . $query;
	  }
	  
	  /**
	   * Since $_SERVER['REQUEST_URI'] is only available on Apache, we
	   * generate an equivalent using other environment variables.
	   */
	  function getRequestUri() {
	    if (isset($_SERVER['REQUEST_URI'])) {
	      $uri = $_SERVER['REQUEST_URI'];
	    }
	    else {
	      if (isset($_SERVER['argv'])) {
	        $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['argv'][0];
	      }
	      elseif (isset($_SERVER['QUERY_STRING'])) {
	        $uri = $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
	      }
	      else {
	        $uri = $_SERVER['SCRIPT_NAME'];
	      }
	    }
	    // Prevent multiple slashes to avoid cross site requests via the Form API.
	    $uri = '/' . ltrim($uri, '/');
	
	    return $uri;
	  }
	  

  /**
   * Get access token from OAuth2.0 token endpoint with authorization code.
   *
   * This function will only be activated if both access token URI, client
   * identifier and client secret are setup correctly.
   *
   * @param $code
   *   Authorization code issued by authorization server's authorization
   *   endpoint.
   *
   * @return
   *   A valid OAuth2.0 JSON decoded access token in associative array, and
   *   NULL if not enough parameters or JSON decode failed.
   */

	  
  public function getAccessTokenFromAuthorizationCode($code,$uid) {
 
    if ( APP_ACCESS_TOKEN_URI && APP_KEY && APP_SECRET) {
	   $postfield = array(
		          'grant_type' => 'authorization-code',
		          'client_id' =>APP_KEY,
		          'client_secret' => APP_SECRET,
		          'code' => $code,
	   			  'uid'  => $uid,
		          'redirect_uri' => APP_CALLBACK_URI,
	        );
		 
	 	return json_decode($this->post(APP_ACCESS_TOKEN_URI,$postfield),true);
	}
    return NULL;
  }
  
  /**
   * Make an API call.
   *
   * Support both OAuth2.0 or normal GET/POST API call, with relative
   * or absolute URI.
   *
   * If no valid OAuth2.0 access token found in session object, this function
   * will automatically switch as normal remote API call without "oauth_token"
   * parameter.
   *
   * Assume server reply in JSON object and always decode during return. If
   * you hope to issue a raw query, please use makeRequest().
   *
   * @param $path
   *   The target path, relative to base_path/service_uri or an absolute URI.
   * @param $method
   *   (optional) The HTTP method (default 'GET').
   * @param $params
   *   (optional The GET/POST parameters.
   *
   * @return
   *   The JSON decoded response object.
   *
   * @throws OAuth2Exception
   */
  public function api($path, $method = 'GET', $params = array()) {
    if (is_array($method) && empty($params)) {
      $params = $method;
      $method = 'GET';
    }
	
    // json_encode all params values that are not strings.
    foreach ($params as $key => $value) {
      if (!is_string($value)) {
        $params[$key] = ra($value);
      }
    }
	
    $result = json_decode($this->post(
      APP_RESOURCE_URI,
      $params
    ), TRUE);
	print_r($result);
  /*  // Results are returned, errors are thrown.
    if (is_array($result) && isset($result['error'])) {
      switch ($e->getType()) {
        // OAuth 2.0 Draft 10 style.
        case 'invalid_token':
          $this->setSession(NULL);
        default:
          $this->setSession(NULL);
      }
      throw $e;
    }*/
    return $result;
  }
  
    /**
   * Try to get session object from custom method.
   *
   * By default we generate session object based on access_token response, or
   * if it is provided from server with $_REQUEST. For sure, if it is provided
   * by server it should follow our session object format.
   *
   * Session object provided by server can ensure the correct expirse and
   * base_domain setup as predefined in server, also you may get more useful
   * information for custom functionality, too. BTW, this may require for
   * additional remote call overhead.
   *
   * You may wish to override this function with your custom version due to
   * your own server-side implementation.
   *
   * @param $access_token
   *   (optional) A valid access token in associative array as below:
   *   - access_token: A valid access_token generated by OAuth2.0
   *     authorization endpoint.
   *   - expires_in: (optional) A valid expires_in generated by OAuth2.0
   *     authorization endpoint.
   *   - refresh_token: (optional) A valid refresh_token generated by OAuth2.0
   *     authorization endpoint.
   *   - scope: (optional) A valid scope generated by OAuth2.0
   *     authorization endpoint.
   *
   *  @return
   *    A valid session object in associative array for setup cookie, and
   *    NULL if not able to generate it with custom method.
   */
  public function getSessionObject($access_token = NULL) {
    $session = NULL;

    // Try generate local version of session cookie.
    if (!empty($access_token) && isset($access_token['access_token'])) {
      $session['access_token'] = $access_token['access_token'];
   //   $session['base_domain'] = $this->getVariable('base_domain', OAUTH2_DEFAULT_BASE_DOMAIN);
      $session['expirse'] = isset($access_token['expires_in']) ? time() + $access_token['expires_in'] : time() + 3600;
      $session['refresh_token'] = isset($access_token['refresh_token']) ? $access_token['refresh_token'] : '';
      $session['scope'] = isset($access_token['scope']) ? $access_token['scope'] : '';
      $session['secret'] = md5(base64_encode(pack('N6', mt_rand(), mt_rand(), mt_rand(), mt_rand(), mt_rand(), uniqid())));

      // Provide our own signature.
      $sig = self::generateSignature(
        $session,
        APP_SECRET
      );
      $session['sig'] = $sig;
    }

    // Try loading session from $_REQUEST.
    if (!$session && isset($_REQUEST['session'])) {
      $session = json_decode(
        get_magic_quotes_gpc()
          ? stripslashes($_REQUEST['session'])
          : $_REQUEST['session'],
        TRUE
      );
    }

    return $session;
  }
  
  /**
   * Generate a signature for the given params and secret.
   *
   * @param $params
   *   The parameters to sign.
   * @param $secret
   *   The secret to sign with.
   *
   * @return
   *   The generated signature
   */
  protected function generateSignature($params, $secret) {
    // Work with sorted data.
    ksort($params);

    // Generate the base string.
    $base_string = '';
    foreach ($params as $key => $value) {
      $base_string .= $key . '=' . $value;
    }
    $base_string .= $secret;

    return md5($base_string);
  }
  
  /**
   * Validates a session_version = 3 style session object.
   *
   * @param $session
   *   The session object.
   *
   * @return
   *   The session object if it validates, NULL otherwise.
   */
  public function validateSessionObject($session) {
    // Make sure some essential fields exist.
    if (is_array($session) && isset($session['access_token']) && isset($session['sig'])) {
      // Validate the signature.
      $session_without_sig = $session;
      unset($session_without_sig['sig']);

      $expected_sig = self::generateSignature(
        $session_without_sig,
        APP_SECRET
      );

      if ($session['sig'] != $expected_sig) {
        error_log('Got invalid session signature in cookie.');
        $session = NULL;
      }
    }
    else {
      $session = NULL;
    }
    return $session;
  }
}
