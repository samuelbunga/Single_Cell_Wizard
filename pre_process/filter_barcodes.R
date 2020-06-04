args <- commandArgs(trailingOnly = TRUE)

filtered <- head(sort(table(unname(read.table(args[1]))), decreasing=T), as.integer(args[3]))
outfile <- args[2]

write.table(names(filtered), file=outfile, row.names=F, quote=F, col.names=F)



