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

//////////
// Décode un url en base 64
function base64url_decode($data) {
    // Remplacer les caractères URL-safe par les caractères base64 standard
		// On fait l'inverse du encode en remplacant '-_' par '+/' voir doc sur google, c'est chiant.
		// jwt utilise base64_url qui est légèrement différent du base64 standard (adapté aux url)
    $base64 = strtr($data, '-_', '+/');
    
    // Ajouter le padding '=' si nécessaire
		// Car base64 fait des blocs de 4 caractères et si il y en a pas assez on rajoute des = pour combler
    $remainder = strlen($base64) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $base64 .= str_repeat('=', $padlen);
    }
    
    return base64_decode($base64);
}

//////////
// Retourne le payload d'un token JWT sous forme de tableau
function get_payload($jwt){
	$token_parts = explode('.',$jwt);

	$base64_url_payload = $token_parts[1];
	$payload_json = base64url_decode($base64_url_payload);

	$payload = json_decode($payload_json, true);

	return $payload;

}

//////////
// Permet de retourner uniquement la réponse donnée et pas les warnings
// que le serveur en prod génère (fait crash sinon)
function get_response_tab_without_warnings($response){
    $jsonStart = strpos($response, '{');
    if ($jsonStart > 0) {
        $response = substr($response, $jsonStart);
    }
    $responseTab = json_decode($response, true);
    return $responseTab;
}

?>