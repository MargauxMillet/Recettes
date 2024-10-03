<?php

$selected = 'none';

use App\ConnectionBD;

$pdo = ConnectionBD::getPDO();

// Recuperation des données de la recette
$requete = $pdo->prepare("SELECT * FROM recette WHERE slug = :slug");
$requete->execute(['slug' => $params['slug']]);
$recette = $requete->fetch();
$title = 'Recette: ' . $recette['name'];

// Si il a été demandé de supprimer la recette, je la supprime de la base de données et renvoie vers le livre de recettes
if(isset($_GET['sup']) && $_GET['sup'] == 1) {
    $requete = $pdo->prepare('
        DELETE FROM ingredient_recette
        WHERE recette_id = :id
    ');
    $requete->execute(['id' => $recette['id']]);

    $requete = $pdo->prepare('
        DELETE FROM categorie_recette
        WHERE recette_id = :id
    ');
    $requete->execute(['id' => $recette['id']]);

    $requete = $pdo->prepare('
        DELETE FROM typeCat_recette
        WHERE recette_id = :id
    ');
    $requete->execute(['id' => $recette['id']]);

    $requete = $pdo->prepare('
        DELETE FROM recette
        WHERE id = :id
    ');
    $requete->execute(['id' => $recette['id']]);

    header('Location: ' . '/mon-livre-de-recettes' . '?sup=1');
    exit();
}

// Recuperation des données des types de la recette
$requete = $pdo->prepare('
SELECT t.id, t.name 
FROM typeCat_recette tr 
JOIN typeCat t ON tr.type_id = t.id
WHERE tr.recette_id = :id
');
$requete->execute(['id' => $recette['id']]);
$typeCats = $requete->fetchAll();

// Recuperation des données des categories de la recette
$requete = $pdo->prepare('
SELECT c.id, c.name 
FROM categorie_recette cr 
JOIN categorie c ON cr.categorie_id = c.id
WHERE cr.recette_id = :id
');
$requete->execute(['id' => $recette['id']]);
$categories = $requete->fetchAll();

// Recuperation des données des ingredients de la recette
$requete = $pdo->prepare('
SELECT i.id, i.name, ir.quantite, ir.unite
FROM ingredient_recette ir 
JOIN ingredient i ON ir.ingredient_id = i.id
WHERE ir.recette_id = :id
');
$requete->execute(['id' => $recette['id']]);
$ingredients = $requete->fetchAll();

$nombreIngredients = count($ingredients);
$nombrePersonnesDefault = 2;

function roundUpToHalf($number) {
    return ceil($number * 2) / 2;
}

?>

<div class="width-80p">
    <a href="<?= $this->router->generate('livreRecettes') ?>" class="display-flex align-items-center recette-lien-retour margin-top-35">
        <div class="recette-lien-retour-fleche"></div>
        <p class="catamaran p-20 noir recette-lien-retour-text">Retour</p>
    </a>
</div>

<?php 

// Lorsque je modifie la recette, je suis redirigé vers cette page avec un message de succès ?>
<?php if (isset($_GET['modif']) && $_GET['modif'] == 1): ?>
    <div class="page-recette-alerte-succes">
        <h2 class="beige catamaran p-25 bold text-align-center cursor-default">La recette a bien été modifiée</h2>
    </div>
<?php endif ?>

<div class="display-flex flex-direction-column align-items-center recette-encadrement-infos">
    <h1 class="recette-title cursor-default"><?= $recette['name'] ?></h1>

    <?php //  J'affiche toutes les categories de la recette (Végé, Au four, ...)?>
    <?php if($categories !== []): ?>
        <div class="display-flex align-items-center justify-content-center margin-top-50">
            <?php foreach($categories as $categorie): ?>
                <?php if($categorie['name'] == 'Salade'):?>
                    <img src="/img/salade.svg" alt="" class="padding-leftright-10">
                <?php endif ?>
                <?php if($categorie['name'] == 'Végétarien'):?>
                    <img src="/img/vegetarien.svg" alt="" class="padding-leftright-10">
                <?php endif ?>
                <?php if($categorie['name'] == 'Au four'):?>
                    <img src="/img/four.svg" alt="" class="padding-leftright-10">
                <?php endif ?>
                <?php if($categorie['name'] == 'Rapide'):?>
                    <img src="/img/rapide.svg" alt="" class="padding-leftright-10">
                <?php endif ?>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <div class="display-flex flex-direction-column align-items-center margin-top-50 cursor-default">
        <p class="catamaran p-25 semi-bold noir">Préparation</p>
        <p class="catamaran p-20 noir margin-top-5"><?= $recette['duration'] ?> minutes</p>
    </div>

    <div class="display-flex flex-direction-column align-items-center margin-top-20 cursor-default">
        <p class="catamaran p-25 semi-bold noir">Ingrédients</p>
        <div class="display-flex margin-top-5">
            <p class="catamaran p-20 noir padding-right-10">Pour</p>
            <div class="js-boutton-moins catamaran p-20 noir cursor-pointer page-recette-personnes-bouton-moins">-</div>
            <p class="js-nombre-personnes catamaran p-20 noir"><?= $nombrePersonnesDefault ?></p>
            <div class="js-boutton-plus catamaran p-20 noir cursor-pointer page-recette-personnes-bouton-plus">+</div>
            <p class="catamaran p-20 noir padding-left-10">personne(s)</p>
        </div>
        <li class="margin-top-15">
            <?php // J'affiche tous les ingredients de la recette avec leurs quantités ?>
            <div class="page-recette-affichage-ingredient-grille">
                <?php foreach($ingredients as $ingredient): ?>
                    <?php
                        // Je prépare les conversions des quantités dans d'autres unités
                        if($ingredient['unite'] == 'cL') {
                            $nouvelleUnite = 'L';
                            $nouvelleQuantite = roundUpToHalf($ingredient['quantite'] * $nombrePersonnesDefault) /100;
                        } elseif($ingredient['unite'] == 'g') {
                            $nouvelleUnite = 'kg';
                            $nouvelleQuantite = roundUpToHalf($ingredient['quantite'] * $nombrePersonnesDefault) /1000;
                        } else {
                            $nouvelleUnite = $ingredient['unite'];
                            $nouvelleQuantite = roundUpToHalf($ingredient['quantite'] * $nombrePersonnesDefault);
                        }

                    ?>
                    <ul class="margin-top-5">
                        <p class="catamaran p-20 noir">
                            <?php // Si les quantités sont assez petites cette partie s'affichera ?>
                            <?php if($ingredient['quantite'] !== null && $ingredient['quantite'] !== ''): ?>
                                <span class="js-ingredient-quantite-base catamaran p-20 noir">
                                    <span class="js-ingredient-quantite-dynamique catamaran p-20 noir"><?= roundUpToHalf($ingredient['quantite'] * $nombrePersonnesDefault) ?></span> 
                                    <?= $ingredient['unite'] ?>
                                </span>
                            <?php endif ?>
                            <?php // Sinon ce sera celle-ci qui s'affichera avec des unités de mesure plus grande ?>
                            <?php if(($ingredient['quantite'] !== null && $ingredient['quantite'] !== '')): ?>
                                <span class="js-ingredient-quantite-autre catamaran p-20 noir">
                                    <span class="js-ingredient-quantite-dynamique-autre catamaran p-20 noir"><?= $nouvelleQuantite ?></span> 
                                    <?= $nouvelleUnite ?>
                                </span>
                            <?php endif ?>

                            <?php if($ingredient['unite'] !== null && $ingredient['unite'] !== ''): ?> de <?php endif ?>
                            <?= ' ' . $ingredient['name'] ?><span class="js-ingredient-s hidden catamaran p-20 noir">s</span>
                        </p>
                        
                    </ul>
                <?php endforeach ?>
            </div>
        </li>
    </div>
</div>

<div class="background-color-beige margin-top-70 page-recette-div-recette-contenu cursor-default">
    <h4 class="catamaran p-30 bold noir text-align-center">RECETTE</h4>
    <p class="margin-top-40 catamaran p-20 noir"><?= $recette['content'] ?></p>
</div>

<div class="display-flex flex-direction-column align-items-center">

    <a href="<?= $this->router->generate('edit', ['slug' => $recette['slug']]) ?>" class="catamaran p-20 orange">
        <button class="bouton-secondaire margin-top-70">Modifier la recette</button>
    </a>

    <a href="<?= $_SERVER['REQUEST_URI'] . '?sup=1' ?>" class="js-bouton-supprimer catamaran p-20 orange">
        <button class="bouton-secondaire margin-top-20">Supprimer la recette</button>
    </a>

    <button class="js-bouton-ajouter bouton-valider margin-top-20">Ajouter à la liste de courses</button>

    <form action="<?= $this->router->generate('listeCourses') . '?ajout=1' ?>" method="post" class="js-form-ajouter hidden margin-top-20 background-color-beige-light div-page-recette-ajouter-liste">
        <h5 class="text-align-center catamaran p-25 noir"><?=$recette['name']?></h5>
        <div class="display-flex justify-content-center align-items-center margin-top-50">
            <img src="/img/iconePersonnes.svg" alt="">
            <div class="display-flex align-items-center margin-left-40">
                <img src="/img/moinsPetitNoir.svg" alt="" class="js-ajouter-bouton-moins cursor-pointer">
                <p class="catamaran p-30 noir margin-leftright-20 js-ajouter-text">2</p>
                <img src="/img/plusPetitNoir.svg" alt="" class="js-ajouter-bouton-plus cursor-pointer">
            </div>
        </div>
        <input type="number" name="personnes[]" min="1" value="2" class="hidden js-ajouter-value">
        <input type="number" name="quantite[]" min="1" value="1" class="hidden">
        <input type="text" name="recette[]" value="<?=$recette['id']?>" class="hidden">
        <button type="submit" class="bouton-valider margin-top-50">Ajouter à la liste de courses</button>
    </form>

</div>


<script>

    const bouttonPersonnesMoins = document.querySelector('.js-boutton-moins');
    const bouttonPersonnesPlus = document.querySelector('.js-boutton-plus');
    const nombrePersonnes = document.querySelector('.js-nombre-personnes');
    const ingredientsQuantitesDynamiques = Array.from(document.querySelectorAll('.js-ingredient-quantite-dynamique'));
    const ingredientsQuantitesDynamiquesAutres = Array.from(document.querySelectorAll('.js-ingredient-quantite-dynamique-autre'));
    const ingredientsQuantitesBase = Array.from(document.querySelectorAll('.js-ingredient-quantite-base'));
    const ingredientsQuantitesAutre = Array.from(document.querySelectorAll('.js-ingredient-quantite-autre'));
    const ingredientsS = Array.from(document.querySelectorAll('.js-ingredient-s'));
    const bouttonAjouter = document.querySelector('.js-bouton-ajouter');
    const formAjouter = document.querySelector('.js-form-ajouter');

    function roundUpToHalfjs(number) {
        return Math.ceil(number * 2) / 2;
    }

    bouttonAjouter.addEventListener('click', ()=> {
        if(formAjouter.classList.contains('hidden')) {
            formAjouter.classList.remove('hidden')
            bouttonAjouter.classList.add('hidden')
        }
    })
        
    function MiseAJourDonnees() {
        if (nombrePersonnes.innerHTML == 1) {
            bouttonPersonnesMoins.classList.add('opacity-20');
            bouttonPersonnesMoins.classList.remove('cursor-pointer');
            bouttonPersonnesMoins.classList.add('cursor-default');
        }
        if (nombrePersonnes.innerHTML > 1) {
            bouttonPersonnesMoins.classList.remove('opacity-20');
            bouttonPersonnesMoins.classList.remove('cursor-default');
            bouttonPersonnesMoins.classList.add('cursor-pointer');
        }
        // Les quantités sont mises à jour
        let unites = [];
        let nouvellesQuantites = [];
        let nouvellesQuantiteskg = [];
        let nouvellesQuantitesL = [];
        let s = [];
         
        <?php foreach($ingredients as $key => $ingredient): ?>
            <?php if($ingredient['quantite'] !== null && $ingredient['quantite'] !== ''): ?>
                ingredientQuantite = roundUpToHalfjs((<?= $ingredient['quantite']?>) * nombrePersonnes.innerHTML)
                ingredientUnite = '<?=$ingredient['unite']?>'
                unites.push(ingredientUnite);
                nouvellesQuantites.push(ingredientQuantite);
                nouvellesQuantiteskg.push(ingredientQuantite / 1000);
                nouvellesQuantitesL.push(ingredientQuantite / 100);
                <?php if($ingredient['unite'] == null): ?>
                    if(ingredientQuantite > 1) {
                        s.push(true);
                    } else {
                        s.push(false);
                    }
                <?php else: ?>
                    s.push(false);
                <?php endif ?>
            <?php endif ?>
        <?php endforeach ?>
        console.log(unites)
        for (i=0; i < ingredientsQuantitesDynamiques.length; i++) {
            if (unites[i] == 'g' && nouvellesQuantites[i] >= 1000) {
                ingredientsQuantitesDynamiquesAutres[i].innerHTML = nouvellesQuantiteskg[i];
                ingredientsQuantitesAutre[i].classList.remove('hidden');
                ingredientsQuantitesBase[i].classList.add('hidden');
            } else if(unites[i]== 'cL' && nouvellesQuantites[i] >= 100) {
                ingredientsQuantitesDynamiquesAutres[i].innerHTML = nouvellesQuantitesL[i];
                ingredientsQuantitesAutre[i].classList.remove('hidden');
                ingredientsQuantitesBase[i].classList.add('hidden');
            } else {
                ingredientsQuantitesDynamiques[i].innerHTML = nouvellesQuantites[i];
                ingredientsQuantitesAutre[i].classList.add('hidden');
                ingredientsQuantitesBase[i].classList.remove('hidden');
            }
            if(s[i] === false) {
                ingredientsS[i].classList.add('hidden');
            } else {
                ingredientsS[i].classList.remove('hidden');
            }
        }
        
    }

    MiseAJourDonnees();

    bouttonPersonnesMoins.addEventListener('click', ()=> {
        // Le nombre de personnes diminue
        if (nombrePersonnes.innerHTML > 1) {
            nombrePersonnes.innerHTML = nombrePersonnes.innerHTML * 1 - 1; 
        }
       
        MiseAJourDonnees();
    })

    bouttonPersonnesPlus.addEventListener('click', ()=> {
        // Le nombre de personnes augmente
        nombrePersonnes.innerHTML = nombrePersonnes.innerHTML * 1 + 1;
        
        MiseAJourDonnees();
    })

    const boutonSupprimer = document.querySelector('.js-bouton-supprimer');

    boutonSupprimer.addEventListener('click', ()=>{
        event.preventDefault();
        let alert = confirm("Voulez-vous vraiment supprimer cette recette ?");
        if (alert) {
            window.location.href = boutonSupprimer.href;
        }
    })

    const ajouterBoutonMoins = document.querySelector('.js-ajouter-bouton-moins');
    const ajouterBoutonPlus = document.querySelector('.js-ajouter-bouton-plus');
    const ajouterText = document.querySelector('.js-ajouter-text');
    const ajouterValue = document.querySelector('.js-ajouter-value');

    ajouterBoutonMoins.addEventListener('click', ()=> {
        if (ajouterText.innerHTML == 2) {
            ajouterBoutonMoins.classList.add('opacity-20');
            ajouterBoutonMoins.classList.remove('cursor-pointer');
            ajouterBoutonMoins.classList.add('cursor-default');
            ajouterText.innerHTML = (ajouterText.innerHTML * 1) - 1;
            ajouterValue.value = ajouterText.innerHTML;
        } else if(ajouterText.innerHTML > 2) {
            ajouterText.innerHTML = (ajouterText.innerHTML * 1) - 1;
            ajouterValue.value = ajouterText.innerHTML;
        }
        
    })

    ajouterBoutonPlus.addEventListener('click', ()=> {
        if (ajouterText.innerHTML == 1) {
            ajouterBoutonMoins.classList.remove('opacity-20');
            ajouterBoutonMoins.classList.remove('cursor-default');
            ajouterBoutonMoins.classList.add('cursor-pointer');
        }
        ajouterText.innerHTML = (ajouterText.innerHTML * 1) + 1;
        ajouterValue.value = ajouterText.innerHTML;
    })
  
</script>