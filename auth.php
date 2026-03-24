<?php

// Endpoint côté serveur permettant de transmettre les demandes d'authentification entre le client et le serveur d'Auth

// On a fait ce choix d'architecture (que le client doivent appeler le serveur des données et pas celui d'auth) 
// afin que TOUS les appels du client soient uniquement vers le serveur et que celui-ci ne sache pas comment est gérée l'authentification.

// Le serveur joue donc ici uniquement le rôle d'intermédiaire.

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/utils.php';

$http_method = $_SERVER['REQUEST_METHOD'];

$urlAPIAuth = 'https://r401-jwt.alwaysdata.net/authapi';

switch($http_method) {
    case 'POST':

        $postedData = file_get_contents('php://input'); //pour récupérer le username et password
        $data = json_decode($postedData, true);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content'=>json_encode($data),//pour transformer le tableau data en string JSON
                'ignore_errors' => true
            ]
        ]);

        $response = file_get_contents($urlAPIAuth, false, $context);
        $responseTab = get_response_tab_without_warnings($response);

        deliver_response($responseTab['status_code'], $responseTab['status_message'], $responseTab['data']);//renvoie le code, le message du status et le token

        break;
    default:
        deliver_response(403,"Méthode HTTP non supportée. Veuillez utiliser POST.");
        break; 
}
?>