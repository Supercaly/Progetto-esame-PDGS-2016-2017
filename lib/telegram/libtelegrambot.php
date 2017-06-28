<?php
	class Bot{
		//token del bot
		private $token;
		private $url_part = "https://api.telegram.org";
		//file di testo con l'ultimo update
		private $fileLastUpdate = __DIR__."/res/last_update.txt";

		public function __construct($token){
			$this->token = $token;
			//$this->url = $this->url_part . '/bot' . $this->token . '/';
		}

		private function getUrl(){
			return $this->url_part . '/bot' . $this->token . '/';
		}

		//metodo che crea e inizializza il file con l'ultimo update.
		private function inizialize(){
			file_put_contents($this->fileLastUpdate, 0);
		}

		//metodo per ottenere gli ultimi messaggi dalle API di telegram
		public function getUpdate(int $limit = null, $timeout= null){
			//se il file contenente l'ultimo update non esiste ne creo uno nuovo
			if(!file_exists($this->fileLastUpdate))
				$this->inizialize();
			$prev_id = intval(file_get_contents($this->fileLastUpdate));

			$query = array();
    		if(is_numeric($prev_id + 1))
        		$query['offset'] = ($prev_id + 1);
    		if(is_numeric($limit) && $limit > 0)
        		$query['limit'] = $limit;
    		if($timeout === true)
        		$parameters['timeout'] = 60;
    		if(is_numeric($timeout) && $timeout > 0)
        		$parameters['timeout'] = $timeout;

			foreach ($query as $key => $value) {
				if (!is_numeric($value) && !is_string($value)) {
            		$value = json_encode($value);
        		}
			}

			$parametri = http_build_query($query);

			$url = $this->getUrl() . 'getUpdates?' . $parametri;  //"limit=1&&offset=" . ($prev_id + 1);


			$curl = curl_init();
			curl_setopt_array($curl,
								array(CURLOPT_URL => $url,
										CURLOPT_RETURNTRANSFER => true));
			$update = json_decode(curl_exec($curl));
			curl_close($curl);

			if(count($update->result) == 0){
				return false;
			}
			else{
				file_put_contents($this->fileLastUpdate, $update->result[0]->update_id);
				return $update;
			}
		}

		//metodo che invia un messaggio ad una chat
		public function sendMessage($chatId, $message){
			$curl = curl_init();
			$url = $this->getUrl() . 'sendMessage?chat_id=' . $chatId . '&text=' . urlencode($message) . "&parse_mode=HTML";

			curl_setopt_array($curl,
								array(CURLOPT_URL=> $url,
										CURLOPT_RETURNTRANSFER => true));
			curl_exec($curl);
			curl_close($curl);
		}

		//metodo che ritorna le info sul bot
		public function getMe(){
			$curl = curl_init();
			$url = $this->getUrl() . "getMe";

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$risposta = curl_exec($curl);
			return json_decode($risposta);
		}
	}
    ?>
