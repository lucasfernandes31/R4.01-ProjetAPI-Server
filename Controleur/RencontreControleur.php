<?php

namespace R301\Controleur;

use DateTime;
use R301\Modele\Rencontre\Rencontre;
use R301\Modele\Rencontre\RencontreDAO;
use R301\Modele\Rencontre\RencontreLieu;
use R301\Modele\Rencontre\RencontreResultat;

class RencontreControleur {
    private static ?RencontreControleur $instance = null;
    private readonly RencontreDAO $rencontres;

    private function __construct() {
        $this->rencontres = RencontreDAO::getInstance();
    }

    public static function getInstance(): RencontreControleur {
        if (self::$instance == null) {
            self::$instance = new RencontreControleur();
        }
        return self::$instance;
    }

    public function ajouterRencontre( // FAIT
        DateTime $dateHeure,
        string $equipeAdverse,
        string $adresse,
        RencontreLieu $lieu
    ) : bool {

        $rencontreAAjouter = new Rencontre(
            $dateHeure,
            $equipeAdverse,
            $adresse,
            $lieu
        );

        return $this->rencontres->insertRencontre($rencontreAAjouter);
    }

    public function enregistrerResultat( // FAIT
        int $rencontreId,
        string $resultat
    ) : bool {
        $rencontreAModifier = $this->rencontres->selectRencontreById($rencontreId);

        if (!$rencontreAModifier->estPassee()) {
            return false;
        } else {
            $rencontreAModifier->setResultat(RencontreResultat::fromName($resultat));

            return $this->rencontres->enregistrerResultat($rencontreAModifier);
        }
    }

    public function getRenconterById(int $rencontreId) : ?Rencontre { // FAIT
        return $this->rencontres->selectRencontreById($rencontreId);
    }

    public function listerToutesLesRencontres() : array { // FAIT
        return $this->rencontres->selectAllRencontres();
    }

    public function modifierRencontre(
        int $rencontreId,
        DateTime $dateHeure,
        string $equipeAdverse,
        string $adresse,
        RencontreLieu $lieu
    ) : bool {

        $rencontreAModifier = $this->rencontres->selectRencontreById($rencontreId);

        if (
            $rencontreAModifier->estPassee()
            || $dateHeure < new DateTime()
        ) {
            return false;
        } else {
            $rencontreAModifier->setDateEtHeure($dateHeure);
            $rencontreAModifier->setEquipeAdverse($equipeAdverse);
            $rencontreAModifier->setAdresse($adresse);
            $rencontreAModifier->setLieu($lieu);

            return $this->rencontres->updateRencontre($rencontreAModifier);
        }
    }

    public function supprimerRencontre(int $rencontreId) : bool { // FAIT
        $rencontreASupprimer = $this->rencontres->selectRencontreById($rencontreId);

        if($rencontreASupprimer->getResultat() != null) {
            return false;
        } else {
            return $this->rencontres->supprimerRencontre($rencontreId);
        }
    }
}