<?php

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/utils.php';

use R301\Controleur\StatistiquesControleur;

$http_method = $_SERVER['REQUEST_METHOD'];

$statistiquesControleur = StatistiquesControleur::getInstance();

$token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: " . $token,
        'ignore_errors' => true
    ]
]);
$response = file_get_contents("http://localhost/projet-api/R4.01-ProjetAPI-Auth/authapi.php", false, $context);
$responseTab = json_decode($response, true);
if (!$responseTab || $responseTab['status_code'] !== 200) {
    deliver_response(401, "Token invalide");
    die();
}

switch($http_method){

  case 'GET':

    ///////////
    // Vérification de la validité des paramètres (fait ici car plus simple et mieux qu'un if englobant)
    $allowed_params = ['equipe', 'joueurs'];
    $unknown_params = array_diff(array_keys($_GET), $allowed_params);
    
    if(!empty($unknown_params)){
      deliver_response(400, "Paramètre(s) inconnu(s)");
      break;
    }

    if(count($_GET) !== 1){ // Verif qu'un parametre est passé (et qu'un seul), au départ aussi pour éviter le if englobant
      deliver_response(400, "Veuillez fournir exactement un paramètre.");
      break;
    }

    //////////
    // Obtenir les stats de l'équipe
    if(isset($_GET['equipe'])){
      try {
        $result = $statistiquesControleur->getStatistiquesEquipe();
        deliver_response(200,"Statistiques de l'équipe récupérées avec succès.", $result);
      } catch (\Throwable $ex){
        deliver_response(500,"Erreur interne au serveur.");
      }
      break;
    }

    //////////
    // Obtenir les stats des joueurs
    if(isset($_GET['joueurs'])){
      try {
        $result = $statistiquesControleur->getStatistiquesJoueurs();
        deliver_response(200,"Statistiques des joueurs récupérées avec succès.", $result);
      } catch (\Throwable $ex){
        deliver_response(500,"Erreur interne au serveur.");
      }
      break;
    }

    break;


  default:
    deliver_response(403, "Méthode HTTP non supportée.");
    break;

}

?>