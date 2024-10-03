<?php

$title = 'Ma liste de courses';
$selected = 'courses';

use App\ConnectionBD;
use App\ListeDeCourses;

session_start();

// Si la liste à été modifiée à partir du récapitulatif, je mets à jour la session
if(isset($_GET['modif']) && $_GET['modif'] == "1") {
    session_unset();
}

// Je rentre les informations envoyées à la page dans la session
if (isset($_POST) && !empty($_POST)) {
    foreach($_POST['personnes'] as $personnes) {
        $_SESSION['personnes'][] = $personnes;
    }
    foreach($_POST['recette'] as $recette) {
        $_SESSION['recette'][] = $recette;
    }
    foreach($_POST['quantite'] as $quantite) {
        $_SESSION['quantite'][] = $quantite;
    }
}

// Je recupère toutes les informations de chaque recette (ingredients, quantités, ...) et les range dans un tableau pour y acceder facilement
if(!empty($_SESSION)) {

    $pdo = ConnectionBD::getPDO();
    $recettesInfos = [];
    $i = 0;

    foreach($_SESSION['recette'] as $recette) {
        $i += 1;
        if ($recette !== "null") {
            $requete = $pdo->prepare('SELECT id, name FROM recette WHERE id = :id');
            $requete->execute(['id' => $recette]);
            $recetteInfo = $requete->fetch();
            $recetteInfo['rang'] = $i-1;
            $recettesInfos[] = $recetteInfo;
        }
    }

    foreach ($recettesInfos as &$recette) {
        $recette['personnes'] = $_SESSION['personnes'][$recette['rang']];
        $recette['quantite'] = $_SESSION['quantite'][$recette['rang']];
        $requete = $pdo->prepare('
        SELECT i.id, i.name, ir.quantite, ir.unite
        FROM ingredient_recette ir 
        JOIN ingredient i ON ir.ingredient_id = i.id
        WHERE ir.recette_id = :id
        ');
        $requete->execute(['id' => $recette['id']]);
        $ingredients = $requete->fetchAll();
        foreach($ingredients as $ingredient) {
            $recette['ingredients'][] = $ingredient;
        }
    }
    unset($recette);

    $listeDeCourses = new ListeDeCourses;

}

// Si une recette à été ajoutée à partir de la page recette, je redirige vers le livre de recette
if(isset($_GET['ajout']) && $_GET['ajout'] == "1") {
    header('Location: ' . '/mon-livre-de-recettes' . '?ajout=1');
    exit();
}

// Je verifie qu'il n'y ait pas que des recettes null
if (!empty($_SESSION)) {
    $test = false;
    foreach($_SESSION['recette'] as $k => $recette) {
        if($recette !== 'null') {
            $test = true;
        }
    }
}

?>

<div class="display-flex justify-content-center margin-top-70">
    <a href="<?= $this->router->generate('choixRecettes') ?>" class="fredoka p-25 noir margin-right-70 titre-menu-courses">Mes recettes</a>

    <div class="display-flex flex-direction-column cursor-default">
        <p class="fredoka p-25 semi-bold noir">Ma liste de courses</p>
        <div class="courses-menu-underline"></div>
    </div>
</div>

<?php // Si la liste est vide, j'affiche la liste est vide ?>
<?php if(empty($_SESSION) || $test == false): ?>
    <div class="display-flex flex-direction-column align-items-center">
        <h3 class="catamaran p-50 noir opacity-20 margin-top-110">Ma liste est vide</h3>
        <a href="<?=$this->router->generate('choixRecettes')?>" class="bouton-valider padding-leftright-30 margin-top-110 display-flex align-items-center justify-content-center">Choisir mes recettes</a>
    </div>

<?php // Sinon, j'affiche la liste des ingredients et le recap ?>
<?php else: ?>
    <div class="display-flex margin-top-60 div-liste-ingredients">
        
        <?php // J'affiche les fruits et légumes ?>
        <?php if($listeDeCourses->getIngredientsPresentsType($recettesInfos, 'isFruitsLegumes')): ?>
            <div class="display-flex flex-direction-column align-items-center margin-leftright-40">
                <div class="display-flex flex-direction-column align-items-center justify-content-center type-ingredient-liste-course">
                    <img src="/img/carotte.svg" alt="">
                    <p class="fredoka p-15 noir">Fruits et légumes</p>
                </div>

                <div class="margin-top-5">
                    <?= $listeDeCourses->getIngredientsType()?>
                </div>
            </div>
        <?php endif ?>

        <?php // J'affiche les protéines ?>
        <?php if($listeDeCourses->getIngredientsPresentsType($recettesInfos, 'isProteines')): ?>
            <div class="display-flex flex-direction-column align-items-center margin-leftright-40">
                <div class="display-flex flex-direction-column align-items-center justify-content-center type-ingredient-liste-course">
                    <img src="/img/poisson.svg" alt="" class="img-poisson-proteines">
                    <p class="fredoka p-15 noir">Protéines</p>
                </div>

                <div class="margin-top-5">
                    <?= $listeDeCourses->getIngredientsType()?>
                </div>
            </div>
        <?php endif ?>

        <?php // J'affiche les produits frais ?>
        <?php if($listeDeCourses->getIngredientsPresentsType($recettesInfos, 'isProduitsFrais')): ?>
            <div class="display-flex flex-direction-column align-items-center margin-leftright-40">
                <div class="display-flex flex-direction-column align-items-center justify-content-center type-ingredient-liste-course">
                    <img src="/img/flocon.svg" alt="">
                    <p class="fredoka p-15 noir">Produits frais</p>
                </div>

                <div class="margin-top-5">
                    <?= $listeDeCourses->getIngredientsType()?>
                </div>
            </div>
        <?php endif ?>

        <?php // J'affiche les féculents ?>
        <?php if($listeDeCourses->getIngredientsPresentsType($recettesInfos, 'isFeculents')): ?>
            <div class="display-flex flex-direction-column align-items-center margin-leftright-40">
                <div class="display-flex flex-direction-column align-items-center justify-content-center type-ingredient-liste-course">
                    <img src="/img/cereale.svg" alt="">
                    <p class="fredoka p-15 noir">Féculents</p>
                </div>

                <div class="margin-top-5">
                    <?= $listeDeCourses->getIngredientsType()?>
                </div>
            </div>
        <?php endif ?>

        <?php // J'affiche les autres ingredients ?>
        <?php if($listeDeCourses->getIngredientsPresentsType($recettesInfos, 'isAutres')): ?>
            <div class="display-flex flex-direction-column align-items-center margin-leftright-40">
                <div class="display-flex flex-direction-column align-items-center justify-content-center type-ingredient-liste-course">
                    <img src="/img/autre.svg" alt="">
                    <p class="fredoka p-15 noir">Autres</p>
                </div>

                <div class="margin-top-5">
                    <?= $listeDeCourses->getIngredientsType()?>
                </div>
            </div>
        <?php endif ?>
    
    </div>
    
    <div class="display-flex flex-direction-column align-items-center margin-top-70">
        <form action="<?=$this->router->generate('listeCourses')?>?modif=1" method="post">
            <button type="submit" class="bouton-secondaire">Réinitialiser la liste</button>
        </form>
        <form action="<?=$this->router->generate('listeCourses')?>?modif=1" method="post" class="js-form-recap hidden display-flex flex-direction-column align-items-center margin-top-40 div-recap-liste-courses background-color-beige-light">
            <div class="text-align-center catamaran p-25 noir">Récapitulatif des recettes</div>
            <table class="margin-top-40">
                <thead>
                    <tr>
                        <th class="table-recap-liste-colonne-1"><div class="catamaran p-20 noir table-recap-liste-titre">Recette</div></th>
                        <th class="table-recap-liste-colonne-2"><div class="catamaran p-20 noir table-recap-liste-titre-center">Personne(s)</div></th>
                        <th class="table-recap-liste-colonne-3"><div class="catamaran p-20 noir table-recap-liste-titre-center">Quantité</div></th>
                    </tr>
                </thead>
                <tbody class="js-table-body">
                    <?php $color = 'none'?>
                    <?php foreach ($recettesInfos as $recette): ?>
                        <?php 
                            if($color == 'none') {
                                $color = 'beige';
                            } else if($color == 'beige') {
                                $color = 'none';
                            }
                        ?>
                        <tr <?php if($color == 'beige'): ?>class="background-color-beige"<?php endif ?>>
                            <td class="table-recap-liste-colonne-1"><div class="catamaran noir p-20"><?=$recette['name']?></div></td>
                            <td class="table-recap-liste-colonne-2">
                                <div class="display-flex align-items-center justify-content-space-between recap-liste-bouton-quantite">
                                    <div class="<?php if($recette['personnes'] > 1):?>cursor-pointer<?php elseif($recette['personnes'] == 1):?>cursor-default<?php endif?> display-flex align-items-center js-personnes-bouton-moins">
                                        <img src="/img/moinsPetitNoir.svg" alt="" class="moins-petit-noir <?php if($recette['personnes'] == 1):?> opacity-20<?php endif?> js-img-personnes-bouton-moins">
                                    </div>
                                    <div class="catamaran p-20 noir js-personnes-quantite"><?=$recette['personnes']?></div>
                                    <div class="cursor-pointer display-flex align-items-center js-personnes-bouton-plus">
                                        <img src="/img/plusPetitNoir.svg" alt="" class="plus-petit-noir">
                                    </div>
                                </div>
                                <input type="number" name="personnes[]" min="1" value="<?=$recette['personnes']?>" class="hidden js-input-personnes">
                            </td>

                            <td class="table-recap-liste-colonne-3">
                                <div class="display-flex align-items-center justify-content-space-between recap-liste-bouton-quantite">
                                    <div class="<?php if($recette['quantite'] > 1):?>cursor-pointer<?php elseif($recette['quantite'] == 1):?>cursor-default<?php endif?> display-flex align-items-center js-quantite-bouton-moins">
                                        <img src="/img/moinsPetitNoir.svg" alt="" class="moins-petit-noir <?php if($recette['quantite'] == 1):?> opacity-20<?php endif?> js-img-quantite-bouton-moins">
                                    </div>
                                    <div class="catamaran p-20 noir js-quantite-quantite"><?=$recette['quantite']?></div>
                                    <div class="cursor-pointer display-flex align-items-center js-quantite-bouton-plus">
                                        <img src="/img/plusPetitNoir.svg" alt="" class="plus-petit-noir">
                                    </div>
                                </div>
                                <input type="number" name="quantite[]" min="1" value="<?=$recette['quantite']?>" class="hidden js-input-quantite">
                            </td>
                            <td class="padding-0"><input type="text" name="recette[]" value="<?=$recette['id']?>" class="hidden"></td>
                            <td class="table-recap-liste-colonne-4"><div class="js-table-row-supp cursor-pointer img-poubelle-poss-hover margin-left-15"></div></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>

            <button type="submit" class="bouton-valider margin-top-40">Valider</button>
        </form>
        <div class="js-bouton-afficher-recapitulatif bouton-valider display-flex align-items-center justify-content-center margin-top-20">Voir le recapitulatif</div>
    </div>

    <form action="<?=$this->router->generate('listeCourses')?>?modif=1" method="post" class="js-form-recap hidden display-flex flex-direction-column align-items-center">
        <table>
            <thead>
                <tr>
                    <th>Recette</th>
                    <th>Nb personnes</th>
                    <th>Quantité</th>
                </tr>
            </thead>
            <tbody class="js-table-body">
                <?php $color = 'none'?>
                <?php foreach ($recettesInfos as $recette): ?>
                    <?php 
                        if($color == 'none') {
                            $color = 'beige';
                        } else if($color == 'beige') {
                            $color = 'none';
                        }
                    ?>
                    <tr <?php if($color == 'beige'): ?>class="background-color-beige"<?php endif ?>>
                        <td class="padding-0"><div class="catamaran noir p-20"><?=$recette['name']?></div></td>
                        <td class="padding-0">
                            <div class="display-flex align-items-center justify-content-space-between padding-leftright-16 recap-liste-bouton-quantite">
                                <div class="<?php if($recette['personnes'] > 1):?>cursor-pointer<?php elseif($recette['personnes'] == 1):?>cursor-default<?php endif?> display-flex align-items-center js-personnes-bouton-moins">
                                    <img src="/img/moinsPetitNoir.svg" alt="" class="moins-petit-noir <?php if($recette['personnes'] == 1):?> opacity-20<?php endif?> js-img-personnes-bouton-moins">
                                </div>
                                <div class="catamaran p-20 noir js-personnes-quantite"><?=$recette['personnes']?></div>
                                <div class="cursor-pointer display-flex align-items-center js-personnes-bouton-plus">
                                    <img src="/img/plusPetitNoir.svg" alt="" class="plus-petit-noir">
                                </div>
                            </div>
                            <input type="number" name="personnes[]" min="1" value="<?=$recette['personnes']?>" class="hidden js-input-personnes">
                        </td>

                        <td class="padding-0">
                            <div class="display-flex align-items-center justify-content-space-between padding-leftright-16 recap-liste-bouton-quantite">
                                <div class="<?php if($recette['quantite'] > 1):?>cursor-pointer<?php elseif($recette['quantite'] == 1):?>cursor-default<?php endif?> display-flex align-items-center js-quantite-bouton-moins">
                                    <img src="/img/moinsPetitNoir.svg" alt="" class="moins-petit-noir <?php if($recette['quantite'] == 1):?> opacity-20<?php endif?> js-img-quantite-bouton-moins">
                                </div>
                                <div class="catamaran p-20 noir js-quantite-quantite"><?=$recette['quantite']?></div>
                                <div class="cursor-pointer display-flex align-items-center js-quantite-bouton-plus">
                                    <img src="/img/plusPetitNoir.svg" alt="" class="plus-petit-noir">
                                </div>
                            </div>
                            <input type="number" name="quantite[]" min="1" value="<?=$recette['quantite']?>" class="hidden js-input-quantite">
                        </td>
                        <td class="padding-0"><input type="text" name="recette[]" value="<?=$recette['id']?>" class="hidden"></td>
                        <td class="padding-0"><div class="js-table-row-supp cursor-pointer img-poubelle-poss-hover margin-left-15"></div></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>

        <button type="submit" class="bouton-valider">Valider</button>
    </form>
    
<?php endif ?>

<script>

    const tableBody = document.querySelector('.js-table-body');
    const tableRowSup = document.querySelectorAll('.js-table-row-supp');

    tableRowSup.forEach((sup) => {
        sup.addEventListener('click', ()=> {
            const rowToRemove = sup.closest('tr');
            tableBody.removeChild(rowToRemove);
        })
    })

    const boutonAfficherRecap = document.querySelector('.js-bouton-afficher-recapitulatif');
    const formRecap = document.querySelector('.js-form-recap');

    boutonAfficherRecap.addEventListener('click', ()=> {
        if (formRecap.classList.contains('hidden')) {
            formRecap.classList.remove('hidden');
            boutonAfficherRecap.innerHTML = 'Masquer le récapitulatif';
            boutonAfficherRecap.classList.remove('bouton-valider');
            boutonAfficherRecap.classList.add('bouton-secondaire');
        } else {
            formRecap.classList.add('hidden');
            boutonAfficherRecap.innerHTML = 'Voir le récapitulatif';
            boutonAfficherRecap.classList.remove('bouton-secondaire');
            boutonAfficherRecap.classList.add('bouton-valider');
        }
    })

    //
    // Personnes
    //
        function handlePersonnesChangeMoins(boutonMoins) {
            boutonMoins.addEventListener('click', ()=>{
                let ligne = boutonMoins.closest('tr');
                let personnesQuantite = ligne.querySelector('.js-personnes-quantite');
                let imgPersonnesBoutonMoins = ligne.querySelector('.js-img-personnes-bouton-moins');
                let inputPersonnes = ligne.querySelector('.js-input-personnes');

                if(personnesQuantite.innerHTML > 1) {
                    personnesQuantite.innerHTML = (personnesQuantite.innerHTML * 1) - 1;
                    inputPersonnes.value = personnesQuantite.innerHTML;
                }
                if (personnesQuantite.innerHTML == 1) {
                    imgPersonnesBoutonMoins.classList.add('opacity-20');
                    boutonMoins.classList.remove('cursor-pointer');
                    boutonMoins.classList.add('cursor-default');
                }
            })
        }

        function handlePersonnesChangePlus(boutonPlus) {
            boutonPlus.addEventListener('click', ()=>{
                let ligne = boutonPlus.closest('tr');
                let personnesQuantite = ligne.querySelector('.js-personnes-quantite');
                let boutonMoins = ligne.querySelector('.js-personnes-bouton-moins')
                let imgPersonnesBoutonMoins = ligne.querySelector('.js-img-personnes-bouton-moins');
                let inputPersonnes = ligne.querySelector('.js-input-personnes');

                if (personnesQuantite.innerHTML == 1) {
                    personnesQuantite.innerHTML = (personnesQuantite.innerHTML * 1) + 1;
                    inputPersonnes.value = personnesQuantite.innerHTML;
                    imgPersonnesBoutonMoins.classList.remove('opacity-20');
                    boutonMoins.classList.remove('cursor-default');
                    boutonMoins.classList.add('cursor-pointer');
                } else {
                    personnesQuantite.innerHTML = (personnesQuantite.innerHTML * 1) + 1;
                    inputPersonnes.value = personnesQuantite.innerHTML;
                }
            })
        }

        // Je permets la mise à jour du changement de nombre de personnes sur les 2 lignes du tableau présentent de base
        let personnesBoutonMoins = Array.from(document.querySelectorAll('.js-personnes-bouton-moins'));
        let personnesBoutonPlus = Array.from(document.querySelectorAll('.js-personnes-bouton-plus'));
        personnesBoutonMoins.forEach((boutonMoins)=>{
            handlePersonnesChangeMoins(boutonMoins);
        })
        personnesBoutonPlus.forEach((boutonPlus)=>{
            handlePersonnesChangePlus(boutonPlus);
        })



    //
    // Quantité
    //
        function handleQuantiteChangeMoins(boutonMoins) {
            boutonMoins.addEventListener('click', ()=>{
                let ligne = boutonMoins.closest('tr');
                let quantiteQuantite = ligne.querySelector('.js-quantite-quantite');
                let imgQuantiteBoutonMoins = ligne.querySelector('.js-img-quantite-bouton-moins');
                let inputQuantite = ligne.querySelector('.js-input-quantite');

                if(quantiteQuantite.innerHTML == 2) {
                    quantiteQuantite.innerHTML = (quantiteQuantite.innerHTML * 1) - 1;
                    inputQuantite.value = quantiteQuantite.innerHTML;
                    imgQuantiteBoutonMoins.classList.add('opacity-20');
                    boutonMoins.classList.remove('cursor-pointer');
                    boutonMoins.classList.add('cursor-default');
                }
                if (quantiteQuantite.innerHTML > 2) {
                    quantiteQuantite.innerHTML = (quantiteQuantite.innerHTML * 1) - 1;
                    inputQuantite.value = quantiteQuantite.innerHTML;
                }
            })
        }

        function handleQuantiteChangePlus(boutonPlus) {
            boutonPlus.addEventListener('click', ()=>{
                let ligne = boutonPlus.closest('tr');
                let quantiteQuantite = ligne.querySelector('.js-quantite-quantite');
                let imgQuantiteBoutonMoins = ligne.querySelector('.js-img-quantite-bouton-moins');
                let inputQuantite = ligne.querySelector('.js-input-quantite');
                let boutonMoins = ligne.querySelector('.js-quantite-bouton-moins');

                if (quantiteQuantite.innerHTML == 1) {
                    quantiteQuantite.innerHTML = (quantiteQuantite.innerHTML * 1) + 1;
                    inputQuantite.value = quantiteQuantite.innerHTML;
                    imgQuantiteBoutonMoins.classList.remove('opacity-20');
                    boutonMoins.classList.remove('cursor-default');
                    boutonMoins.classList.add('cursor-pointer');
                } else {
                    quantiteQuantite.innerHTML = (quantiteQuantite.innerHTML * 1) + 1;
                    inputQuantite.value = quantiteQuantite.innerHTML;
                }
            })
        }

        // Je permets la mise à jour du changement de quantite sur les 2 lignes du tableau présentent de base
        let quantiteBoutonMoins = Array.from(document.querySelectorAll('.js-quantite-bouton-moins'));
        let quantiteBoutonPlus = Array.from(document.querySelectorAll('.js-quantite-bouton-plus'));
        quantiteBoutonMoins.forEach((boutonMoins)=>{
            handleQuantiteChangeMoins(boutonMoins);
        })
        quantiteBoutonPlus.forEach((boutonPlus)=>{
            handleQuantiteChangePlus(boutonPlus);
        })




</script>