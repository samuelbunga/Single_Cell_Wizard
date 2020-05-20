args <- commandArgs(trailingOnly = TRUE)

infile <- read.table(args[1])
outfile <- args[2]
min_bc <- as.integer(args[3])

a <- which(table(unname(infile)) >= min_bc )
write.table(names(a), file=outfile, row.names=F, quote=F, col.names=F)



