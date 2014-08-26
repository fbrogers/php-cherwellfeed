<?php
    //set team cookie
    if(!isset($_COOKIE['team'])){
        setcookie("team", "Select a Team");
        header("Location: ./");
        exit();
    }

    if(is_post() && isset($_POST['team'])){
        setcookie("team", $_POST['team']);
        header("Location: ./");
        exit();
    }

    //different controller actions
    $filter = isset($_GET['filter']) ? $_GET['filter'] : null;
    switch ($filter) {
        case 'my':
            $incidents = getTicketsByUser();
            break;
        case 'resolved':
            $incidents = getResolvedTicketsByTeam($_COOKIE['team']);
            break;
        case 'user':
            if(isset($_GET['user'])){
                $incidents = getTicketsByUser($_GET['user']);
                break;
            }
        default:
            $incidents = getTicketsByTeam($_COOKIE['team']);
            break;
    }

    //team list
    $teams = GetTeams();
    $links = '<div class="content-main-links">';
    $links .= '<form method="post">';
    $links .= 'Team: <select name="team" id="team" onchange="this.form.submit()">';
    $links .= '<option></option>';
    foreach($teams as $team){
        $links .= '<option>'.$team.'</option>';
    }
    $links .= '</select>';
    $links .= '</form>';
    $links .= '</div>';

    //page content
    $title = $_COOKIE['team'].' ('.count($incidents).')';
?>

<script type="text/javascript" src="//assets.sdes.ucf.edu/plugins/datatables/js/jquery.datatables.min.js"></script>
<script type="text/javascript">
$(function(){
    $('.display').dataTable({
        "bPaginate": false,
        "bLengthChange": false,
        "bFilter": false,
		"bInfo": false,
        "aaSorting": []
    });
});
</script>

<table class="grid smaller display">
    <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Created</th>
            <th scope="col">Status</th>
            <th scope="col">Title</th>
            <th scope="col">Customer, Dept</th>
            <th scope="col">Assigned To</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($incidents as $row): ?>
        <tr>
            <td><a href="?id=incident&amp;incident=<?= $row['IncidentID'] ?>"><?= $row['IncidentID'] ?></a></td>
            <td><?= date("Y-m-d g:ia", strtotime($row['CreatedDateTime'])) ?></td>
            <td><?= $row['Status'] == "Work In Progress" ? "WIP" : $row['Status'] ?></td>
            <td><?= $row['Summary'] ?></td>
            <td><?= $row['CustomerDisplayName'] ?>, <?= $row['dept'] ?></td>
            <td><a href="./?filter=user&amp;user=<?= $row['nid'] ?>"><?= $row['OwnedBy'] ?></a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>