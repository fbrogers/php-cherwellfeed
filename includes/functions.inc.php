<?php

function getTicketsByTeam($team, $orderby = false){
    $instance = db::instance();
    $conn = $instance::connect();

    $query = "
    SELECT
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
    U.SAMAccountName AS [nid]
    FROM [Incident] I
    INNER JOIN [TeamDistribution] T ON I.OwnerTeamID = T.TeamID
    INNER JOIN [Departments] D ON I.BillableDepartment = D.DepartmentName
    LEFT JOIN [UserInfo] U ON I.OwnerID = U.RecID
    WHERE T.TeamName = ? AND (I.Status != 'Closed' AND I.Status != 'Resolved') 
    ORDER BY I.CreatedDateTime DESC
    ";
    $params = [$team];
    $result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsqrv_errors(), true));
    $incidents = [];
    while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
        $incidents[] = $row;
    }

    return $incidents;
}

function getResolvedTicketsByTeam($team){
    $instance = db::instance();
    $conn = $instance::connect();

    $query = "
    SELECT TOP 250
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
    U.SAMAccountName AS [nid]
    FROM [Incident] I
    INNER JOIN [TeamDistribution] T ON I.OwnerTeamID = T.TeamID
    INNER JOIN [Departments] D ON I.BillableDepartment = D.DepartmentName
    INNER JOIN [UserInfo] U ON I.OwnerID = U.RecID
    WHERE T.TeamName = ? AND (I.Status = 'Closed' OR I.Status = 'Resolved') 
    ORDER BY I.CreatedDateTime DESC
    ";
    $params = [$team];
    $result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsqrv_errors(), true));
    $incidents = [];
    while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
        $incidents[] = $row;
    }

    return $incidents;
}

function getTicketsByUser($user = null){
    $instance = db::instance();
    $conn = $instance::connect();

    if($user == null){
        $user = $_SERVER['AUTH_USER'];
    }

    $query = "
    SELECT
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
    U.SAMAccountName AS [nid]
    FROM [Incident] I
    INNER JOIN [Departments] D ON I.BillableDepartment = D.DepartmentName
    INNER JOIN [UserInfo] U ON I.OwnerID = U.RecID
    WHERE 
        (I.Status != 'Closed' AND I.Status != 'Resolved') 
        AND U.SAMAccountName = ?
    ORDER BY I.CreatedDateTime DESC
    ";
    $params = [$user];
    $result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsqrv_errors(), true));
    $incidents = [];
    while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
        $incidents[] = $row;
    }

    return $incidents;
}

function getAttachmentsById($recId){
    $instance = db::instance();
    $conn = $instance::connect();

    $query = "
    SELECT A.[RecID] AS [id], A.FilePath, A.FileExt
    FROM [TrebuchetShortcuts] S
    INNER JOIN [TrebuchetAttach] A ON S.TargetRecId = A.RecID
    WHERE [OwningBusObRecId] = ?
    ";
    $params = [$recId];
    $result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsqrv_errors(), true));
    $files = [];
    while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
        $files[] = $row;
    }

    return $files;
}

function getJournalsById($recId){
    $instance = db::instance();
    $conn = $instance::connect();

    $query = "
    SELECT 
    [JournalTypeName] AS [name],
    [CreatedDateTime],
    [CreatedBy],
    [OwnedByTeam] AS [team],
    [Details],
    [MailDirection]
    FROM [Journal]
    WHERE [ParentRecordID] = ?
    ORDER BY [CreatedDateTime] DESC
    ";
    $params = [$recId];
    $result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsqrv_errors(), true));
    $journals = [];
    while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
        $journals[] = $row;
    }

    return $journals;
}

function cherwellUrl($recId){
    $output = null;
    $output .= 'cherwellclient://commands/goto?recType=Incident&recid=';
    $output .= $recId;
    return $output;
}

function emailClientUrl($recId){
    $output = null;

    $instance = db::instance();
    $conn = $instance::connect();

    $query = "SELECT TOP 1 RecID, IncidentID, Summary, CreatedByEmail FROM [Incident] WHERE RecID = ?";
    $params = [$recId];
    $result = sqlsrv_query($conn, $query, $params) or die(print_r(sqlsrv_errors(), true));
    $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);

    $output .= 'mailto:'.$row['CreatedByEmail'];
    $output .= '?cc=sdesservicedesk@ucf.edu';
    $output .= '&amp;subject=Regarding SDES Service Desk Ticket '.$row['IncidentID'];
    $output .= ' - '.str_replace('&', '%26', $row['Summary']);

    return $output;
}

function GetTeams(){
    $instance = db::instance();
    $conn = $instance::connect();

    $teams = [];
    $query = "SELECT * FROM [TeamDistribution] ORDER BY [TeamName]";
    $result = sqlsrv_query($conn, $query) or die(print_r(sqlsqrv_errors(), true));
    while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
        $teams[] = $row['TeamName'];
    }

    return $teams;
}

?>