<?php
require_once "liberror.php";
require_once "libgeolocalization.php";
  /*
   * Libreria di API per estrarre ed inserire i dati dal sito www.Wheelmap.org
  */
  class Wheelmap
  {
    //prima parte dell'url delle API
    private $url_parte_iniziale = 'http://wheelmap.org/api';
    //chiave unuca per accedere alle API di Wheelmap
    private $api_key;

    public function __construct(string $api_key){
      $this->api_key = $api_key;
    }//end costruttore

    //ritorna tutte le categorie in italiano
    public function getCategories(){
      $url = $this->getUrl()."/categories?locale=it";
      //avvio una nuova istanza di curl
      $curl = curl_init();
      // Setto i parametri di curl:
      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION=> true,
        CURLOPT_HTTPHEADER => array(
          "accept: application/json",
          "x-api-key:" . $this->api_key
        ),
      ));
      //eseguo la richiesta di dati
      $risposta = curl_exec($curl);
      $err = curl_error($curl);

      //eseguo il controllo dei codici HTTP
      $html_code = $this->validateHtmlResponse(curl_getinfo($curl, CURLINFO_HTTP_CODE));
      //chiudo la sessione di curl
      curl_close($curl);

      //controllo se ho ricevuto un codice 200 o 201
      if($html_code == 200 || $html_code == 201){
        //controllo la presenza di errori
        if ($err) {
          throw new ErrorNotListed("cURL Error #:" . $err);
        }
        else {
          //ritorno i dati formattati in JSON
          return json_decode($risposta);
        }
      }
    }
    //ritorna tutti i tipi di nodi in italiano
    public function getNodeType(){
      $url = $this->getUrl()."/node_types?locale=it";
      //avvio una nuova istanza di curl
      $curl = curl_init();
      // Setto i parametri di curl:
      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION=> true,
        CURLOPT_HTTPHEADER => array(
          "accept: application/json",
          "x-api-key:" . $this->api_key
        ),
      ));
      //eseguo la richiesta di dati
      $risposta = curl_exec($curl);
      $err = curl_error($curl);

      //eseguo il controllo dei codici HTTP
      $html_code = $this->validateHtmlResponse(curl_getinfo($curl, CURLINFO_HTTP_CODE));
      //chiudo la sessione di curl
      curl_close($curl);

      //controllo se ho ricevuto un codice 200 o 201
      if($html_code == 200 || $html_code == 201){
        //controllo la presenza di errori
        if ($err) {
          throw new ErrorNotListed("cURL Error #:" . $err);
        }
        else {
          //ritorno i dati formattati in JSON
          return json_decode($risposta);
        }
      }
    }
    /*
     * Ritorna le risorse
     */
    public function getResource(string $località, $distanza = 10, $parametri = null, $categoria = null){
      $location = new GeoLocalization();
      $bbox_coordination = $location->getBbox($località, $distanza);
      $data = $this->getResource_Core($bbox_coordination, $parametri, $categoria);
      return $data;
    }
    /*
     * Ritorna la prima parte dell'url delle api; se specificato un array di
     * parametri li aggiunge in coda all'url.
     */
    private function getUrl(array $categoria = null){
      $url = $this->url_parte_iniziale;
      if ($categoria != null) {
        return  $url . '/' . $categoria['tipo'] . '/' . $categoria['valore'];
      }
      else {
        return $url;
      }
    }

    /*
     * Core principale per le richieste di nodi.
     * bbox:      coordinate (Ovest, Sud, Est, Nord), valore obbligatorio
     * parametri: array con altri parametri opzionali.
     * categoria: array con la categoria o i nodi
     */
    private function getResource_Core(string $bbox, array $parametri = null, array $categoria = null){
      //setto l'url delle API
      $url = $this->getUrl($categoria) . "/nodes?&bbox=" . $bbox;

      //se ho dei parametri li converto in query html e li aggiungo all'url
      if($parametri != null){
        $parametri = http_build_query($parametri);
        $url = $url . "&" . $parametri."&locale=it";
      }
      //avvio una nuova istanza di curl
      $curl = curl_init();
      // Setto i parametri di curl:
      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION=> true,
        CURLOPT_HTTPHEADER => array(
          "accept: application/json",
          "x-api-key:" . $this->api_key
        ),
      ));
      //eseguo la richiesta di dati
      $risposta = curl_exec($curl);
      $err = curl_error($curl);

      //eseguo il controllo dei codici HTTP
      $html_code = $this->validateHtmlResponse(curl_getinfo($curl, CURLINFO_HTTP_CODE));
      //chiudo la sessione di curl
      curl_close($curl);

      //controllo se ho ricevuto un codice 200 o 201
      if($html_code == 200 || $html_code == 201){
        //controllo la presenza di errori
        if ($err) {
          throw new ErrorNotListed("cURL Error #:" . $err);
        }
        else {
          //ritorno i dati formattati in JSON
          return json_decode($risposta);
        }
      }
    }

    //metodo per validare i codici d'errore HTTP
    private function validateHtmlResponse($html_status){
      switch ($html_status) {
        //OK
        case '200':
          return 200;
          break;
        //Accepted
        case '202':
          return 202;
          break;
        //Bad Request
        case '400':
          throw new BadRequest();
          break;
        //Authorization Required
        case '401':
          throw new AuthorizationRequired();
          break;
        //Forbidden
        case '403':
          throw new Forbidden();
          break;
        //Not Found
        case '404':
          throw new NotFound();
          break;
        //Not Acceptable
        case '406':
          throw new NotAcceptable();
          break;
        //Internal Server Error
        case '500':
          throw new InternalServerError();
          break;
        //Temporarily nor available
        case '503':
          throw new TemporarilyNotAvailable();
          break;
        //Errore non riconosciuto
        default:
          throw new ErrorNotListed();
          break;
      }
    }

    // public function addNode()
  }




?>
