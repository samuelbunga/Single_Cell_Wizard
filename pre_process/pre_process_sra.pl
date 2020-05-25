
#!/usr/bin/perl
#use warnings;
use strict;
use feature qw(say);
use Getopt::Long;

=cut
Program:	Pre-processing of SRA fastq
Input:  	SRA fastq file
Output:		Counts matrix
Author:		Samuel Bunga
Contact:	bunga.s@husky.neu.edu
=cut


GetOptions (	"sp=s" 		=> \my $species,    	#M or H
              	"file=s"   	=> \my $data, 		# path to the file
              	"out=s"  	=> \my $name,		#name of the project
		"protocol=s" 	=> \my $protocol,	# protocol type
		"bclen=i" 	=> \my $bc_len, 	#bc length
		"umi=i"		=> \my $umi_len,	#umi length
		"sample_size=i"	=> \my $sample_size,
		"email=s"	=> \my $to_email,
		"aligner=s"	=> \my $aligner,
		"post_process=s"=> \my $pp,
		"id=s"		=> \my $id,
		"min_cells=i"	=> \my $min_cells,
		"min_genes=i"	=> \my $min_genes,
		"qc=s"		=> \my $qc,
		
	)
  or die("Error in command line arguments\n");

my $sp = $species;
#Check if name is provided
if(defined $name){}else{die "Please provide -out name"}
my $dir = $name . "/";
unless (-e $dir){
	`mkdir -p $dir`;
	}


#Creating the path variable
my $wd = $name . "/";


my ($SRA, $SRA_PATH);

if($protocol eq "SRA" || $protocol eq "sra"){
	#Getting the file name
	$SRA = `cd $data && find -type f -iname "*sra*" -printf "%f"`;
        chomp $SRA;
	$SRA_PATH = join("/", $data, $SRA);
	}
else{die "Killed, cannot proceed!";}


#read the species
if(($species eq "H")||($species eq "h")){
	$species = "GRCh38";
}
elsif(($species eq "M")||($species eq "m")){
	$species = "GRCm38";
}


#Dump FastQ
say "Dumping the SRA to fastq";
`/usr/bin/fastq-dump $SRA_PATH -O $wd`;


#Get the dumped fastq file
if($SRA =~ m/(\w+)(\.+)/){
	$SRA = $1;
	}

#------------------------------------ Quality Check ------------------------------------

#Move the Barcode and UMI to header
say "Moving the Barcode and UMI to the header";	
`/usr/bin/perl  /home/ubuntu/pre_process/demux.pl -file $wd/$SRA.fastq  -out $wd/$SRA.s1.qc.fastq -bclen $bc_len -umi $umi_len`;

#Quality check using FastP
if($qc == "yes"){
say "Running Fastp to clean the fastq";
my $fastp = `/home/ubuntu/anaconda3/bin/fastp  -i $wd/$SRA.s1.qc.fastq -o $wd/$SRA.clean.fastq`;

#Remove the low quality reads
say "Removing the low quality reads";
`/usr/bin/perl /home/ubuntu/pre_process/clean_fastq.pl -file $wd/$SRA.clean.fastq -umi $umi_len -bc_len $bc_len -wd $wd -min_bc $sample_size`;
}
else{
say "Skipping QC";
say "Removing the low quality reads";
`/usr/bin/perl /home/ubuntu/pre_process/clean_fastq.pl -file $wd/$SRA.s1.qc.fastq -umi $umi_len -bc_len $bc_len -wd $wd -min_bc $sample_size`;
}

#------------------------------------ END OF QC ------------------------------------

#------------------------------------ ALIGN THE READS ------------------------------------


#Align the FastQ file
say "Aligning the FastQ";
my $aln="";
 if($species eq "GRCm38"){

                if($aligner eq "STAR"){
                        $aln = `/usr/bin/STAR --runThreadN 6 --genomeDir /home/ubuntu/Databases/GRCm38/STAR/ --readFilesIn $wd/clean_filtered.fastq --outSAMattributes Standard --outFileNamePrefix $wd/$SRA`;
                }


                elsif($aligner eq "hisat2"){
                        $aln = `/home/ubuntu/bunga_tools/hisat2-2.1.0/hisat2 --phred33 -p 2 -x /home/ubuntu/Databases/GRCm38/hisat_index/GRCm38.primary_assembly.genome.fa.hisat2 -U $wd/clean_filtered.fastq | awk '\$5 >= 60' > $wd/$SRA.sam`;
                }

        }

elsif($species eq "GRCh38"){

                if($aligner eq "STAR"){
                       $aln = `/usr/bin/STAR --runThreadN 6 --genomeDir /home/ubuntu/Databases/GRCh38/STAR/ --readFilesIn $wd/clean_filtered.fastq --outSAMattributes Standard --outFileNamePrefix $wd/$SRA`;
        }


                elsif($aligner eq "hisat2"){
                        $aln = `/home/ubuntu/bunga_tools/hisat2-2.1.0/hisat2 --phred33 -p 2 -x /home/ubuntu/Databases/GRCh38/hisat_index/GRCh38.primary_assembly.genome.fa.hisat2 -U $wd/clean_filtered.fastq | awk '\$5 >= 60' > $wd/$SRA.sam`;
                }

        }




=cut
if($species eq "GRCm38"){
	
	$aln = `/home/ubuntu/bunga_tools/hisat2-2.1.0/hisat2 --phred33 -p 2 -x /home/ubuntu/Databases/GRCm38/hisat_index/GRCm38.primary_assembly.genome.fa.hisat2 -U $wd/clean_filtered.fastq | awk '\$5 >= 60' > $wd/$SRA.sam`;
}
elsif($species eq "GRCh38"){
	
	$aln = `/home/ubuntu/bunga_tools/hisat2-2.1.0/hisat2 --phred33 -p 2 -x /home/ubuntu/Databases/GRCh38/hisat_index/GRCh38.primary_assembly.genome.fa.hisat2 -U $wd/clean_filtered.fastq | awk '\$5 >= 60' > $wd/$SRA.sam`;

}
=cut
#------------------------------------ END OF ALIGNMENT ------------------------------------

#------------------------------------ POST-PROCESSING ------------------------------------
#Convert to BAM
say "Converting to BAM";
my $exe;

my $star = join("", $SRA, "Aligned.out.sam");
        if($aligner eq "hisat2"){
        $exe = `/home/ubuntu/anaconda3/bin//samtools view -@ 2 -T /home/ubuntu/Databases/$species/genome/$species.primary_assembly.genome.fa -bS $wd/$SRA.sam > $wd/$SRA.bam`;
        say "Done";
        }
        elsif($aligner eq "STAR"){
        $exe = `/home/ubuntu/anaconda3/bin//samtools view -@ 2 -T /home/ubuntu/Databases/$species/genome/$species.primary_assembly.genome.fa -bS $wd/$star > $wd/$SRA.bam`;
        say "Done";
        }


#Sort the BAM
say "Sorting the BAM";
$exe = `time /home/ubuntu/anaconda3/bin//samtools sort -T .  -m 2G  -@ 2 $wd/$SRA.bam > $wd/$SRA.sorted.bam`;


#Add index to BAM
say "Adding the index to BAM";
$exe = `time /home/ubuntu/anaconda3/bin//samtools index $wd/$SRA.sorted.bam`;

#Add gene tag
say "Adding gene tag";
my $fc = "/home/ubuntu/bunga_tools/subread-1.6.4-source/bin/./featureCounts";

if($species eq "GRCm38"){
$exe = `time $fc -R BAM  --tmpDir . -T 2  -F GTF  -a /home/ubuntu/Databases/$species/annotations/gencode.vM21.annotation.meta_genes.gtf -o $wd/$SRA.sorted.bam.featureCounts $wd/$SRA.sorted.bam`;
}
elsif($species eq "GRCh38"){
$exe = `time $fc -R BAM  --tmpDir . -T 2  -F GTF  -a /home/ubuntu/Databases/$species/annotations/gencode.vH21.annotation.meta_genes.gtf -o $wd/$SRA.sorted.bam.featureCounts $wd/$SRA.sorted.bam`;
}

#sort and index again
say "Sorting and indexing the BAM";
$exe = `time /home/ubuntu/anaconda3/bin//samtools sort -T . -m 2G -@ 2 $wd/$SRA.sorted.bam.featureCounts.bam > $wd/$SRA.sorted.bam.featureCounts.sorted.bam`;
$exe = `time /home/ubuntu/anaconda3/bin//samtools index $wd/$SRA.sorted.bam.featureCounts.sorted.bam`;


#Perform UMI duplication
say "Performing UMI duplication";
$exe = `time /home/ubuntu/anaconda3/bin/umi_tools dedup --no-sort-output --method unique --gene-tag=XT --per-gene --per-cell --log2stderr -I $wd/$SRA.sorted.bam.featureCounts.sorted.bam > $wd/$SRA.sorted.bam.featureCounts.sorted.umi_dedup.bam`;

#Sort and index final time
say "Sorting and Indexing again";
$exe = `time /home/ubuntu/anaconda3/bin//samtools sort -T . -m 2G -@ 2 $wd/$SRA.sorted.bam.featureCounts.sorted.umi_dedup.bam > $wd/$SRA.sorted.bam.featureCounts.sorted.umi_dedup.sorted.bam`;
$exe = `/home/ubuntu/anaconda3/bin//samtools index $wd/$SRA.sorted.bam.featureCounts.sorted.umi_dedup.sorted.bam`;

#Count the reads
say "Count the reads";
$exe = `time /home/ubuntu/anaconda3/bin/umi_tools count --per-gene --gene-tag=XT --per-cell --wide-format-cell-counts -I $wd/$SRA.sorted.bam.featureCounts.sorted.umi_dedup.sorted.bam -S $wd/counts.tsv.gz`;

say "extracting the counts matrix";
`/bin/gunzip $wd/counts.tsv.gz`;


`/home/ubuntu/miniconda3/bin/R --no-save --no-restore --slave --args $wd/counts.tsv $wd/$id.markers.csv < /home/ubuntu/pre_process/remove_geneid.R`;
say "Done";

if($pp eq "no"){
#gzip the file
        `cd $wd && /bin/gzip $id.markers.csv`;

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
	#`cd $wd && /bin/gzip $id.markers.csv`;

#gzip the working directory
	`cd $wd && /bin/tar -czf $id.out.tar.gz * `;
        #`/bin/tar -zcvf $id.out.tar.gz $wd`;
	`mv $wd/$id.out.tar.gz /var/www/html/scw_page/scw_out/`; 

#Send email to the user
my $new_file = "https://www.bhasinlab.us/scw_page/scw_out/".$id.".out.tar.gz";
`/home/ubuntu/anaconda3/bin//python3 /home/ubuntu/pre_process/smtp_ssl.py -to $to_email -linkout $new_file`;


#Delete the files
`rm -rfv $wd`;
}
#------------------------------------ END OF PROGRAM ------------------------------------
