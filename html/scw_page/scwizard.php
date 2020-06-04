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
      
      <!-- Importing Sweet alert function for creating nice javascript alerts */ -->
      <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
      <!--<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>-->
      
      <!-- Javascript for selecting chemistry -->
      <script>
        function yesnoCheck(that) {
          if (that.value == "10X") {
            document.getElementById("ifYes").style.display = "inline";
          } else {
            document.getElementById("ifYes").style.display = "none";
            }

          if (that.value == "SRA") {
            document.getElementById("SRA_Class").style.display = "inline";
          } else{
            document.getElementById("SRA_Class").style.display = "none";
            }
       }
        function toggleTable() {
          var lTable = document.getElementById("show_advance");
          lTable.style.display = (lTable.style.display == "table") ? "none" : "table";
            }

      </script>
    

      <?php
        session_start();
        if(isset($_POST['reset'])||isset($_SESSION['pipe_done'])){
        session_unset();
        unset($_POST['reset']);
        }
        $set = False; 
      ?>
      <fieldset class="field_set">
        <form action="scwizard.php" method = "post" enctype="multipart/form-data">
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
          <a href="./Sample/please_extract_me.tar.gz" class="der" style="">Download Sample Data</a>
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
          <label for="protocol" class="labell">Protocol</label>
          <br />
          <select name="protocol" class="margin_pixel" onchange="yesnoCheck(this);">
            <option value="10X">10X</option>
            <option value="dropseq">Dropseq</option>
            <option value="SRA">SRA <font color='red'>(single-end)</font></option>
            <option value="celseq">Celseq</option>
          </select>
          <br />
         
          <div id ="ifYes" style="display: inline;">
           <label class="labell">Chemistry for 10X<font color='red'> *</font></label>
           <br />
           <select name="chemistry" class="margin_pixel">
           <option value="10Xv2">Chromium V2 Chemistry</option>
           <option value="10Xv3">Chromium V3 Chemistry</option>
           </select>
          </div>
          <br />
          <div id="SRA_Class" style="display: none;">
            <label class="labell">Barcode Length:<font color='red'> *</font></label>
            <input type="text" name='bclen' id='bclen' class='iii form-control'>
            <label class='labell'>UMI Length:<font color ='red'> *</font></label>
            <input type='text' name='umi' id='umi' class='iii form-control'>
          </div> 
          <br />
          <label for="aligner" class="gmtclass">Aligner</label>
          </br>
          <select name="aligner" class="margin_pixel">
            <option value="hisat2">hisat2</option>
            <option value="STAR">STAR</option>
          </select>
          </br>
          <label for="qc" class="labell">Quality Control</label>
          </br>
          <select name="qc" class="margin_pixel">
            <option value="yes">Yes</option>
            <option value="no">No</option>
          </select>
          </br>
          </br>
          <label class="labell" for="ssize">Sample Size: <font color="red">*</font></label>
          <input type="text" name="ssize" id="ssize" class="iii form-control">
          <label for="pp" class="labell">Post Process:</label>
          </br>
          <select name="pp" class="margin_pixel">
            <option value="yes">yes</option>
            <option value="no">no</option>
          </select>
	  <br />
          <a href="javascript:toggleTable()" class='labell'>advance options</a><br>
          <div id="show_advance", style="display:none">
            <label class="labell">min cells (default=3):</label>
            <input type="text" name="min_cells" id="min_cells" class="iii form-control">
            <label class="labell">min genes (default=100):</label>
            <input type="text" name="min_genes" id="min_genes" class="iii form-control">
          </div>
          <input class="submitbuton" type="submit" name="RunPipeline" value = "Run Pipeline">
          <input class="submitbuton" type="submit" name="reset" value = "Reset"/>
      </fieldset>
      </form>
      </br>
      
      <?php
        if(isset($_POST['RunPipeline'])){

          if($_POST['protocol'] == '10X'){$_POST['protocol'] = $_POST['chemistry'];}


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
            echo '<script>alert("Error! Please provide a job name")</script>';
          }
          if(empty($_POST['ssize'])){
            $error = "quit";
	    echo '<script>alert("Error! Please provide an approximate sample size")</script>';
	  }
          if(empty($_POST['uemail'])){
            $error = "quit";
            echo '<script>alert("Error! please provide an email address to recieve the output.")</script>';
          }
             
          echo '<script type="text/javascript">swal(modals);</script>';

          $_SESSION['jobName']	= $_POST['jobName'];
          $_SESSION['qc'] 	= $_POST['qc'];
          $_SESSION['pp']	= $_POST['pp'];
          $_SESSION['aligner']	= $_POST['aligner'];
          $_SESSION['protocol']	= $_POST['protocol'];
          $_SESSION['ssize']	= $_POST['ssize'];
          $_SESSION['species']	= $_POST['species'];
          $_SESSION['uemail']	= $_POST['uemail'];
          $currentDir = getcwd();
          $folderDir = "/home/ubuntu/scwizard_pipeline/outputs" . $_SESSION['jobName'];
        
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
                if(!(preg_match('/.*fastq.gz/', $FileType) || preg_match('/.*fq.gz/', $FileType) || preg_match('/.*sra/', $FileType))){
                  echo '<script>alert("Error! Please make sure uploaded fastq file ends with [fastq/fq].[gz/sra]")</script>';
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
            
            if(!(preg_match('/.*[1-2].*fastq.gz/', $FileType) || preg_match('/.*[1-2].*fq.gz/', $FileType))){
              echo '<script>alert("Error! Please make sure uploaded fastq file ends with [fastq/fq].gz")</script>';
              $error = "quit";
             }
            } 
           }
           $Converted_Size = ($TotalSize*0.000001)*(0.001);
           if($Converted_Size >= 10){
             echo '<script>alert("Error! file is too large to process. Please make sure the file(s) are below 10Gb.")</script>';
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
        	
          $python = exec("nohup python3 /home/ubuntu/scwizard_pipeline/database/database_helper.py -i " . $_SESSION['dir'] . " -id " . $_SESSION['jobName'] . " -p " . $_SESSION['protocol'] . " -o " . $folderDir . " -sp " . $_SESSION['species'] . " -bc " . $_SESSION['bclen'] . " -umi " . $_SESSION['umi'] . " -ss " . $_SESSION['ssize'] . " -e " . $_SESSION['uemail'] . " -a " . $_SESSION['aligner'] . " -pp " . $_SESSION['pp'] . " -min_cells " . $_SESSION['min_cells']. " -min_genes " . $_SESSION['min_genes'] ."&");

          echo '<script>
          	  swal("Job submitted!", "You will receive the output to your email once completed.", "success");
                </script>';

          session_destroy();
         }
     }
        ?>
  </div>
  </body>
</html>
<?php include 'footer.php';?>

