<?php
require_once "libtelegrambot.php";
/**
 * Classe contenente le operazioni principali del bot
 */
class BotCore
{
	private $botToken = "327776009:AAGfoRlze1JUCTf1NQHKxrBa8D8XCfUHyWE";
	private $fileCategories = __DIR__."/res/categories.txt";
	private $fileNodes = __DIR__."/res/nodes_bot.txt";
	private $filePage = __DIR__."/res/page_bot";
	private $bot;
	private $chat_id;
	private $message;
	private $text;

	//se non presenti creo i nuovi file
	public function __construct()
	{
		if(!file_exists($this->fileCategories)){
			$url = "localhost/api/categorie.php";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$risposta = curl_exec($ch);
			curl_close($ch);
			file_put_contents($this->fileCategories, $risposta);
		}
		if(!file_exists($this->fileNodes)){
			file_put_contents($this->fileNodes, "");
		}
		if(!file_exists($this->filePage)){
			file_put_contents($this->filePage, 1);
		}
	}//end costruttore

	//metodo principale ed unico accessibile all'esterno della classe
	public function esegui_codice(){
		$this->bot = new Bot($this->botToken);
		//richiede aggiornamenti al bot
		$update = $this->bot->getUpdate();

		//se ci sono nuovi messaggi li elaboro
		if($update != false){
			$result = $update->result[0];
			$this->message = $result->message;
			$this->chat_id = $this->message->chat->id;
			//se il messaggio conteneva testo
			if(isset($this->message->text)){
				$this->text = explode(" ", $this->message->text);
				$this->valutaComando();
			}
			//se il messaggio non conteneva testo
			else{
				$this->bot->sendMessage($this->chat_id, "<b>Errore!</b> Il messaggio deve contenere solo testo");
			}
		}
		//altrimenti non faccio nulla
		else{
			echo "Nessun aggiornamento!";
		}
	}
	//metodo che ritorna una stringa con i dati richiesti
	public function ottieniDati($page, $city){
		$url = "localhost/api/risorse.php?city={$city}&raggio=5&sedia=yes&pagina={$page}&per_page=5&categoria=0";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$risposta = curl_exec($ch);
		$jdata = json_decode($risposta);
		curl_close($ch);
		$jcateg = json_decode(file_get_contents($this->fileCategories), true);
		$string = "";
		for($i = 0; $i < $jdata->meta->item_count; $i++){
			$categ_localized = $jcateg["categories"][$jdata->nodes[$i]->category->id - 1]["localized_name"];
			$string = $string . "N: ".(($jdata->nodes[$i]->name == NULL) ? "N.D." : $jdata->nodes[$i]->name)."\n".
													"S: ".(($jdata->nodes[$i]->wheelchair == NULL) ? "N.D." : $this->valutaSediaLocale($jdata->nodes[$i]->wheelchair))."\n".
													"C: {$categ_localized}\n".
													"C: ".round($jdata->nodes[$i]->lat, 3).", ".round($jdata->nodes[$i]->lon, 3)."\n\n";
		}
		$next = "";
		if($page < $jdata->meta->num_pages){
			$next = "/next";
		}
		return "Pagina: " . $page."\n".$string . $next;
	}
	//metodo che valuta quale comando è stato richiesto e lo esegue
	private function valutaComando(){
		switch ($this->text[0]) {
			//messaggio di benvenuto
			case '/start':
				$this->bot->sendMessage($this->chat_id, "Benvenuto " .
			 																		$this->message->chat->first_name .
			 																		"\n usa \help per avere una lista dei comandi disponibili");
				break;
			//spiegazione comandi
			case '/help':
		 		$this->bot->sendMessage($this->chat_id, "<b>Comandi disponibili:</b>\n".
		 																			"- /start - avvia il bot\n".
		 																			"- /help - mostra la lista dei comandi disponibili \n".
		 																			"- /luogo città - mostra le info sulla città \n".
																					"- /next - mostra più info");
				break;
			//ritorna info sulla città
			case '/luogo':
				$this->elabora_luogo();
				break;
			//mostra più info
			case '/next':
				$this->elabora_next();
				break;
			//comando non riconosciuto
			default:
				$this->bot->sendMessage($chat_id, "<b>Comando non riconosciuto...</b> Riprovare!\n".
																					"Provare /help per una lista di comandi");
				break;
		}
	}
	//metodo che elabora il comando /luogo
	private function elabora_luogo(){
		file_put_contents($this->filePage, 1);
		file_put_contents($this->fileNodes, $this->text[1]);
		if(sizeof($this->text) > 1){
			$messaggio_dati = $this->ottieniDati(1, $this->text[1]);
			$this->bot->sendMessage($this->chat_id, $messaggio_dati);
		}
		else{
			$this->bot->sendMessage($this->chat_id, "Inserire dei parametri validi al comando!\n".
																							"il comando è in forma /luogo città");
		}
	}
	//metodo che elabora il comando /next
	private function elabora_next(){
		$pagina = (file_get_contents($this->filePage) + 1);
		$città = file_get_contents($this->fileNodes);
		file_put_contents($this->filePage, $pagina);
		$this->bot->sendMessage($this->chat_id, $this->ottieniDati($pagina, $città));
	}
	//metodo che converte in italiano lo stato della sedia a rotelle
	private function valutaSediaLocale($sedia){
		switch ($sedia) {
			case 'yes':
				return 'Si';
				break;
			case 'limited':
				return 'Limitato';
				break;
			case 'no':
				return 'No';
				break;
			case 'unknown':
				return 'Sconosciuto';
				break;
		}
	}
}
 ?>
