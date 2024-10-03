<?php

namespace App;

class ListeDeCourses {

    private $ingredientsPresents = [];
    private $ingredientsPresentsInfos = [];

    public function getIngredientsType()
    {
        $divHTML = '';

        foreach ($this->ingredientsPresentsInfos as $ingredient) {
            if($ingredient['quantite'] >= 1000 && $ingredient['unite']== 'g') {
                $ingredient['unite'] = 'kg';
                $ingredient['quantite'] = $ingredient['quantite'] / 1000 ;
            }
            if($ingredient['quantite'] >= 100 && $ingredient['unite']== 'cL') {
                $ingredient['unite'] = 'L';
                $ingredient['quantite'] = $ingredient['quantite'] / 100 ;
            }
    
            $text = '' ;
            
            if($ingredient['quantite'] !== 0 && $ingredient['quantite'] !== '') {
                $text .= $ingredient['quantite'] . ' ';
            }
    
            $text .= $ingredient['unite'];
    
            if($ingredient['unite'] !== null && $ingredient['unite'] !== '') {
                $text .= ' de ';
            }
            
            if($ingredient['quantite'] > 1 && ($ingredient['unite'] == null || $ingredient['unite'] == '')) {
                $text .= $ingredient['name'] . 's';
            } else {
                $text .= $ingredient['name'];
            }
    
            $divHTML .= <<<HTML
    <p class='margin-top-15 catamaran p-20 noir max-width-145'>$text</p>
    HTML;
        }
        
        return $divHTML;
    }

    public function getIngredientsPresentsType($recettesInfos, $typeachercher) {

        $types = new TypesIngredients;
        $this->ingredientsPresents = [];
        $this->ingredientsPresentsInfos = [];

        foreach ($recettesInfos as $recette) {
            foreach ($recette['ingredients'] as $ingredient) {
                if($types->$typeachercher($ingredient['id'])) {
                    if(in_array($ingredient['id'], $this->ingredientsPresents)) {
                        $index = array_search($ingredient['id'], $this->ingredientsPresents);
                        if($ingredient['unite'] == $this->ingredientsPresentsInfos[$index]['unite']) {
                            $this->ingredientsPresentsInfos[$index]['quantite'] += $ingredient['quantite'] * $recette['quantite'] * $recette['personnes'];
                        } else {
                            $this->ingredientsPresents[] = $ingredient['id'];
                            $this->ingredientsPresentsInfos[] = [
                                'name' => $ingredient['name'],
                                'unite' => $ingredient['unite'], 
                                'quantite' => $ingredient['quantite']* $recette['quantite'] * $recette['personnes']
                            ];
                        }
                    } else {
                        $this->ingredientsPresents[] = $ingredient['id'];
                        $this->ingredientsPresentsInfos[] = [
                            'name' => $ingredient['name'],
                            'unite' => $ingredient['unite'], 
                            'quantite' => $ingredient['quantite']* $recette['quantite'] * $recette['personnes']
                        ];
                    }
                }
            }
        }

        if($this->ingredientsPresents !== []) {
            return true;
        } else {
            return false;
        }
    }

}

?>
