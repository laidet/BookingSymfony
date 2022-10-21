$('#add-image').click(function(){

    // récupérer le n° des futurs champs
    const index = +$('#widget-count').val();

    // récupérer le prototype des entrées
    const tmpl=$('#annonce_images').data('prototype').replace(/__name__/g,index);
        // console.log(tmpl);

    // injecter le code dans div pour ajouter un champ
    $('#annonce_images').append(tmpl);

    // on ajoute 1 à la valeur initiale de la collection pour qu'une nouvelle valeur de "hidden" se modifie en fonction du tableau
    $('#widget-count').val(index+1);

    deleteButtons();

});

// fonction créer pour enlever le bug de la valeur du hidden qui répéte 2 fois le 0 du tableau des champs
function updateCounter(){

    const count = +$('#annonce_images div.form-group').length;

    // on met à jour la valeur de widget-counter
    $('#widget-count').val(count);

}

// fonction pour effacer les champs d'ajout d'image en trop 
function deleteButtons(){

    $('button[data-action = "delete"]').click(function(){

        const target = this.dataset.target;

        $(target).remove();
    });
}

updateCounter();
deleteButtons();