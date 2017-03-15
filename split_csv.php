<?php
  ini_set('display_errors','On'); // DEBUG...
  error_reporting( E_STRICT | E_ALL ); // DEBUG...

  $up_errs = array(
		0=>"There is no error, the file uploaded with success.", 
		1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini.", 
		2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.", 
		3=>"The uploaded file was only partially uploaded.", 
		4=>"No file was uploaded.", 
		6=>"Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.", 
		7=>"Failed to write file to disk.", 
		8=>"A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help"
  );
  
  $split_dir = "splits/";
	$ufile_target = "";
  
  
  $file_header = "";
  $file_content = "";
  $max_rows = 5000; // 1 header row + 4999 data rows
  
  if(isset($_POST['action']) && $_POST['action'] == 'split'){
    if($_FILES['source_file']['error'] == 0){

      $file_src = $_FILES['source_file']['tmp_name'];
      $file_name = str_replace(".csv","",$_FILES['source_file']['name']);
      $file_counter = 1; // append to end of file name

      $i = 0; // source file row counter
      $col = 0; // source file row counter
      $row = 1; // destination file counter (keep under $max_rows)
      
      if(($handle = fopen($file_src, "r")) !== FALSE) {

        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
          $col = count($data);
          //echo "<pre>".print_r($data,1)."</pre>";
          if($i==0){
            
            // store the file header
            for($n=0;$n<$col;$n++){
              if($n>0){
                $file_header.= ",";
              }
              
              $file_header.= $data[$n];
            }
            
            $file_header.= "\n";
          }
          else{
            if($row<$max_rows){
              for($n=0;$n<$col;$n++){
                if($n>0){
                  $file_content.= ",";
                }
              
                $file_content.= '"'.$data[$n].'"';
              }
              
              $file_content.= "\n";
            }
            else{
              make_file($file_name,$file_counter,$split_dir,$file_header,$file_content);
              
              // increment
              $file_counter++;
              
              // reset
              $file_content = "";
              
              // record this row
              for($n=0;$n<$col;$n++){
                if($n>0){
                  $file_content.= ",";
                }
              
                $file_content.= '"'.$data[$n].'"';
              }
              
              $file_content.= "\n";
              
              
              $row = 1;
            }
            $row++;
          }
          $i++;
        }
        
        make_file($file_name,$file_counter,$split_dir,$file_header,$file_content);
        
        fclose($handle);
      }
        
    }
    else{
      echo "ERROR: ".$_FILES['source_file']['error'];
    }
  }
  
  function make_file($file_name,$file_counter,$split_dir,$file_header,$file_content){
    
    // name file
    $name = $file_name."_".$file_counter.".csv";
    
    // set path
    $path = $split_dir.$name;
    
    // set content
    $content = $file_header.$file_content;
    
    // save file
    if(($fp = fopen($path, "w+")) !== FALSE) {
      fwrite($fp, $content);
      fclose($fp);
    }
    
  } // make_file()
  

  //echo "<pre>".print_r($_POST,1)."</pre>";
  //echo "<pre>".print_r($_FILES,1)."</pre>";
?>
<!DOCTYPE html>
<html lang="en">
  
  <head>
    <title>CSV splitter</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
    .container{
      margin-top:50px;
    }
    .pad-right{
      text-align:right;
      padding-right:30%;
    }
    </style>
  </head>
  
  <body>
    
    <div class="container col-md-4 col-md-offset-4">
      <div class="row">
        
        <form action="" method="post" enctype="multipart/form-data">
          <div class="row">
            <input type="file" name="source_file">
          </div>
        
          <div class="row pad-right">
            <button type="submit" class="btn btn-default" name="action" value="split">Submit</button>
          </div>
        </form>
        
      </div>
    </div>
    
  </body>
  
</html>