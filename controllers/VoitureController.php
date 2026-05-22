<?php
/* Contrôleur Voiture - Gère les opérations sur les véhicules*/

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/models/Voiture.php';

class VoitureController {
    private $voiture;
    
    public function __construct() {
        $this->voiture = new Voiture();
    }
    
    /*Récupère tous les véhicules actifs  */
    public function getActifs() {
        return $this->voiture->getActifs();
    }
    
    /*Récupère un véhicule par ID*/
    public function getById($id) {
        if (empty($id)) {
            return null;
        }
        return $this->voiture->getById($id);
    }
    
    /*filtre les véhicules par modèle*/
    public function filtrerEtRechercher($search = '', $sort = '') {
    return $this->voiture->filtrerEtRechercher($search, $sort);
}
    
    
}