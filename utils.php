<?php

//////////
// Fonction permettant d'envoyer la réponse complete au client
function deliver_response($status_code, $status_message, $data=null){

    header("Access-Control-Allow-Origin: *");
    
    // Paramétrage de l'entête HTTP
    http_response_code($status_code); // Utilise un message standardisé en fonction du code HTTP

    // header("HTTP/1.1 $status_code $status_message");
    // Permet de personnaliser le message associé au code HTTP
    header("Content-Type:application/json; charset=utf-8"); // Indique au client le format de la réponse

    $response['status_code'] = $status_code;
    $response['status_message'] = $status_message;
    $response['data'] = $data;

    // Mapping de la réponse au format JSON
    $json_response = json_encode($response);

    if($json_response === false){
      die('json encode ERROR : '.json_last_error_msg());
    }

    // Affichage de la réponse (retourné au client)
    echo $json_response;
}

//////////
// Fonction permettant de vérifier si un string est un nombre entier (utile notamment pour les param GET)
function isIntString($nombre){
  // Cast en int puis re cast en string pour verifier que c'est un nombre et comparaison avec lui-même avant conversion pour vérifier qu'il est entier
  return (string)(int)$nombre === $nombre && (int)$nombre > 0;
}


?>