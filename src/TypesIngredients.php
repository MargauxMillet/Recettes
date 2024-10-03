<?php

namespace App;

class TypesIngredients {

    public function isFruitsLegumes ($id)
    {
        if($this->isType($id, 1)){
            return true; 
        } else {
            return false;
        }
    }

    public function isProteines ($id)
    {
        if($this->isType($id, 2)){
            return true; 
        } else {
            return false;
        }
    }

    public function isProduitsFrais ($id)
    {
        if($this->isType($id, 3)){
            return true; 
        } else {
            return false;
        }
    }

    public function isFeculents ($id)
    {
        if($this->isType($id, 4)){
            return true; 
        } else {
            return false;
        }
    }

    public function isAutres ($id)
    {
        if($this->isType($id, 5)){
            return true; 
        } else {
            return false;
        }
    }

    public function isType ($id, $iCat_id)
    {
        $pdo = ConnectionBD::getPDO();
        //Je recupère la liste du type
        $requete = $pdo->prepare('
            SELECT ingredient_id 
            FROM iCat_ingredient 
            WHERE iCat_id = :id
        ');
        $requete->execute(['id' => $iCat_id]);
        $typesids = $requete->fetchAll();
        $types = [];
        foreach ($typesids as $typeid) {
            $types[] = $typeid['ingredient_id'];
        }

        if(in_array($id, $types)) {
            return true;
        } else {
            return false;
        }
    }

}

?>