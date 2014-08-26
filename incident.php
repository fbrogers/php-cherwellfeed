<?php
    if(!isset($_GET['incident']) || !is_numeric($_GET['incident'])){
        exit(header("Location: ./"));
    }

    $query = "
        SELECT TOP 1
            I.RecID,
            I.IncidentID,
            I.CreatedDateTime,
            I.CreatedBy,
            I.[Status],
            I.Impact,
            I.Urgency,
            I.Priority,
            I.OwnedBy,
            I.[Source],
            I.CustomerDisplayName,
            I.PendingReason,
            I.Summary,
            D.DepartmentAbbreviation AS [dept],
            COALESCE(S.[Description], I.[Description]) AS [Description]
        FROM [Incident] I
        INNER JOIN [TeamDistribution] T ON I.OwnerTeamID = T.TeamID
        INNER JOIN [Departments] D ON I.BillableDepartment = D.DepartmentName
        LEFT JOIN [Specifics] S ON I.RecID = S.Sdf
        WHERE [IncidentID] = ?
    ";
    $result = sqlsrv_query($conn, $query, [$_GET['incident']]) or die(print_r(sqlsrv_errors(), true));   
    $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
    $title = 'Incident #'.$_GET['incident'];
    $links = [
        'Open in Cherwell' => cherwellUrl($row['RecID']), 
        'Email Client' => emailClientUrl($row['RecID']),
        'Back' => './'
    ];
    $files = getAttachmentsById($row['RecID']);
    $journals = getJournalsById($row['RecID']);

    $stupids = ['image001.png', 'image001.jpg', 'image002.png', 'image002.jpg'];
    foreach($files as $i => $stupid){
        $pieces = explode('\\', $stupid['FilePath']);
        $last = end($pieces);
        if(in_array($last, $stupids)){
            unset($files[$i]);
        }
    }
?>

<div class="sidebar-right">
    <div class="event-title">Quick Data</div>
    <div class="menu">
        <table class="grid smaller mobile">
            <tr>
                <th scope="row">Status</th>
                <td>
                    <?= $row['Status'] ?>
                    <?= $row['Status'] == "Pending" ? ' ('.$row['PendingReason'].')' : null ?>
                </td>
            </tr>
            <tr>
                <th scope="row">Assigned</th>
                <td><?= $row['OwnedBy'] ?: "<em>Not Assigned</em>" ?></td>
            </tr>
            <tr>
                <th scope="row">Customer</th>
                <td><?= $row['CustomerDisplayName'] ?>, <?= $row['dept'] ?></td>
            </tr>
            <tr>
                <th scope="row">Created</th>
                <td><?= date("Y-m-d g:ia", strtotime($row['CreatedDateTime'])) ?></td>
            </tr>
            <tr>
                <th scope="row">Source</th>
                <td><?= $row['Source'] ?></td>
            </tr>
        </table>
    </div>

    <?php if(!empty($files)): ?>
    <div class="event-title">Attachments</div>
    <div class="menu">
        <ul>
            <?php
                foreach($files as $i => $file):
                $pieces = explode('\\', $file['FilePath']);
            ?>
            <li><a href="?id=attachment&amp;aid=<?= $file['id'] ?>"><?= end($pieces) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>
<div class="left">
    <h2 class="summary"><?= $row['Summary'] ?></h2>
    <p><?= nl2br(htmlentities($row['Description'])) ?></p>

    <?php if(!empty($journals)): ?>
    <div class="hr"></div>
    <div class="mzero">
        <?php foreach($journals as $i => $row): ?>
        <?php 
        if(($row['CreatedBy'] != 'Cherwell Admin' || $row['MailDirection'] != "Outgoing")
            && $row['name'] != "Journal - SLM History"
            && $row['Details'] != "Automatically reset Pending Review date for +1 business day."){ ?>
        
        <h3 class="entry">
            <?= str_replace('Journal - ', '', $row['name']) ?>
            <?= $row['MailDirection'] ? ' ('.$row['MailDirection'].')' : null ?>
        </h3>
        <div class="datestamp">
            <?= date("Y-m-d g:ia", strtotime($row['CreatedDateTime'])) ?> by <?= $row['CreatedBy'] ?>
        </div>
        <div class="entry">
            <p><?= nl2br($row['Details']) ?></p>
        </div>

        <div class="hr-blank"></div>

        <?php } ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<div class="hr-clear"></div>