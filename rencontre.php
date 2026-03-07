<?php

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/utils.php';

use R301\Controleur\RencontreControleur;
use R301\Modele\Rencontre\RencontreLieu;
use R301\Modele\Rencontre\RencontreResultat;

$http_method = $_SERVER['REQUEST_METHOD'];

$rencontreControleur = RencontreControleur::getInstance();

switch($http_method){

  case 'GET':

    ///////////
    // Vérification de la validité des paramètres (fait ici car plus simple et mieux qu'un if englobant)
    $allowed_params = ['rencontreId'];
    $unknown_params = array_diff(array_keys($_GET), $allowed_params);
    
    if(!empty($unknown_params)){
      deliver_response(400, "Paramètre(s) inconnu(s)");
      break;
    }

    ///////////
    // Récupération rencontre par ID
    if(isset($_GET['rencontreId'])){

      if(!isIntString($_GET['rencontreId'])){
        deliver_response(400, "L'id doit être un entier.");
        break;
      }

      $result = $rencontreControleur->getRenconterById((int)$_GET['rencontreId']);
      $result ? deliver_response(200,"Rencontre d'ID " . $_GET['rencontreId'] . " obtenue avec succès.",$result) : deliver_response(200,"La rencontre d'ID " . $_GET['rencontreId'] . " n'existe pas.");
      break;
    }
    
    ///////////
    // Si aucun rencontreId spécifié : liste de toutes les rencontres
    $result = $rencontreControleur->listerToutesLesRencontres();
    deliver_response(200,"Toutes les rencontres obtenues avec succès.",$result);

    break;


  case 'POST':
    
    $postedData = file_get_contents('php://input');
    $data = json_decode($postedData, true);

    //////////
    // Ajout d'une nouvelle rencontre et vérification des champs
    if(isset($data['dateHeure']) && isset($data['equipeAdverse']) && isset($data['adresse']) && isset($data['lieu'])){

      if(!isDateRencontreValide($data['dateHeure'])){
        deliver_response(400, "La date de rencontre doit être au format Y-m-d H:i:s et doit être dans le futur.");
        break;
      }

      if(!isLieuValid($data['lieu'])){
        deliver_response(400, "Le lieu n'est pas valide. Attendu : DOMICILE ou EXTERIEUR.");
        break;
      }

      $ddr = DateTime::createFromFormat('Y-m-d H:i:s', $data['dateHeure']);
      $lieu = RencontreLieu::fromName($data['lieu']);

      if($rencontreControleur->ajouterRencontre($ddr,$data['equipeAdverse'],$data['adresse'],$lieu)){
        deliver_response(201, "La requête a réussi et une nouvelle rencontre a été créée.");
      } else {
        deliver_response(500, "Erreur interne, impossible de créer la ressource.");
      }

    } else {
      deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants.");
    }

    break;


  case 'PUT':

    $postedData = file_get_contents('php://input');
    $data = json_decode($postedData, true);

    //////////
    // Modifier une rencontre
    if(isset($data['rencontreId']) && isset($data['dateHeure']) && isset($data['equipeAdverse']) && isset($data['adresse']) && isset($data['lieu'])){

      if(!isIntString($data['rencontreId'])){
        deliver_response(400, "L'ID de rencontre doit être un nombre entier.");
        break;
      }

      if(is_null($rencontreControleur->getRenconterById($data['rencontreId']))){
        deliver_response(400, "La rencontre d'ID " . $data['rencontreId'] . " n'existe pas.");
        break;
      }

      if(!isDateRencontreValide($data['dateHeure'])){
        deliver_response(400, "La date de rencontre doit être au format Y-m-d H:i:s et doit être dans le futur.");
        break;
      }
      
      if(!isLieuValid($data['lieu'])){
        deliver_response(400, "Le lieu n'est pas valide. Attendu : DOMICILE ou EXTERIEUR.");
        break;
      }

      $ddr = DateTime::createFromFormat('Y-m-d H:i:s', $data['dateHeure']);
      $lieu = RencontreLieu::fromName($data['lieu']);

      if($rencontreControleur->modifierRencontre((int)$data['rencontreId'], $ddr, $data['equipeAdverse'], $data['adresse'], $lieu)){
        deliver_response(200, "La rencontre d'ID " . $data['rencontreId'] . " a bien été modifiée.");
      } else {
        deliver_response(400, "Impossible de modifier une rencontre déjà passée.");
      }

      break;
    }
    
    //////////
    // Enregistrer les résultats d'une rencontre
    if(isset($data['rencontreId']) && isset($data['resultat'])){

      if(is_null($rencontreControleur->getRenconterById($data['rencontreId']))){
        deliver_response(400, "La rencontre d'ID " . $data['rencontreId'] . " n'existe pas.");
        break;
      }

      if(!isIntString($data['rencontreId'])){
        deliver_response(400, "L'ID de rencontre doit être un nombre entier.");
        break;
      }

      if(!isResultatValid($data['resultat'])){
        deliver_response(400, "Le résultat n'est pas valide. Attendu : VICTOIRE ou DEFAITE ou NULL.");
        break;
      }

      if($rencontreControleur->enregistrerResultat((int)$data['rencontreId'], $data['resultat'])){
        deliver_response(201, "Le résultat de la rencontre d'ID " . $data['rencontreId'] . " a bien été enregistré.");
      } else {
        deliver_response(400, "Impossible d'enregistrer le résultat d'une rencontre qui n'a pas encore eu lieu.");
      }

      break;
    }

    deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants.");
    break;


  case 'DELETE':

    //////////
    // Supprimer une rencontre avec son ID rencontreId
    if(isset($_GET['rencontreId']) && isIntString($_GET['rencontreId'])){

      if(is_null($rencontreControleur->getRenconterById($_GET['rencontreId']))){
        deliver_response(400, "La rencontre d'ID " . $_GET['rencontreId'] . " n'existe pas.");
        break;
      }

      if($rencontreControleur->supprimerRencontre($_GET['rencontreId'])){
        deliver_response(200, "La rencontre d'ID " . $_GET['rencontreId'] . " a bien été supprimée.");
      } else {
        deliver_response(400, "Vous ne pouvez pas supprimer une rencontre dont les résultats ont déjà été saisis.");
      }

    } else {
      deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants, au mauvais format ou invalides.");
    }
    
    break;


  default:
    deliver_response(403, "Méthode HTTP non supportée.");
    break;

}


//////////
// Fonction permettant de vérifier la validité d'une date de rencontre (format + ultérieure à la date de maintenant)
// retourne true si valide, false si invalide
function isDateRencontreValide($dateRencontre){
  
  $ddr = DateTime::createFromFormat('Y-m-d H:i:s', $dateRencontre);
  // est false si format invalide
  return $ddr && $ddr > date("Y-m-d H:i:s");
}

//////////
// Fonction permettant de vérifier si un lieu de rencontre fourni est valide ou non
// retourne true si valide, false si invalide
function isLieuValid($lieuStr){
  return !is_null(RencontreLieu::fromName($lieuStr));
}

//////////
// Fonction permettant de vérifier si un resultat de rencontre fourni est valide ou non
// retourne true si valide, false si invalide
function isResultatValid($resultatStr){
  return !is_null(RencontreResultat::fromName($resultatStr));
}

?>