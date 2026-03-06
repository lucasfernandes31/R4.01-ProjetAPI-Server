<?php

require_once __DIR__ . '/autoload.php';

use R301\Modele\Joueur\JoueurDAO;
use R301\Modele\Joueur\JoueurStatut;



$http_method = $_SERVER['REQUEST_METHOD'];

$joueurDAO = JoueurDAO::getInstance();

switch($http_method){

  case 'GET':

    ///////////
    // Vérification de la validité des paramètres (A FAIRE POUR TOUTES LES METHODES DE TOUS LES ENDPOINTS, cool niveau sécu)
    $allowed_params = ['statut', 'id'];
    $unknown_params = array_diff(array_keys($_GET), $allowed_params);
    
    if(!empty($unknown_params) || count($_GET) > 1){
        deliver_response(400, "Paramètre(s) inconnu(s) ou trop de paramètres");
        break;
    }

    ///////////
    // Récupération joueurs par statut
    if(isset($_GET['statut'])){

      $statut = JoueurStatut::fromName($_GET['statut']);
      if(is_null($statut)){
        deliver_response(406, "Le statut spécifié n'est pas valide.");
        break;
      }

      $joueurs = $joueurDAO->selectJoueursByStatut($statut);
      deliver_response(200, "Liste des joueurs de statut " . $_GET['statut'] . " récupérée avec succès", $joueurs);
      break;
    }

    ///////////
    // Récupération joueurs par ID
    if(isset($_GET['id'])){
      // Cast en int puis re cast en string pour verifier que c'est un nombre et qu'il est entier
      if((string)(int)$_GET['id'] !== $_GET['id']){
        deliver_response(400, "L'id doit être un entier.");
        break;
      }

      // try catch parce que si aucun joueur de l'ID spécifié, le mapToJoueur plante dans le DAO
      try {
        $joueur = $joueurDAO->selectJoueurById((int)$_GET['id']);
        deliver_response(200, "Joueur d'id " . $_GET['id'] . " récupéré avec succès.", $joueur);
      } catch(\Throwable $e) {
        deliver_response(404, "Aucun joueur d'id " . $_GET['id'] . ".");
      }
      break;
    }

    ///////////
    // Si aucune option spécifiée : liste de tous les joueurs
    $joueurs = $joueurDAO->selectAllJoueurs();
    if(empty($joueurs)){
      deliver_response(204, "Liste des joueurs récupérée mais vide.", $joueurs);
    } else {
      deliver_response(200, "Liste des joueurs récupérée avec succès.", $joueurs);
    }

    break;
  
  default:
    deliver_response(403, "Méthode de requête HTTP non supportée.");
    break;


}







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

?>