<?php include ('header.php');?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <!--<link href="style.css" rel="stylesheet">-->
    <link rel="shortcut icon" type="image/png" href="../images/favicon.ico"/>
  </head>
  <div class="container" style="margin: 25px 25px">
    <div style:"display:inline">
      <!--?php include 'old_header.php';?>-->
      <!--<h1 style="margin-left: 120px;font-size: 18px;">Single Cell Wizard</h1>-->
    </div>
    <body style="padding-top: 56px">
      <br />
      <br />
      <?php
        session_start();
        if(isset($_POST['reset'])||isset($_SESSION['pipe_done'])){
        session_unset();
        unset($_POST['reset']);
        }
        $set = False; 
      ?>
      <fieldset class="field_set">
        <form action="scwiz2.php" method = "post" enctype="multipart/form-data">
          <div class="uemailcc">
            <label class="labell">Email: <font color="red">*</font></label>
            <input type="text" name="uemail" id="uemail" class="iii form-control">
          </div>
          <br />
          <div class="jobnamecc">
            <label class="labell">Job Name: <font color="red">*</font></label>
            <input type="text" name="jobName" id="jobName" class="iii form-control">
          </div>
          <br />
          <a href="./propic/Sample_SingleCell_Data.zip" download="sample_data.zip" class="der" style="">Download Sample Data</a>
          <br />
          <br />
          <label class="idjd" for="upload">
            <p>Upload FASTQ Data<font color='red' style="font-family:'Courier New'">(max file size = 10Gb)*</font></p>
          </label>
          <br />
          <input id="upload" name="upload[]" type="file" multiple="multiple" class="uploadimg" />	
          <br />
          <label class="idjd" for="species">Species</label>
          </br>
          <select name="species" class="margin_pixel">
            <option value="H">Human</option>
            <option value="M">Mouse</option>
          </select>
          <br />
          <label for="protocol" class="gmtclass">Protocol</label>
          <br />
          <select name="protocol" class="margin_pixel">
            <option value="10X">10X</option>
            <option value="dropseq">Dropseq</option>
            <option value="SRA">SRA</option>
            <option value="celseq">Celseq</option>
          </select>
          <br />
          <label for="aligner" class="gmtclass">Aligner</label>
          </br>
          <select name="aligner" class="margin_pixel">
            <option value="hisat2">hisat2</option>
            <option value="STAR">STAR</option>
          </select>
          </br>
          <label for="qc" class="gmtclass">Quality Control</label>
          </br>
          <select name="qc" class="margin_pixel">
            <option value="yes">Yes</option>
            <option value="no">No</option>
          </select>
          </br>
          </br>
          <label class="idjd" for="ssize">Sample Size: <font color="red">*</font></label>
          <input type="text" name="ssize" id="ssize" class="iii form-control">
          <label for="pp" class="gmtclass">Post Process:</label>
          </br>
          <select name="pp" class="margin_pixel">
            <option value="yes">yes</option>
            <option value="no">no</option>
          </select>
          <label class="labell">Barcode Length (Only for SRA):</label>
          <input type="text" name="bclen" id="bclen" class="iii form-control">
          <label class="labell">UMI Length (Only for SRA):</label>
          <input type="text" name="umi" id="umi" class="iii form-control">
          
          <!--- <label class="labell">Min Reads/Barcode (Only for SRA):</label>
            <input type="text" name="min_bc" id="min_bc" class="iii form-control">
            --->

          <a href="javascript:toggleTable()">advance options</a><br>
          <div id="show_advance", style="display:none">
            <label class="min_cells">min cells (default=3):</label>
            <input type="text" name="min_cells" id="min_cells" class="iii form-control">
            <label class="min_genes">min genes (default=100):</label>
            <input type="text" name="min_genes" id="min_genes" class="iii form-control">
          </div>
          <script>
            function toggleTable() {
               var lTable = document.getElementById("show_advance");
               lTable.style.display = (lTable.style.display == "table") ? "none" : "table";
            }
          </script>
          <input class="submitbuton" type="submit" name="RunPipeline" value = "Run Pipeline">
          <input class="submitbuton" type="submit" name="reset" value = "Reset"/>
      </fieldset>
      </form>
      </br>


      
      <?php
        if(isset($_POST['RunPipeline'])){

          if(!empty($_POST['min_cells'])){
            $_SESSION['min_cells'] = $_POST['min_cells'];
          }
          else{
            $_SESSION['min_cells'] = 3;
          } 
          if(!empty($_POST['min_genes'])){
            $_SESSION['min_genes'] = $_POST['min_genes'];
          }
          else{
            $_SESSION['min_genes'] = 100;
          }
          if(!empty($_POST['bclen'])){
            $_SESSION['bclen'] = $_POST['bclen'];
          }
          else{
            $_SESSION['bclen'] = "None";
          }
          if(!empty($_POST['umi'])){
            $_SESSION['umi'] = $_POST['umi'];
          }
          else{
            $_SESSION['umi'] = "None";
          }
          if(empty($_POST['jobName'])){
            $error = "quit";
            echo '<script>alert("error! please provide a job name.")</script>';
          }
          if(empty($_POST['ssize'])){
            $error = "quit";
            echo '<script>alert("error! please provide an approximate sample size.")</script>';
          }
          if(empty($_POST['uemail'])){
            $error = "quit";
            echo '<script>alert("error! please give an email address to recieve the output.")</script>';
          }

       
          $_SESSION['jobName']	= $_POST['jobName'];
          $_SESSION['qc'] 	= $_POST['qc'];
          $_SESSION['pp']	= $_POST['pp'];
          $_SESSION['aligner']	= $_POST['aligner'];
          $_SESSION['protocol']	= $_POST['protocol'];
          $_SESSION['ssize']	= $_POST['ssize'];
          $_SESSION['species']	= $_POST['species'];
          $_SESSION['uemail']	= $_POST['uemail'];
          $currentDir 		= getcwd();
          $folderDir 	        = "/home/ubuntu/project/" . $_SESSION['jobName'];
        
          $uploadDir = $folderDir . "/" . "_tmp_upload"; 
          $_SESSION['dir'] = $uploadDir;
          mkdir($uploadDir, 0777, true);
          chmod($uploadDir, 0777);
          chmod($folderDir, 0777);
          chown($folderDir, "ubuntu");
          chown($uploadDir, "ubuntu");
          $_SESSION['dir'] = $uploadDir;

          /* Function to convert bytes to human readable file sizes */
          function convert_filesize($bytes, $decimals = 2){
            $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
            $factor = floor((strlen($bytes) - 1) / 3);
            return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
        	}
        
          $TotalSize=0;
          $FCount = count($_FILES['upload']['name']);

          /* Checking uploaded single-end fastq files */ 
          if($_SESSION['protocol'] == 'SRA'){
	    if($FCount != 1){
               echo '<script>alert("Error! you are only supposed to upload only 1 single-end fastq file.")</script>';
               $error = "quit";
		}
            else{
              for($i=0; $i<$FCount; $i++){
              $FileType = strtolower(pathinfo(basename($_FILES['upload']['name'][$i]),PATHINFO_BASENAME));
              #echo '<script type="text/javascript">alert("'.$FileType.'");</script>';
              
              /* regex to check if the passed files have fastq/fq followed by gz extension */
              if(!(preg_match('/.*fastq.gz/', $FileType) || preg_match('/.*fq.gz/', $FileType))){
                echo '<script>alert("Error! Please make sure uploaded fastq file ends with [fastq/fq].gz")</script>';
                $error = "quit";
               }
             }
           }
         } 

          /* Checking uploaded pair-end fastq files */
	  else{
          for($k=0; $k<$FCount; $k++){
            $TotalSize += $_FILES['upload']['size'][$k];
            $FileType = strtolower(pathinfo(basename($_FILES['upload']['name'][$k]),PATHINFO_BASENAME)); 
            /* Check if the number of files uploaded are 2 */
            if($FCount != 2){
              echo '<script>alert("Error! Please make sure to only upload 2 files - R1 and R2")</script>';
             }
            
            if(!(preg_match('/.*[1-2]*fastq.gz/', $FileType) || preg_match('/.*[1-2]*fq.gz/', $FileType))){
              echo '<script>alert("Error! Please make sure uploaded fastq file ends with [fastq/fq].gz")</script>';
              $error = "quit";
             }
            } 
           }
           $Converted_Size = ($TotalSize*0.000001)*(0.001);
           if($Converted_Size >= 10){
             echo '<script>alert("error! file is too large to process. Please make sure the file(s) are below 10Gb.")</script>';
             $error = "quit";
            }

	   if($error == "quit"){
             echo '<script>alert("Please review the errors and try again.")</script>';
            }
           else{
             if(count($_FILES['upload']['name']) == 2 || count($_FILES['upload']['name'] == 1)){
               for($i=0; $i < count($_FILES['upload']['name']); $i++){
                 $tmpFilePath = $_FILES['upload']['tmp_name'][$i];
                 $fileName = $_FILES['upload']['name'][$i];
                   if($tmpFilePath != ""){
                     $filenm = $_FILES['upload']['name'][$i];
                     if(move_uploaded_file($tmpFilePath, ($_SESSION['dir'] . "/" . $filenm))) {
                       $files[] = $_FILES['upload']['name'][$i];
                    				}
                  			}			
                		}
             		}
        	
          $python = exec("nohup python3 /home/ubuntu/pre_process/database/database_helper.py -i " . $_SESSION['dir'] . " -id " . $_SESSION['jobName'] . " -p " . $_SESSION['protocol'] . " -o " . $folderDir . " -sp " . $_SESSION['species'] . " -bc " . $_SESSION['bclen'] . " -umi " . $_SESSION['umi'] . " -ss " . $_SESSION['ssize'] . " -e " . $_SESSION['uemail'] . " -a " . $_SESSION['aligner'] . " -pp " . $_SESSION['pp'] . " -min_cells " . $_SESSION['min_cells']. " -min_genes " . $_SESSION['min_genes'] ."&");
      
          session_destroy();
         }
     }
        ?>
  </div>
  </body>
</html>
<?php include 'bhasin_lab_footer.php';?>

