<?php include 'header.php';?>
<!DOCTYPE html>
<html>

<head>
	<title></title>
	<!doctype html>
<html lang="en-US">
<style type="text/css">
.width_size{width: 100% !important;
}
	@media only screen and (max-width: 765px) {
        .heading_h2{
        margin-top: 24% !important;
        }
}
.heading_h2{
margin-top: 15% ;
font-size: 22px !important;
margin-bottom: 18px !important;
}
.icon_book{
	display: none !important;
}
.margin_lef{
margin: 0px !important;

width: 100% !important;
}

	}
</style>

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
</head>
<body style="margin: 30px 30px">
<div class="container">	
	<h2 class="heading_h2">Pipeline Workflow</h2>
	<img src="../images/Map.png">
	<div class="kc-elm kc-css-362905 kc_col-sm-8 kc_column kc_col-sm-8 width_size"><div class="kc-col-container"><div class="paper_cut "><div class="pub-item with-icon">
                    <div class="elem-wrapper icon_book">
                        <i class="ion-ios-paper-outline"></i>
                    </div>
                    <div class="content-wrapper margin_lef">
                        <h3 class="title mb_0">About the Pipeline</h3>
                        <div class="slc_des">
                            <div class="authr font_size">As shown above, the Single Cell Wizard uses a clustering based approach for the identification of cells from raw single cell sequenced data. The input used is the three 10X data files: matrix.mtx, barcodes.tsv, and genes.tsv.
<br/>
<br/>
The tool first clusters the cells on the basis of gene similarity and overestimates the initial number of total clusters in order for more specific and accurate labelling through gene expression levels. Gene expression levels are employed for enrichment analysis against known cell type gene expression rankings, through this, we are able to accurate identify the cell type on a labelled plot as well as providing various details on how the identification was obtained. Below is a description for every output to the user.
<br/>
<br/>
The tool allows for the analysis of single sample and the comparison of two samples.</div>
                        </div>
                        
                  
                </div>
            </div></div></div></div>
<div class="kc-elm kc-css-362905 kc_col-sm-8 kc_column kc_col-sm-8 width_size"><div class="kc-col-container"><div class="paper_cut "><div class="pub-item with-icon">
                    <div class="elem-wrapper icon_book">
                        <i class="ion-ios-paper-outline"></i>
                    </div>
                    <div class="content-wrapper margin_lef">
                        <h3 class="title mb_0">Usage</h3>
                        <div class="slc_des">
                            <div class="authr font_size"> The key concept of the pipeline is to provide a combination of efficacy, efficiency, and ease of use. Usage instructions are provided below:
<br/>
    <ol><li>Job Name: You will need to enter a name for the job you are submitting. In the final output there will be a random number prepended to the job name in order to insure that your job is unique and correctly processed. For sample comparison, please enter a job name for each sample.</li>
    <li>File Upload: This step requires the user to upload all three single cell sequenced files: matrix.mtx, barcodes.tsv, genes.tsv. Please make sure that you select all three at once and then click open in your file selector prompt.</li>
    <li>GMT Selection: We have compiled developed 4 different gene sets for analysis and cell labelling. You may select one of the following:</li>
        <ul><li>Full Human Genes: Contains more than 600 unique cell type identifications. Primarily immune, skin, and muscle cells are in this database.</li>
        <li>Transcription Factors: Contains mapping for which transcription factor proliferated the coding of expressed genes.</li>
        <li>Mouse Genes: Contains the homologous genes to the Full Human Genes database in mice. Translated using a script for complete GMT conversion, please contact us if you would like to translate your GMT.</li>
        <li>Cancer/Stemness: Contains genes which are found in cancers and stemness indicators. Especially useful for cancer or general disease single cell insight.</li></ul>
    <li>Run Pipeline: Executes the pipeline on upload data. Please only click this button once, the page will load and display appropriate tSNE plots and links for your job's file downloads.</li>
    <li>Reset: Clear the previous job for analysis on other samples.</li></ol>

</div>
                        </div>
                        
                  
                </div>
            </div></div></div></div> 
<div class="kc-elm kc-css-362905 kc_col-sm-8 kc_column kc_col-sm-8 width_size"><div class="kc-col-container"><div class="paper_cut "><div class="pub-item with-icon">
                    <div class="elem-wrapper icon_book">
                        <i class="ion-ios-paper-outline"></i>
                    </div>
                    <div class="content-wrapper margin_lef">
                        <h3 class="title mb_0">Outputs</h3>
                        <div class="slc_des">
                            <div class="authr font_size">
    <ul>
    <li>*job name* tSNE: An unlabelled tSNE plot containing the clustered cells.</li>
    <li>Cluster GSEA Results: An excel workbook containing the information on how the identity of the cells was determined.</li>
    <li>Mapped Cluster Reference: An excel workbook containing information for the details on the determination of the broader identity of the cells.</li>
    <li>File.rds: A Seurat R object for further analysis. Contains cluster labelled cells, marker genes, PCAs, etc.</li>
    <li>File Markers: A CSV that contains ranked overexpressed expressed genes used for identification.</li>
    <li>Gene Set Labelled tSNE: A PNG of a labelled tSNE plot containing the clustered cells.</li>
    <li>MTX: A matrix containing the enrichment scores for cell identities on the basis of cluster for further analysis.</li>
    </ul>
</div>
                        </div>
                        
                  
                </div>
            </div></div></div></div>                        
</div>
</body>


</html>
<?php include 'footer.php';?>
