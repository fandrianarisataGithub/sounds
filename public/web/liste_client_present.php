<?php

// connexion à la base de donnée avec PDO 

$bdd = new PDO("mysql:host=localhost; dbname=dashboard_enac", "root", "");

// requette selection de tous les clients présents même le jour actuel( sans ceux qui ne sont qu'en reservetion )

$sql = "SELECT * FROM client WHERE date_depart > DATE_ADD(NOW(), INTERVAL -1 DAY) AND date_arrivee < NOW() ORDER by id DESC;";

$req = $bdd->query($sql);

while ($liste = $req->fetch()) {
?>

    <tr>
        <td>
            <span>
                <span class="nom_client"><?php echo ($liste["nom"]);  ?></span>
                <br>
                <span class="prenom_client"><?php echo ($liste["prenom"]);  ?></span>
            </span>
        </td>
        <td>
            <span class="date_arrivee_client">
                <?php echo ($liste["date_arrivee"]);  ?>
            </span>
        </td>
        <td>
            <span class="date_depart_client">
                <?php echo ($liste["date_depart"]);  ?>
            </span>
        </td>
        <td>
            <span class="duree_sejour_client"><?php echo ($liste["duree_sejour"]);  ?> nuitée(s)</span>
        </td>
        <td class="td__actions">
            <a href="#" data-toggle="modal" data-target="#modal_form" class="btn_client_modif" data-id=" <?php echo ($liste["id"]);  ?> ">
                <span class="fa fa-edit"></span>
            </a>
            <a href="#" data-toggle="modal" data-target="#modal_form_confirme" class="btn_client_suppr" data-id=" <?php echo ($liste["id"]);  ?> ">
                <span class="fa fa-trash-o"></span>
            </a>
        </td>
    </tr>

<?php

}

?>