<?php
require_once "../lib/libwheelmap.php";
$dataToSend = [];

$citta;
$raggio = 5;
$pagina = 1;
$per_pagina = 20;
$sedia = 'yes';
$categoria = 0;
$categ_array = NULL;

if(isset($_GET['city'])){
  $citta = $_GET['city'];
}
else {
  exit(json_encode(array("error" => "invalid city")));
}
if(isset($_GET['raggio'])){
  $raggio = $_GET['raggio'];
}
if(isset($_GET['pagina'])){
  $pagina = $_GET['pagina'];
}
if(isset($_GET['per_page'])){
  $per_pagina = $_GET['per_page'];
}
if(isset($_GET['sedia'])){
  $sedia = $_GET['sedia'];
}
if(isset($_GET['categoria'])){
  $categoria = $_GET['categoria'];
}

if($categoria != 0){
  $categ_array = array('tipo' => 'categories', 'valore' => $categoria);
}
$wm = new Wheelmap('cMvb7QtgGzxsnv8zESwn');
//provo ad acquisire i dati
try{
  $dataToSend = $wm->getResource($citta, $raggio, array('page' => $pagina, 'per_page' => $per_pagina, 'wheelchair' => $sedia), $categ_array);
}catch(Errore $e){
  exit(json_encode(array("error" => $e)));
}

$risposta = json_encode($dataToSend);
exit($risposta);

 ?>
