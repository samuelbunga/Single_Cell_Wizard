#!/usr/bin/perl
use warnings;
use strict;
use feature qw(say);
use Getopt::Long;
use File::Copy qw(copy);

=cut
Program:        Pre-processing of 10X, CelSeq and DropSeq protocol fastq
Input:          Paired-end fastq files
Output:         Counts matrix
Author:         Samuel Bunga
Contact:        bunga.s@husky.neu.edu
=cut


GetOptions (	"sp=s" 		=> \my $species,    		#M or H
              	"file=s"   	=> \my $data,      		#path to the file
              	"out=s"  	=> \my $name,			#name of the project
		"protocol=s" 	=> \my $protocol,  		#protocol used
		"sample_size=i" => \my $sample_size,		#size of the sample
		"qc=s"		=> \my $qc,			#Perfrom QC switch(yes/no)
		"aligner=s"	=> \my $aligner,		#Choose between hisat2 and STAR(default is hisat2)
		"email=s"	=> \my $to_email,		#Email ID
		"post_process=s"=> \my $pp,			#Post Process
		"id=s"		=> \my $id,			#Job name
		"min_cells=i"	=> \my $min_cells,
		"min_genes=i"	=> \my $min_genes,
	)
  or die("Error in command line arguments\n");

	my $protocol_temp = $protocol;
	my $sp = $species;
#Check if name is provided
	if(defined $name){
	}
	else{
		die "Please provide -out name"
	}
	
	my $dir = $name;

#Getting the file name
	my $R1 = `cd $data && find -type f -iname "*1*fastq*" -printf "%f"`;
	my $R2 = `cd $data && find -type f -iname "*2*fastq*" -printf "%f"`;
	chomp $R1;
	chomp $R2;

	my $R1_path = join("/", $data, $R1);
	my $R2_path = join("/", $data, $R2);


#Checking for the type of protocol
	if($protocol eq "celseq"){
    		$protocol        = "CCCCCCCCNNNNNN";
	}
	elsif($protocol eq "dropseq"){
    		$protocol        = "CCCCCCCCCCCCNNNNNNNN";
	}
	elsif($protocol eq "10X" || $protocol eq "10x"){
    		$protocol        = "CCCCCCCCCCCCCCCCNNNNNNNNNN";
	}

	chomp $protocol;

#Create a working directory path
	my $wd = $name . "/";

#Create path to R1 and R2 out
	my $R1_out = join("",$wd,"R1.fastq.gz");
	my $R2_out = join("",$wd,"R2.fastq.gz");
 
#Check if QC is defined or not
	unless($qc){
		say "Quality check has not been defined. Performing default settings( qc == yes)!";
		$qc = "yes";
	}

#------------------------------------ QUALITY CHECK ------------------------------------

	my($R2_name,$umi,$exe);
	$R2_name = "R2_extracted";
	if($qc eq "yes"){	
		say "running quality check using fastp";
		#Quality filter using FastP
		my $fastp = `/home/ubuntu/anaconda3/bin/fastp  -i $R1_path -I $R2_path -o $R1_out -O $R2_out`;
		say "done";

		say "Identify correct cell barcodes";
		$umi = `/home/ubuntu/anaconda3/bin/umi_tools whitelist --stdin $R1_out --bc-pattern=$protocol --set-cell-number=$sample_size --log2stderr > $wd/whitelist.txt`;
		say "done";
		
		say "Extract barcdoes and UMIs and add to read names";
		$umi = `/home/ubuntu/anaconda3/bin/umi_tools extract --bc-pattern=$protocol --stdin $R1_out --stdout $wd/R1_extracted.fastq.gz --read2-in $R2_out --read2-out=$wd/$R2_name.clean.fastq.gz --filter-cell-barcode --whitelist $wd/whitelist.txt`;   
		say "done";
=cut
		say "Cleaning the fastq";
		$exe = `/usr/bin/perl /home/ubuntu/pre_process/clean.pl -file $wd/R2_extracted.fastq.gz -protocol $protocol_temp -out $wd/$R2_name.clean.fastq`;
		say "done";

	
		say "gunzip the clean fastq";
		my $gz = `/bin/gzip $wd/$R2_name.clean.fastq`;
		say "gunzip done";
=cut
	}
	elsif($qc eq "no"){

		say "Identify correct cell barcodes";
        	$umi = `/home/ubuntu/anaconda3/bin/umi_tools whitelist --stdin $R1_path --bc-pattern=$protocol --set-cell-number=$sample_size --log2stderr > $wd/whitelist.txt`;
        	say "done";

        	say "Extract barcdoes and UMIs and add to read names";
        	$umi = `/home/ubuntu/anaconda3/bin/umi_tools extract --bc-pattern=$protocol --stdin $R1_path --stdout $wd/R1_extracted.fastq.gz --read2-in $R2_path --read2-out=$wd/R2_extracted.clean.fastq.gz --filter-cell-barcode --whitelist $wd/whitelist.txt`;
        	say "done";

	}


#------------------------------------ END OF QC ------------------------------------




#------------------------------------ ALIGNING THE READS ------------------------------------

	say "Aligning fastq";
	my $aln ="";

	if($species eq "M" || $species eq "m"){

		if($aligner eq "STAR"){
			$aln = `/usr/bin/STAR --runThreadN 6 --genomeDir /home/ubuntu/Databases/GRCm38/STAR/ --readFilesIn $wd/$R2_name.clean.fastq.gz --readFilesCommand zcat --outSAMattributes Standard --outFileNamePrefix $wd/$R2_name`;
			$species = "GRCm38";
		}

	
		elsif($aligner eq "hisat2"){
			$aln = `/home/ubuntu/bunga_tools/hisat2-2.1.0/hisat2 --phred33 -p 2 -x /home/ubuntu/Databases/GRCm38/hisat_index/GRCm38.primary_assembly.genome.fa.hisat2 -U $wd/$R2_name.clean.fastq.gz | awk '\$5 >= 60' > $wd/$R2_name.sam`;
                	$species = "GRCm38";
		}

	}
	
	elsif($species eq "H" || $species eq "h"){
	
		if($aligner eq "STAR"){
                       $aln = `/usr/bin/STAR --runThreadN 6 --genomeDir /home/ubuntu/Databases/GRCh38/STAR/ --readFilesIn $wd/$R2_name.clean.fastq.gz --readFilesCommand zcat --outSAMattributes Standard --outFileNamePrefix $wd/$R2_name`;
        		$species = "GRCh38";        
	}


                elsif($aligner eq "hisat2"){
                        $aln = `/home/ubuntu/bunga_tools/hisat2-2.1.0/hisat2 --phred33 -p 2 -x /home/ubuntu/Databases/GRCh38/hisat_index/GRCh38.primary_assembly.genome.fa.hisat2 -U $wd/$R2_name.clean.fastq.gz | awk '\$5 >= 60' > $wd/$R2_name.sam`;
	                $species = "GRCh38";

                }

        }
	
	
	say "Alignment done";


#------------------------------------ END OF ALIGNMENT ------------------------------------


#------------------------------------ POST-PROCESSING ------------------------------------
#Convert to BAM
	say "converting to BAM";

	my $star = "R2_extractedAligned.out.sam"; 
	if($aligner eq "hisat2"){
	$exe = `/home/ubuntu/anaconda3/bin//samtools view -@ 2 -T /home/ubuntu/Databases/$species/genome/$species.primary_assembly.genome.fa -bS $wd/$R2_name.sam > $wd/$R2_name.bam`;
	say "Done";
	}
	elsif($aligner eq "STAR"){
	$exe = `/home/ubuntu/anaconda3/bin//samtools view -@ 2 -T /home/ubuntu/Databases/$species/genome/$species.primary_assembly.genome.fa -bS $wd/$star > $wd/$R2_name.bam`;
	say "Done";
	}
#Sort the BAM
	say "\nSorting BAM";
	$exe = `/home/ubuntu/anaconda3/bin//samtools sort -T .  -m 2G  -@ 2 $wd/$R2_name.bam > $wd/$R2_name.sorted.bam`;
	say "Done";

#Add index to BAM
	say "\nadding index to BAM";
	$exe = `/home/ubuntu/anaconda3/bin//samtools index $wd/$R2_name.sorted.bam`;
	say "Done";

#Add gene tag
	say "\nadding gene tag";
	my $fc = "/home/ubuntu/bunga_tools/subread-1.6.4-source/bin/./featureCounts";
	if($species eq "GRCm38"){
		$exe = `$fc -R BAM  --tmpDir . -T 2  -F GTF  -a /home/ubuntu/Databases/$species/annotations/gencode.vM21.annotation.meta_genes.gtf -o $wd/$R2_name.sorted.bam.featureCounts $wd/$R2_name.sorted.bam`;
	}
	elsif($species eq "GRCh38"){
		$exe = `$fc -R BAM  --tmpDir . -T 2  -F GTF  -a /home/ubuntu/Databases/$species/annotations/gencode.v31.annotation.meta_genes.gtf -o $wd/$R2_name.sorted.bam.featureCounts $wd/$R2_name.sorted.bam`;
	}
	say "Done";

#sort and index again
	say "\nSort and index";
	$exe = `/home/ubuntu/anaconda3/bin//samtools sort -T . -m 2G -@ 2 $wd/$R2_name.sorted.bam.featureCounts.bam > $wd/$R2_name.sorted.bam.featureCounts.sorted.bam`;
	$exe = `/home/ubuntu/anaconda3/bin//samtools index $wd/$R2_name.sorted.bam.featureCounts.sorted.bam`;
	say "Done";


#Count the reads
	say "Count UMIs per gene per cell";
	$umi = `/home/ubuntu/anaconda3/bin/umi_tools count --per-gene --gene-tag=XT --assigned-status-tag=XS --per-cell --wide-format-cell-counts -I $wd/*featureCounts*sorted.bam -S $wd/counts.tsv.gz`;
	say "Done";
	
	say "extracting the counts matrix";
	`/bin/gunzip $wd/counts.tsv.gz`;

	`/home/ubuntu/miniconda3/bin/R --no-save --no-restore --slave --args $wd/counts.tsv $wd/$id.markers.csv < /home/ubuntu/pre_process/remove_geneid.R`;

if($pp eq "no"){
#gzip the file
        `/bin/gzip $wd/$id.markers.csv`;

#move the markers file
        `mv $wd/$id.markers.csv.gz /var/www/html/scw_page/scw_out/`;
my $new_file = "https://www.bhasinlab.us/scw_page/scw_out/".$id.".markers.csv.gz";

#Send email to the user
        `/home/ubuntu/anaconda3/bin//python3 /home/ubuntu/pre_process/smtp_ssl.py -to $to_email -linkout $new_file`;
        `rm -rfv $wd`;
}
elsif($pp eq "yes"){
my $csv = $wd.$id.".markers.csv";
`mv $csv /home/ubuntu/project/temp/`;


`rm -rf $wd*`;
`mv /home/ubuntu/project/temp/* $wd`;

#post processing
        `/home/ubuntu/miniconda3/bin//python3 /home/ubuntu/pre_process/post_process/scan.py -i $wd/$id.markers.csv -o $wd/post_process_out/ -sp $sp -mg $min_genes -mc $min_cells`;

#gzip the markers file
        `/bin/gzip $wd/$id.markers.csv`;

#gzip the working directory
	`cd $wd && /bin/tar -czf $id.out.tar.gz *`;
        #`/bin/tar -zcvf $id.out.tar.gz $wd`;
        `mv /home/ubuntu/project/$id.out.tar.gz /var/www/html/scw_page/scw_out/`;

#Send email to the user
my $new_file = "https://www.bhasinlab.us/scw_page/scw_out/".$id.".out.tar.gz";
`/home/ubuntu/anaconda3/bin//python3 /home/ubuntu/pre_process/smtp_ssl.py -to $to_email -linkout $new_file`;


#Delete the files
`rm -rfv $wd`;
}

#------------------------------------ END OF PROGRAM ------------------------------------
