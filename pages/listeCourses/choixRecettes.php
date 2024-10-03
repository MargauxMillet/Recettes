<?php

$title = 'Le choix des recettes'; 
$selected = 'courses';

use App\ConnectionBD;

$pdo = ConnectionBD::getPDO();

// Je récupère toutes les recettes
$requete = $pdo->query('SELECT * FROM recette ORDER BY name ASC');
$recettes = $requete->fetchAll();

?>

<div class="display-flex justify-content-center margin-top-70">
    <div class="display-flex flex-direction-column margin-right-70 cursor-default">
        <p class="fredoka p-25 semi-bold noir">Mes recettes</p>
        <div class="courses-menu-underline"></div>
    </div>
    <a href="<?= $this->router->generate('listeCourses') ?>" class="fredoka p-25 noir titre-menu-courses">Ma liste de courses</a>
</div>


<form action="<?= $this->router->generate('listeCourses') ?>" method="post" class="margin-top-80 display-flex flex-direction-column align-items-center">

    <div class="display-flex flex-direction-column align-items-start">

        <table class="table-choix-recette">
            <thead>
                <tr>
                    <th><div class="margin-left-5"><img src="/img/iconePersonnes.svg" alt=""></div></th>
                    <th><div class="margin-left-35"><img src="/img/iconeRecette.svg" alt=""></div></th>
                    <th><div class="margin-left-5"><img src="/img/iconeQuantite.svg" alt=""></div></th>
                </tr>
            </thead>
            <tbody class="js-table-body">
                <?php for($i=0; $i<2; $i++):?>
                    <tr>
                        <td>
                            <div class="margin-top-15">
                                <div class="display-flex align-items-center justify-content-space-between padding-leftright-16 choix-recette-bouton-quantite">
                                    <div class="cursor-pointer display-flex align-items-center js-personnes-bouton-moins">
                                        <img src="/img/moinsPetitNoir.svg" alt="" class="moins-petit-noir js-img-personnes-bouton-moins">
                                    </div>
                                    <div class="catamaran p-25 noir js-personnes-quantite">2</div>
                                    <div class="cursor-pointer display-flex align-items-center js-personnes-bouton-plus">
                                        <img src="/img/plusPetitNoir.svg" alt="" class="plus-petit-noir">
                                    </div>
                                </div>
                                <input type="number" name="personnes[]" min="1" value="2" class="hidden js-input-personnes">
                            </div>
                        </td>
                        <td class="position-relative">
                            <div class="margin-top-15">
                                <div class="margin-leftright-30 js-select-recette-div">
                                    <div class="js-bouton-choix-recettes js-target-bouton-choix-recettes choix-recette-bouton-recette display-flex align-items-center justify-content-space-between padding-leftright-16 cursor-pointer">
                                        <p class="catamaran p-20 opacity-60 noir js-target-bouton-choix-recettes js-bouton-choix-recettes-text">Choisir ma recette</p>
                                        <div class="choix-recette-fleche opacity-60 js-target-bouton-choix-recettes"></div>
                                    </div>
                                    <div class="div-choix-recettes-options js-div-choix-recettes-options hidden">
                                        <?php foreach ($recettes as $recette): ?>
                                            <div class="js-choix-recettes-option js-target-choix-recettes-option-div choix-recette-bouton-recette-option display-flex align-items-center justify-content-space-between padding-leftright-16">
                                                <p class="catamaran p-20 noir js-choix-recettes-option-text js-target-choix-recettes-option-p"><?=$recette['name']?></p>
                                                <p class="hidden js-choix-recettes-option-id"><?=$recette['id']?></p>
                                            </div>
                                        <?php endforeach ?>
                                    </div>
                                    <select name="recette[]" class="hidden">
                                        <option value="null" class="js-select-recette"></option>
                                    </select>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="margin-top-15">
                                <div class="display-flex align-items-center justify-content-space-between padding-leftright-16 choix-recette-bouton-quantite">
                                    <div class="cursor-default display-flex align-items-center js-quantite-bouton-moins">
                                        <img src="/img/moinsPetitNoir.svg" alt="" class="moins-petit-noir opacity-20 js-img-quantite-bouton-moins">
                                    </div>
                                    <div class="catamaran p-25 noir js-quantite-quantite">1</div>
                                    <div class="cursor-pointer display-flex align-items-center js-quantite-bouton-plus">
                                        <img src="/img/plusPetitNoir.svg" alt="" class="plus-petit-noir">
                                    </div>
                                </div>
                                <input type="number" name="quantite[]" min="1" value="1" class="hidden js-input-quantite">
                            </div>
                        </td>
                        <td>
                            <div class="margin-top-15">
                                <div class="js-table-row-supp cursor-pointer img-poubelle-poss-hover margin-left-15"></div>
                            </div>
                        </td>
                    </tr>
                <?php endfor ?>
            </tbody>
        </table>

        <div class="display-flex margin-top-20">
            <div class="choix-recette-bouton-moins-poss-hover js-bouton-moins margin-right-15 cursor-pointer"></div>
            <div class="choix-recette-bouton-plus js-bouton-plus cursor-pointer"></div>
        </div>
    </div>

    <div class="margin-top-70">
        <button type="submit" class="margin-center bouton-valider">Générer ma liste</button>
    </div>
</form>

<script>
        
    const tableBody = document.querySelector('.js-table-body');

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

    
    //
    // ChoixRecette
    //
        // Je permets l'ouverture et la fermeture de la div avec les options de recettes
        const selectsRecettes = Array.from(document.querySelectorAll('.js-bouton-choix-recettes'));
        selectsRecettes.forEach((select)=>{
            select.addEventListener('click', ()=>{
                if(select.classList.contains('choix-recette-bouton-recette-ouvert')) {
                    select.nextElementSibling.classList.add('hidden');
                    select.classList.remove('choix-recette-bouton-recette-ouvert');
                } else {
                    Array.from(document.querySelectorAll('.js-bouton-choix-recettes')).forEach((selectTous)=>{
                        selectTous.classList.remove('choix-recette-bouton-recette-ouvert');
                        selectTous.nextElementSibling.classList.add('hidden');
                    })
                    select.nextElementSibling.classList.remove('hidden');
                    select.classList.add('choix-recette-bouton-recette-ouvert');
                    const recetteAfficheSelect = select.querySelector('.js-bouton-choix-recettes-text');
                    const recettesOptionsSelectP = Array.from(document.querySelectorAll('.js-target-choix-recettes-option-p'));
                    recettesOptionsSelectP.forEach((p)=>{
                        p.closest('div').classList.remove('choix-recette-bouton-recette-option-selectionne');
                        if(recetteAfficheSelect.innerHTML == p.innerHTML) {
                            p.closest('div').classList.add('choix-recette-bouton-recette-option-selectionne');
                        }
                    })
                }
            })
        })

        // Si l'on clique sur une option de recette, la div se referme et la recette est passée en html
        const optionsRecettesItem = Array.from(document.querySelectorAll('.js-choix-recettes-option'));
        optionsRecettesItem.forEach((item)=>{
            item.addEventListener('click', ()=>{
                const optionChoixRecettesText = item.children[0];
                const optionChoixRecettesId = item.children[1];
                const row = item.closest('tr');
                const selectChoixRecettes= row.querySelector('.js-bouton-choix-recettes');
                const selectChoixRecettesText = row.querySelector('.js-bouton-choix-recettes-text');
                const divOptions = row.querySelector('.js-div-choix-recettes-options');
                const selectRecetteValue = row.querySelector('.js-select-recette');

                selectChoixRecettesText.innerHTML = optionChoixRecettesText.innerHTML;
                selectRecetteValue.value = optionChoixRecettesId.innerHTML;
                selectChoixRecettesText.classList.remove('opacity-60');
                divOptions.classList.add('hidden');
                selectChoixRecettes.classList.remove('choix-recette-bouton-recette-ouvert');
            })
        })



    //
    // Ajouter - Supprimer une ligne
    //
        // J'ajoute ou supprime une ligne avec les boutons + -
        const boutonMoins = document.querySelector('.js-bouton-moins');
        const boutonPlus = document.querySelector('.js-bouton-plus');
        boutonPlus.addEventListener('click', ()=> {
            // J'ajoute une nouvelle ligne au tableau
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <tr>
                    <td>
                        <div class="margin-top-15">
                            <div class="display-flex align-items-center justify-content-space-between padding-leftright-16 choix-recette-bouton-quantite">
                                <div class="cursor-pointer display-flex align-items-center js-personnes-bouton-moins">
                                    <img src="/img/moinsPetitNoir.svg" alt="" class="moins-petit-noir js-img-personnes-bouton-moins">
                                </div>
                                <div class="catamaran p-25 noir js-personnes-quantite">2</div>
                                <div class="cursor-pointer display-flex align-items-center js-personnes-bouton-plus">
                                    <img src="/img/plusPetitNoir.svg" alt="" class="plus-petit-noir">
                                </div>
                            </div>
                            <input type="number" name="personnes[]" min="1" value="2" class="hidden js-input-personnes">
                        </div>
                    </td>
                    <td class="position-relative">
                        <div class="margin-top-15">
                            <div class="margin-leftright-30">
                                <div class="js-bouton-choix-recettes js-target-bouton-choix-recettes choix-recette-bouton-recette display-flex align-items-center justify-content-space-between padding-leftright-16 cursor-pointer">
                                    <p class="catamaran p-20 opacity-60 noir js-target-bouton-choix-recettes js-bouton-choix-recettes-text">Choisir ma recette</p>
                                    <div class="choix-recette-fleche opacity-60 js-target-bouton-choix-recettes"></div>
                                </div>
                                <div class="div-choix-recettes-options js-div-choix-recettes-options hidden">
                                    <?php foreach ($recettes as $recette): ?>
                                        <div class="js-choix-recettes-option js-target-choix-recettes-option-div choix-recette-bouton-recette-option display-flex align-items-center justify-content-space-between padding-leftright-16">
                                            <p class="catamaran p-20 noir js-choix-recettes-option-text js-target-choix-recettes-option-p"><?=$recette['name']?></p>
                                            <p class="hidden js-choix-recettes-option-id"><?=$recette['id']?></p>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                                <select name="recette[]" class="hidden">
                                    <option value="null" class="js-select-recette"></option>
                                </select>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="margin-top-15">
                            <div class="display-flex align-items-center justify-content-space-between padding-leftright-16 choix-recette-bouton-quantite">
                                <div class="cursor-default display-flex align-items-center js-quantite-bouton-moins">
                                    <img src="/img/moinsPetitNoir.svg" alt="" class="moins-petit-noir opacity-20 js-img-quantite-bouton-moins">
                                </div>
                                <div class="catamaran p-25 noir js-quantite-quantite">1</div>
                                <div class="cursor-pointer display-flex align-items-center js-quantite-bouton-plus">
                                    <img src="/img/plusPetitNoir.svg" alt="" class="plus-petit-noir">
                                </div>
                            </div>
                            <input type="number" name="quantite[]" min="1" value="1" class="hidden js-input-quantite">
                        </div>
                    </td>
                    <td>
                        <div class="margin-top-15">
                            <div class="js-table-row-supp cursor-pointer img-poubelle-poss-hover margin-left-15"></div>
                        </div>
                    </td>
                </tr>
            `
            tableBody.appendChild(newRow);

            // Si besoin, je réactive les boutons supprimer qui avaient été désactivés s'il ne restait plus qu'une ligne
            if (tableBody.children.length > 1) {
                boutonMoins.classList.remove('opacity-20');
                boutonMoins.classList.remove('cursor-default');
                boutonMoins.classList.add('cursor-pointer');
                boutonMoins.classList.remove('choix-recette-bouton-moins');
                boutonMoins.classList.add('choix-recette-bouton-moins-poss-hover');
                let tableBoutonSupp = document.querySelector('.js-table-row-supp');
                tableBoutonSupp.classList.remove('opacity-20');
                tableBoutonSupp.classList.remove('cursor-default');
                tableBoutonSupp.classList.add('cursor-pointer');
                tableBoutonSupp.classList.remove('img-poubelle');
                tableBoutonSupp.classList.add('img-poubelle-poss-hover');
            }

            // Je permets la mise à jour du changement de nombre de personnes sur la nouvelle ligne
            let tableRowSup = tableBody.lastChild;
            let personnesBoutonMoinsRow = tableRowSup.querySelector('.js-personnes-bouton-moins');
            let personnesBoutonPlusRow = tableRowSup.querySelector('.js-personnes-bouton-plus');
            handlePersonnesChangeMoins(personnesBoutonMoinsRow);
            handlePersonnesChangePlus(personnesBoutonPlusRow);

            // Je permets la mise à jour du changement de quantite sur la nouvelle ligne
            let quantiteBoutonMoinsRow = tableRowSup.querySelector('.js-quantite-bouton-moins');
            let quantiteBoutonPlusRow = tableRowSup.querySelector('.js-quantite-bouton-plus');
            handleQuantiteChangeMoins(quantiteBoutonMoinsRow);
            handleQuantiteChangePlus(quantiteBoutonPlusRow);

            // Je permets l'ouverture et la fermeture de la div avec les options de recettes
            const select = tableRowSup.querySelector('.js-bouton-choix-recettes');
            select.addEventListener('click', (event)=>{
                event.stopPropagation();
                if(select.classList.contains('choix-recette-bouton-recette-ouvert')) {
                    select.nextElementSibling.classList.add('hidden');
                    select.classList.remove('choix-recette-bouton-recette-ouvert');
                } else {
                    Array.from(document.querySelectorAll('.js-bouton-choix-recettes')).forEach((selectTous)=>{
                        selectTous.classList.remove('choix-recette-bouton-recette-ouvert');
                        selectTous.nextElementSibling.classList.add('hidden');
                    })
                    select.nextElementSibling.classList.remove('hidden');
                    select.classList.add('choix-recette-bouton-recette-ouvert');
                    const recetteAfficheSelect = select.querySelector('.js-bouton-choix-recettes-text');
                    const recettesOptionsSelectP = Array.from(document.querySelectorAll('.js-target-choix-recettes-option-p'));
                    recettesOptionsSelectP.forEach((p)=>{
                        p.closest('div').classList.remove('choix-recette-bouton-recette-option-selectionne');
                        if(recetteAfficheSelect.innerHTML == p.innerHTML) {
                            p.closest('div').classList.add('choix-recette-bouton-recette-option-selectionne');
                        }
                    })
                }
            })

            // Si l'on clique sur une option de recette, la div se referme et la recette est passée en html
            const optionsRecettesItem = Array.from(tableRowSup.querySelectorAll('.js-choix-recettes-option'));
            optionsRecettesItem.forEach((item)=>{
                item.addEventListener('click', (event)=>{
                    event.stopPropagation();
                    const optionChoixRecettesText = item.children[0];
                    const optionChoixRecettesId = item.children[1];
                    const row = item.closest('tr');
                    const selectChoixRecettes= row.querySelector('.js-bouton-choix-recettes');
                    const selectChoixRecettesText = row.querySelector('.js-bouton-choix-recettes-text');
                    const divOptions = row.querySelector('.js-div-choix-recettes-options');
                    const selectRecetteValue = row.querySelector('.js-select-recette');

                    selectChoixRecettesText.innerHTML = optionChoixRecettesText.innerHTML;
                    selectRecetteValue.value = optionChoixRecettesId.innerHTML;
                    selectChoixRecettesText.classList.remove('opacity-60');
                    divOptions.classList.add('hidden');
                    selectChoixRecettes.classList.remove('choix-recette-bouton-recette-ouvert');
                })
            })

        });
 

        boutonMoins.addEventListener('click', ()=> {
            if (boutonMoins.classList.contains('opacity-20') == false) {
                // Je supprime la dernière ligne du tableau
                const lastChild = tableBody.lastElementChild;
                tableBody.removeChild(lastChild);

                // Si il ne reste plus qu'une ligne, je désactive les boutons supprimer
                if (tableBody.children.length == 1) {
                    boutonMoins.classList.add('opacity-20');
                    boutonMoins.classList.remove('cursor-pointer');
                    boutonMoins.classList.add('cursor-default');
                    boutonMoins.classList.remove('choix-recette-bouton-moins-poss-hover');
                    boutonMoins.classList.add('choix-recette-bouton-moins');
                    let tableBoutonSupp = document.querySelector('.js-table-row-supp');
                    tableBoutonSupp.classList.add('opacity-20');
                    tableBoutonSupp.classList.remove('cursor-pointer');
                    tableBoutonSupp.classList.add('cursor-default');
                    tableBoutonSupp.classList.remove('img-poubelle-poss-hover');
                    tableBoutonSupp.classList.add('img-poubelle');
                }
            }
            
        })

        // Je supprime une ligne avec le bouton supp
        function SupprimerLigneBoutonSupp (event) {
            if (event.target.classList.contains('js-table-row-supp') && event.target.classList.contains('opacity-20') == false) {
                // Je supprime la ligne souhaitée
                const rowToRemove = event.target.closest('tr');
                tableBody.removeChild(rowToRemove);

                // Si il ne reste plus qu'une ligne, je désactive les boutons supprimer
                if (tableBody.children.length === 1) {
                    boutonMoins.classList.add('opacity-20');
                    boutonMoins.classList.remove('cursor-pointer');
                    boutonMoins.classList.add('cursor-default');
                    boutonMoins.classList.remove('choix-recette-bouton-moins-poss-hover');
                    boutonMoins.classList.add('choix-recette-bouton-moins');
                    let tableBoutonSupp = document.querySelector('.js-table-row-supp');
                    tableBoutonSupp.classList.add('opacity-20');
                    tableBoutonSupp.classList.remove('cursor-pointer');
                    tableBoutonSupp.classList.add('cursor-default');
                    tableBoutonSupp.classList.remove('img-poubelle-poss-hover');
                    tableBoutonSupp.classList.add('img-poubelle');
                }
            }
        }
        tableBody.addEventListener('click', SupprimerLigneBoutonSupp);

    /// Lorsque je clique n'importe où le select se referme
    document.addEventListener('click', (event)=>{
            
        let element = event.target;
        
        // Lorsque je clique n'importe où le select recette se referme
        let elementIsSelect = false;
        while (element){
            if (element.classList.contains('js-select-recette-div')) {
                elementIsSelect = true;
                break;
            } else {
                element = element.parentElement;
            }
        }
        if (elementIsSelect == false) {
            Array.from(document.querySelectorAll('.js-bouton-choix-recettes')).forEach((selectTous)=>{
                selectTous.classList.remove('choix-recette-bouton-recette-ouvert');
                selectTous.nextElementSibling.classList.add('hidden');
            })
        }
    })

</script>