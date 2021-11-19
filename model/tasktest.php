<?php 

require_once('task.php');


try {
    $task = new Task(1,"title is here","descripiton is here","28/02/2005 12:30","N");
    header('Content-type:application/json; charset=UTF-8');
    echo json_encode($task->returnTaskAsArray());



} catch (TaskException $ex) {
    echo "Error: ".$ex->getMessage();
}

?>