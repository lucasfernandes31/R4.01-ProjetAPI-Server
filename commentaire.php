<?php

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/utils.php';

use R301\Controleur\CommentaireControleur;


$http_method = $_SERVER['REQUEST_METHOD'];

$commentaireControleur = CommentaireControleur::getInstance();

switch($http_method){

  case 'GET':

    //////////
    // Récupération des commentaires pour un joueur d'ID joueurId
    if(isset($_GET['joueurId'])){

      try{ 
        $result = $commentaireControleur->listerLesCommentairesDuJoueur($_GET['joueurId']);
        deliver_response(200, "Commentaires du joueur d'ID " . $_GET['joueurId'] . " récupérés avec succès.", $result);
      } catch (\Throwable $ex) {
        deliver_response(404, "Le joueur d'ID " . $_GET['joueurId'] . " n'existe pas.");
      }

    } else {
      deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants ou au mauvais format.");
    }

    break;

  
  case 'POST':

    $postedData = file_get_contents('php://input');
    $data = json_decode($postedData, true);

    //////////
    // Création d'un nouveau commentaire pour le joueur d'ID joueurId
    if(isset($data['contenu']) && isset($data['joueurId'])){

      try{

        $commentaireControleur->ajouterCommentaire($data['contenu'],$data['joueurId']);
        deliver_response(200, "Commentaire inséré avec succès pour le joueur d'ID " . $data['joueurId'] . ".");

      } catch (\PDOException $ex) { // là je me sers direct de la PDO exception car contrairement à listerCommentaires la fonction pour ajouter ne fait pas d'exit
          if ($ex->getCode() === '23000') {
              deliver_response(404, "Le joueur d'ID " . $data['joueurId'] . " n'existe pas.");
          } else {
              deliver_response(500, "Erreur interne du serveur.");
          }
      }

    } else {
      deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants ou au mauvais format.");
    }

    break;

  
  case 'DELETE':

    //////////
    // Suppression du commentaire d'ID commentaireId
    if(isset($_GET['commentaireId'])){

      if($commentaireControleur->supprimerCommentaire($_GET['commentaireId'])){
        deliver_response(200, "Commentaires d'ID " . $_GET['commentaireId'] . " supprimé avec succès.");
      } else {
        deliver_response(404, "Le commentaire d'ID " . $_GET['commentaireId'] . " n'existe pas.");
      }

    } else {
      deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants ou au mauvais format.");
    }

    break;



  default:
    deliver_response(403, "Méthode HTTP non supportée.");
    break;
}

?>