#!/usr/bin/perl
#use warnings;
use strict;
use feature qw(say);
use Getopt::Long;

GetOptions(
		"file=s" 	=> \my $infile,
		"bclen=i"	=> \my $bc_len,
		"out=s"		=> \my $outfile,
		"umi=i"		=> \my $umi_len,
	  );


my(@A,@T,@G,@C);

for(my $i = 1; $i <= $bc_len; $i++){
        push @A,"A";
        push @T,"T";
        push @G,"G";
        push @C,"C";

}


my $A = join("",@A);
my $T = join("",@T);
my $G = join("",@G);
my $C = join("",@C);


my $infh;	
unless(open($infh, "<", $infile)) {
	die "Cant read the infile";
	}

my (@lines, @null, $process, $bc, $umi, $out, $quality, @temp);
my $count = 0;

while(<$infh>){
	chomp $_;
	$count++;
	push @lines, $_;
	if($count == 4){
		$lines[0] 	=~ s/(\s+.*)//g;
		$lines[2] 	=~ s/(\s+.*)//g;
		$bc 		= substr($lines[1],0, $bc_len);
		$umi 		= substr($lines[1], $bc_len, $umi_len);
		$lines[3]	=~ s/^.{0,20}//g;
		$lines[1]	=~ s/$bc.*$umi//g;
		$lines[2] 	= join("_", $lines[2], $bc, $umi);
		$lines[0] 	= join("_", $lines[0], $bc, $umi); 
		$process 	= join("\n", @lines);
		
		if($lines[0] =~ /($A)/ || $lines[0] =~ /($T)/ || $lines[0] =~ /($G)/ || $lines[0] =~ /($C)/){
                $count = 0;
                @lines = @null;
	        $process = "";
		$count = 0;
			}
		if(length($lines[3]) != length($lines[1])){
                $count = 0;
		@lines = @null;
                $process = "";
                $count = 0;
			}
		else{
                open $out, '>>:encoding(UTF-8)', "$outfile";
                say $out $process;
                $process = "";
		@lines	 = @null;
	        $count = 0;
                }

	
	}
	

}

