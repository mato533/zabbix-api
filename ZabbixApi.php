<?php
interface ZabbixException { }

class HttpConnectionException extends \RuntimeException implements ZabbixException { }

class ZabbixApiExeption extends \RuntimeException implements ZabbixException { }

class ZabbixApi {
	private $host ='';
	private $id = '';
	private $token = null;

	public function __construct($host, $user, $pass) {
		$this->host = (string)filter_var($host);
		$this->id = date('YmdHis');
		$this->login($user, $pass);
	}

	public function isConnected() {
		if (empty($this->token)) {
			return false;
		} else {
			return true;
		}
	}

	private function login($user, $pass) {
		$params = array(
			'user'      => $user,
			'password'  => $pass
		);
		$response = $this->executeRequest('user.login', $params);
		$this->token = $response['result'];
	}

	public function executeRequest($method, $params) {
		$request = array(
			'jsonrpc'   => '2.0',
			'method'    => $method,
			'params'    => $params,
			'id'        => $this->id,
			'auth'      => $this->token
		);

		$request_json = json_encode($request);

		$url = 'http://' . $this->host . '/zabbix/api_jsonrpc.php';
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_HTTPHEADER => array('Content-Type: application/json-rpc'),
			CURLOPT_FAILONERROR => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $request_json	
		]);

		$response = curl_exec($ch);
		
		$errno = curl_errno($ch);
		$error = curl_error($ch);

		curl_close($ch);
		
		$response = json_decode($response, true);		

		if (CURLE_OK !== $errno or empty($response)) {
			throw new HttpConnectionException($error, $errno);
		} elseif ( ! empty($response['error']) ) {
			throw new ZabbixApiExeption($response['error']['data'], $response['error']['code']);			
		}

//		echo json_encode($response, JSON_PRETTY_PRINT);
		return $response;
	}

	public function logout() {
		$response = $this->executeRequest('user.logout', []);
		return $response['result'];
	} 
}
?>
