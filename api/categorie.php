<?php
require_once "../lib/libwheelmap.php";
$dataToSend = [];

$wm = new Wheelmap('cMvb7QtgGzxsnv8zESwn');
//provo ad acquisire i dati
try{
  $dataToSend = $wm->getCategories();
}catch(Errore $e){
  exit(json_encode(array("error" => $e)));
}

$risposta = json_encode($dataToSend);
exit($risposta);

 ?>
