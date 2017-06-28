<?php
/**
 * Classe che gestisce i dati richiesti dal sito web
 */
class DataMap
{
  //riferimenti ai vari file esterni usati per memorizzare vari dati
  private $fileDati = __DIR__."/res/dati.txt";
  private $filePage = __DIR__."/res/page.txt";
  private $fileNodes = __DIR__."/res/nodes.txt";
  private $fileCategories = __DIR__."/res/categories.txt";

  function __construct()
  {
    //creo i file esterni e li inizializzo nel caso in cui non esistano
    if(!file_exists($this->filePage)){
      file_put_contents($this->filePage, 1);
    }
    if(!file_exists($this->fileDati)){
      file_put_contents($this->fileDati, "");
    }
    if(!file_exists($this->fileNodes)){
      $url = "localhost/api/tipo_nodi.php";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $risposta = curl_exec($ch);
      curl_close($ch);
      file_put_contents($this->fileNodes, $risposta);
    }
    if(!file_exists($this->fileCategories)){
      $url = "localhost/api/categorie.php";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $risposta = curl_exec($ch);
      curl_close($ch);
      file_put_contents($this->fileCategories, $risposta);
    }
  }//end costruttore

/*
 * Metodo principale per stampare i dati nalla pagina html
 */
function getData(){
  //se il cookie con i dati o quello con una nuova pagina è settato elaboro i dati
  if(isset($_COOKIE["dati"]) || isset($_COOKIE["next"])){
    $jdata;       //dati in JSON presi dalle API locali
    $errore;      //errore nell'acquisizione dei dati
    $page = 1; //numero della pagina attuale

    //array contenente ogni dato separato
    $dati = explode(",", file_get_contents($this->fileDati));

    //se il cookiecon i dati è presente elaboro i nuovi dati
    if(isset($_COOKIE["dati"])){
      $dati = explode(",", $_COOKIE["dati"]);
      file_put_contents($this->fileDati, $dati[0].",".$dati[1].",".$dati[2].",".$dati[3]);
      file_put_contents($this->filePage, 1);
      //rimuovo il cookie con i dati
      setcookie("dati", "", time()-3600, "/");
    }
    //se il cookie con la nuova pagina è presente elaboro i vecchi dati
    if(isset($_COOKIE["next"])){
      $dati = explode(",", file_get_contents($this->fileDati));
      //rimuovo il cookie con la nuova pagina
      setcookie("next", "", time()-3600, "/");
      //setto il numero della pagina (se i dati sono nuovi la pagina è 1)
      $page = file_get_contents($this->filePage) + 1;
      file_put_contents($this->filePage, $page);
    }
    //setto alcune variabili con i dati per un facile accesso
    $citta = $dati[0];
    $raggio = $dati[1];
    $categoria = $dati[2];
    $sedia = $dati[3];

    $jcategories = json_decode(file_get_contents($this->fileCategories), true);
    $jnode_types = json_decode(file_get_contents($this->fileNodes), true);

    //chiedo i dati alle API della piattaforma
    $url = "localhost/api/risorse.php?city={$citta}&raggio={$raggio}&sedia={$sedia}&pagina={$page}&per_page=20&categoria={$categoria}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $risposta = curl_exec($ch);
    $jdata = json_decode($risposta);
    curl_close($ch);

    //se non è presente alcun errore
    if(!isset($jdata->error)){
      $item_count = $jdata->meta->item_count;
      $nodes = $jdata->nodes;
      $nullo  = "Non Disponibile";

      $string = "";
      for($i = 0; $i < $item_count; $i++){
        $categories = $jcategories["categories"][$nodes[$i]->category->id - 1]["localized_name"];
        $node_type = $this->getNodeLocale($jnode_types, $nodes[$i]->node_type->id);
        //echo $node_type;
        $string =  $string."<tr><td><b><i>Nome:</i></b></td>              <td>".(($nodes[$i]->name == NULL) ? $nullo : $nodes[$i]->name)."</td>
                                <td><b><i>Sedia a rotelle:</i></b></td>   <td>".(($this->getWheelchairLocale($nodes[$i]->wheelchair) == NULL) ? $nullo : $this->getWheelchairLocale($nodes[$i]->wheelchair))."</td>
                                <td><b><i>Bagno per disabili:</i></b></td><td>".(($this->getWheelchairLocale($nodes[$i]->wheelchair_toilet) == NULL) ? $nullo : $this->getWheelchairLocale($nodes[$i]->wheelchair_toilet))."</td>
                                <td><b><i>Categoria: </i></b></td>         <td>{$categories}</td>
                                <td><b><i>Tipo: </i></b></td>              <td>{$node_type}</td>
                                <td><b><i>Indirizzo:</i></b></td>          <td>".(($nodes[$i]->street == NULL) ? $nullo : $nodes[$i]->street)."</td>
                                <td><b><i>Posizione (lat-lon):</i></b></td><td>".round($nodes[$i]->lat, 3).", ".round($nodes[$i]->lon, 3)."</td>
                           </tr>";
      }

      echo "<table><tbody>{$string}</tbody></table>";

      $end_page = "";
      if($page >= $jdata->meta->num_pages){
        $end_page = "disabled";
      }
      echo '<div align="right"><text>Pagina: '.$page.'</text><button id="next_page" '. $end_page .'>Avanti</button></div>';
    }
    //altrimenti stampo un messaggio d'errore
    else {
      echo "<p>Errore inaspettato: ".$jdata->error."</p>";
    }
  }
  //altrimenti comunico l'assenza di dati
  else {
    file_put_contents($this->filePage, 1);
    file_put_contents($this->fileDati, "");

    echo "<p>Nulla da mostrare</p>";
    }
  }

  function getNodeLocale($node, $type){
    for ($i = 0; $i < sizeof($node["node_types"]) ; $i++) {
      if($node["node_types"][$i]["id"] == $type)
        return $node["node_types"][$i]["localized_name"];
    }
  }

  function getWheelchairLocale($wheel_stat){
    switch ($wheel_stat) {
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
      default:
        return NULL;
        break;
    }
  }
}



 ?>
