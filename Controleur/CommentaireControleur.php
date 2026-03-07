<?php

namespace R301\Controleur;

use DateTime;
use R301\Modele\Joueur\Commentaire\Commentaire;
use R301\Modele\Joueur\Commentaire\CommentaireDAO;
use R301\Modele\Joueur\Joueur;
use R301\Modele\Joueur\JoueurDAO;
use R301\Modele\Joueur\JoueurStatut;

class CommentaireControleur {
    private static ?CommentaireControleur $instance = null;
    private readonly CommentaireDAO $commentaires;

    private function __construct() {
        $this->commentaires = CommentaireDAO::getInstance();
        $this->joueurs = JoueurDAO::getInstance();
    }

    public static function getInstance(): CommentaireControleur {
        if (self::$instance == null) {
            self::$instance = new CommentaireControleur();
        }
        return self::$instance;
    }

    public function ajouterCommentaire( // FAIT
        string $contenu,
        string $joueurId
    ) : bool {

        $commentaireACreer = new Commentaire(
            0,
            $contenu,
            new DateTime()
        );

        return $this->commentaires->insertCommentaire($commentaireACreer, $joueurId);
    }

    public function listerLesCommentairesDuJoueur(string $joueurId) : array { // FAIT
        $this->joueurs->selectJoueurById($joueurId); // pour renforcer robustesse de l'API, fait planter la fonction (exit) si aucun joueur trouvé
        return $this->commentaires->selectCommentaireByJoueurId($joueurId);
    }

    public function supprimerCommentaire(string $commentaireId) : bool { // FAIT
        return $this->commentaires->deleteCommentaire($commentaireId);
    }
}