<?php

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/utils.php';

use R301\Controleur\ParticipationControleur;
use R301\Controleur\JoueurControleur;
use R301\Controleur\RencontreControleur;

use R301\Modele\Participation\Poste;
use R301\Modele\Participation\TitulaireOuRemplacant;
use R301\Modele\Participation\Performance;



$http_method = $_SERVER['REQUEST_METHOD'];

$participationControleur = ParticipationControleur::getInstance();

switch($http_method){

  case 'GET':

    $allowed_params = ['rencontreId'];
    $unknown_params = array_diff(array_keys($_GET), $allowed_params);

    if(!empty($unknown_params)){
      deliver_response(400, "Paramètre(s) inconnu(s)");
      break;
    }

    //////////
    // Obtenir la feuille de match pour une rencontre d'ID rencontreId
    if(isset($_GET['rencontreId']) && isIntString($_GET['rencontreId'])){
      $result = $participationControleur->getFeuilleDeMatch($_GET['rencontreId']); // je fais pas de verif ici si vide ou pas car une feuille de match peut être vide si pas encore complétée
      deliver_response(200, "Feuille de match obtenue avec succès.", $result);
      break;
    }
    
    //////////
    // Si aucun argument, liste de toutes les participations
    $result = $participationControleur->listerToutesLesParticipations();
    deliver_response(200, "Liste de toutes les participations obtenue avec succès.", $result);

    break;
  

  case 'POST':

    $postedData = file_get_contents('php://input');
    $data = json_decode($postedData, true);

    //////////
    // Vérification des champs et insertion d'une nouvelle participation d'un joueur à un match avec son poste et titulaireOuRemplacant
    if(arePostChampsValids($data['joueurId'] ?? null,$data['rencontreId'] ?? null,$data['poste'] ?? null,$data['titulaireOuRemplacant'] ?? null)){
      
      $poste = Poste::fromName($data['poste']);
      $titulaireOuRemplacant = TitulaireOuRemplacant::fromName($data['titulaireOuRemplacant']);

      if(!$participationControleur->assignerUnParticipant((int)$data['joueurId'],(int)$data['rencontreId'],$poste,$titulaireOuRemplacant)){
        deliver_response(400, "Le joueur est déjà sur la feuille de match ou le poste est déjà occupé.");
      } else {
        deliver_response(201, "Joueur assigné au match avec succès.");
      }

    } else {
      deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants, au mauvais format ou invalides.");
    }

    break;

  
  case 'PUT':

    $postedData = file_get_contents('php://input');
    $data = json_decode($postedData, true);

    //////////
    // Modification de la performance
    if(isset($_GET['participationId']) && isset($data['performance']) && isIntString($_GET['participationId'])){

      if(isPerformanceStrValid($data['performance']) && $participationControleur->getParticipationById(($_GET['participationId'] !== null))){

        if($participationControleur->mettreAJourLaPerformance((int) $_GET['participationId'], $data['performance'])){
          deliver_response(200, "Performance de la participation d'ID " . $_GET['participationId'] . " mise à jour avec succès.");
        } else {
          deliver_response(422, "Vous ne pouvez pas mettre à jour les performances d'un match qui n'a pas encore eu lieu.");
        }

      } else {
        deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants, au mauvais format ou invalides.");
      }
      break;
    }

    //////////
    // Modification de la participation
    if(isset($_GET['participationId']) && arePutChampsValids($data['poste'] ?? null, $data['titulaireOuRemplacant'] ?? null, $data['joueurId'] ?? null) && isIntString($_GET['participationId'])){
      
      $poste = Poste::fromName($data['poste']);
      $titulaireOuRemplacant = TitulaireOuRemplacant::fromName($data['titulaireOuRemplacant']);

      if(!$participationControleur->modifierParticipation((int)$_GET['participationId'],$poste,$titulaireOuRemplacant,(int)$data['joueurId'])){
        deliver_response(400, "Le joueur est déjà sur la feuille de match ou le poste est déjà occupé.");
      } else {
        deliver_response(200, "Participation d'ID " . $_GET['participationId'] . " modifiée avec succès.");
      }

    } else {
      deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants, au mauvais format ou invalides.");
    }

    break;

  
  case 'DELETE':

    //////////
    // Suppression de la participation d'un joueur à un match
    if(isset($_GET['participationId']) && isIntString($_GET['participationId'])){

      if($participationControleur->supprimerLaParticipation((int)$_GET['participationId'])){
        deliver_response(200, "Participation d'ID " . $_GET['participationId'] . " supprimée avec succès.");
      } else {
        deliver_response(404, "La participation d'ID " . $_GET['participationId'] . " n'existe pas.");
      }
      break;

    }

    //////////
    // Suppression de la performance d'un joueur
    if(isset($_GET['perfParticipationId']) && isIntString($_GET['perfParticipationId'])){

      if($participationControleur->supprimerLaPerformance((int)$_GET['perfParticipationId'])){
        deliver_response(200, "Performance de la participation d'ID " . $_GET['perfParticipationId'] . " supprimée avec succès.");
      } else {
        deliver_response(404, "La participation d'ID " . $_GET['perfParticipationId'] . " n'existe pas, ou le match n'a pas encore eu lieu.");
      }
      break;

    }

    deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants, au mauvais format ou invalides.");
    break;


  default:
    deliver_response(403, "Méthode HTTP non supportée.");
    break;
}


//////////
// Fonction permettant de vérifier la validité des champs passés pour la méthodes POST
// retourne true si valides, false si invalides
function arePostChampsValids($joueurId, $rencontreId, $poste, $titulaireOuRemplacant){

    $joueurControleur = JoueurControleur::getInstance();
    $rencontreControleur = RencontreControleur::getInstance();

    $verif1 = !is_null($joueurId) && !is_null($rencontreId) && !is_null($poste) && !is_null($titulaireOuRemplacant);

    if(!$verif1){
      return false;
    }

    $verif2 = isIntString($joueurId) && isIntString($rencontreId);

    if(!$verif2){
      return false;
    }

    $verif3 = $joueurControleur->getJoueurById($joueurId) !== null;

    $verif4 = $rencontreControleur->getRenconterById($rencontreId) !== null;

    $verif5 = !is_null(Poste::fromName($poste));

    $verif6 = !is_null(TitulaireOuRemplacant::fromName($titulaireOuRemplacant));

    return $verif1 && $verif2 && $verif3 && $verif4 && $verif5 && $verif6;
}


//////////
// Fonction permettant de vérifier la validité des champs passés pour la méthodes PUT
// retourne true si valides, false si invalides
function arePutChampsValids($poste, $titulaireOuRemplacant, $joueurId){

    $joueurControleur = JoueurControleur::getInstance();
    $participationControleur = ParticipationControleur::getInstance();

    !is_null($poste) && !is_null($titulaireOuRemplacant) && !is_null($joueurId);

    $verif1 = isIntString($joueurId);

    if(!$verif1){
      return false;
    }

    $verif2 = $joueurControleur->getJoueurById($joueurId) !== null;

    $verif3 = !is_null(Poste::fromName($poste));

    $verif4 = !is_null(TitulaireOuRemplacant::fromName($titulaireOuRemplacant));

    return $verif1 && $verif2 && $verif3 && $verif4;
}

//////////
// Fonction permettant de vérifier si une performance fournie est valide ou non
// retourne true si valide, false si invalide
function isPerformanceStrValid($perfStr){
  return !is_null(Performance::fromName($perfStr)) || !is_null(Performance::fromValue((int)$perfStr));
}

?>