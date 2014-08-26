<?php 
    if(isset($_GET['aid'])){        
        //get back the first picture
        $query = "SELECT TOP 1 [FilePath], [FileContents] FROM [TrebuchetAttach] WHERE [RecID] = ?";
        $result = sqlsrv_query($conn, $query, [$_GET['aid']]) or die(print_r(sqlsrv_errors(), true));   
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                
        //return file data
        $pieces = explode('\\', $row['FilePath']);
        $name = end($pieces);
        $periods = explode('.', $name);
        $ext = strtolower(end($periods));

        switch($ext){
            case 'pdf':
                header("Content-Disposition: inline; filename={$name}");
                header('Content-Type: application/pdf');
                break;
            case 'jpg':
            case 'jpeg':
                header("Content-Disposition: inline; filename={$name}");
                header('Content-Type: image/jpeg');
                break;
            case 'png':
                header("Content-Disposition: inline; filename={$name}");
                header('Content-Type: image/png');
                break;
            default:
                header("Content-Disposition: attachment; filename={$name}");
                header('Content-Type: application/octet-stream');
                break;
        }
        exit($row['FileContents']);
    }
?>