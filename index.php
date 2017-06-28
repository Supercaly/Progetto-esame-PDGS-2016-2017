<!DOCTYPE html>
<html>
  <!-- HEAD -->
  <head>
    <title>Progetto PDGS</title>
  </head>
  <!-- BODY -->
  <body>
    <h1>Mappa</h1>
    <!-- Parte superiore in cui inserire i dati -->
    <div id="top_menu_bar">
      <text>Città:</text>
      <input id="text_city" type="text" size="15" maxlength="200"/>
      <text>Raggio (Km):</text>
      <input id="text_distance" type="number" size="8" min="2" max="50" value="5"/>
      <fieldset>
        <legend>Tipo di servizio</legend>
        <input type="radio" name="filtro_categoria" value="0" checked/>Tutti
        <input type="radio" name="filtro_categoria" value="1"/>Trasporto pubblico
        <input type="radio" name="filtro_categoria" value="2"/>Alimenti
        <input type="radio" name="filtro_categoria" value="3"/>Tempo Libero
        <input type="radio" name="filtro_categoria" value="4"/>Banca / Ufficio postale
        <input type="radio" name="filtro_categoria" value="5"/>Educazione
        <input type="radio" name="filtro_categoria" value="6"/>Shopping
        <input type="radio" name="filtro_categoria" value="7"/>Sport
        <input type="radio" name="filtro_categoria" value="8"/>Turismo
        <input type="radio" name="filtro_categoria" value="9"/>Alloggi
        <input type="radio" name="filtro_categoria" value="10"/>Vari
        <input type="radio" name="filtro_categoria" value="11"/>Governo
        <input type="radio" name="filtro_categoria" value="12"/>Salute
      </fieldset>
      <fieldset>
        <legend>Disponibilità di sedia a rotelle:</legend>
        <input type="radio" name="filtro_sedia" value="yes" checked/>Si
        <input type="radio" name="filtro_sedia" value="limited"/>Limitato
        <input type="radio" name="filtro_sedia" value="no"/>No
        <input type="radio" name="filtro_sedia" value="unknown"/>Sconosciuto
      </fieldset>
      <button id="btn_invia">Invia</button>
    </div>
    <!-- Parte centrale in cui sono mostrati i dati -->
    <div id="central_page">
      <h3>Informazioni</h3>
      <div id="container_dati">
        <?php
          include_once "lib/libmappa.php";
          $map = new DataMap();
          $map->getData();
        ?>
      </div>
    </div>
    <!-- Script per settare gli EventListener e inviare al server le informazioni -->
    <script>
      document.getElementById("btn_invia").addEventListener("click", btn_invia_click, false);
      //funzione che crea i cookie con i dati acquisiti
      function btn_invia_click(){
        var citta = document.getElementById("text_city").value;
        //se il campo città è stato compilato creo il cookie con i dati
        if(citta != ""){
          var raggio = document.getElementById("text_distance").value;
          var categories = document.querySelector('input[name="filtro_categoria"]:checked').value;
          var sedia = document.querySelector('input[name="filtro_sedia"]:checked').value;
          document.cookie = "dati = "+citta+","+raggio+","+categories+","+sedia;
          window.location.reload();
        }
        //altrimenti segnalo un errore
        else {
          alert("Errore! Assicurarsi di aver completato tutti i campi");
        }

      }
      if(document.getElementById("next_page")){
        document.getElementById("next_page").addEventListener("click", btn_next_page, false);
      }

      function btn_next_page(){
        document.cookie = "next = 1";
        window.location.reload();
      }
    </script>
    <style>
      body, #container_dati {
        height: 100%;
        width: 100%;
      }
    </style>
  </body>
</html>
