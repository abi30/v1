<?php 
require_once('db.php');
require_once('../model/Task.php');
require_once('../model/Response.php');
  

try {
    $writeDB= DB::connectWriteDB();
    $readDB=DB::connectReadDB();
    //code...
} catch (PDOException  $ex) {
    error_log("Connection error - ".$ex,0);
    $response=new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database connection Error!!");
    $response->send();
    exit();

}

if (array_key_exists("taskid",$_GET)) {
    $taskid= $_GET['taskid'];
    if($taskid=='' ||  !is_numeric($taskid)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Task ID cannot be blank or must be numeric!!");
        $response->send();
        exit;
        
    }
    
    if($_SERVER['REQUEST_METHOD']==='GET'){
        
        
        try {
            $query = $readDB->prepare('SELECT id, title, description, DATE_FORMAT(deadline, "%d/%m/%Y %H:%i")as deadline, completed from tbltasks where id = :taskid');
            
            $query->bindParam(':taskid', $taskid, PDO::PARAM_INT);
            $query->execute();
            $rowCount=$query->rowCount();
            

        $taskArray = array();

        if($rowCount==0){
            $response=new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Task not found!");
            $response->send();
            exit();

        }

       while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // create new task object for each row
        $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);

        // create task and store in array for return in json data
  	    $taskArray[] = $task->returnTaskAsArray();
      }
        $returnData=array();
        $returnData['rows_returned']=$rowCount;
        $returnData['tasks']=$taskArray;

        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->toCache(true);
        $response->setData($returnData);
        $response->send();
        exit;


       }
       catch(TaskException $ex){

        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage($ex->getMessage());
        $response->send();
        exit;

       }
       catch (PDOException  $ex) {
            error_log("Database query error - ".$ex,0);
            $response=new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to get Task!");
            $response->send();
            exit;
        
        }

    }elseif($_SERVER['REQUEST_METHOD']=== 'DELETE'){

        try {
        $query= $writeDB->prepare('DELETE FROM tbltasks WHERE id = :taskid');
        $query->bindParam(':taskid',$taskid , PDO:: PARAM_INT);
        $query->execute();

        if($rowCount===0){
            $response=new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Task not found!");
            $response->send();
            exit();

        }
    
        } catch (PDOException $ex) {
            
        }





    }elseif($_SERVER['REQUEST_METHOD']=== 'PATCH'){

    }else{
        $response=new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed!");
        $response->send();
        exit();
    }

}

  
  
  ?>