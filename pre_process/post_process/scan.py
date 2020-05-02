import gseapy
import matplotlib
import matplotlib.pyplot as plt
import numpy as np
import pandas as pd
import scanpy as sc
import argparse
import re
import os

parser		= argparse.ArgumentParser()
parser.add_argument('-i','--infile', dest='infile', type=str)
parser.add_argument('-o','--outfile', dest='outdir', type=str)
parser.add_argument('-sp','--species', dest='species', type=str)
parser.add_argument('-mc', '--min_cells',dest="min_cell", type=int)
parser.add_argument('-mg', '--min_genes',dest="min_gene", type=int)

args 		= parser.parse_args()
infile		= args.infile
outdir          = args.outdir
species		= args.species
min_gene	= args.min_gene
min_cell	= args.min_cell

if species == 'M':
    species = 'mouse'
else:
    species = 'human'


sc.settings.verbosity = 3 
sc.settings.autoshow=False
sc.settings.figdir=outdir
sc.settings.autosave=True
sc.logging.print_versions()
results_file = outdir+"/"+'out.h5ad'


sc.settings.set_figure_params(dpi=300)

print("reading dataset")
adata = sc.read(infile, delimiter=",", cache=True).transpose()
print("done")

sc.pl.highest_expr_genes(adata, n_top=20, )
sc.pp.filter_cells(adata, min_genes=min_gene)
sc.pp.filter_genes(adata, min_cells=min_cell)
#print("min genes for cell" + str(min_gene))
#print("min cells for gene" + str(min_cell))

cell_constant = 12
print(cell_constant)

mito_genes = adata.var_names.str.startswith('MT-')
# for each cell compute fraction of counts in mito genes vs. all genes
# the `.A1` is only necessary as X is sparse (to transform to a dense array after summing)
adata.obs['percent_mito'] = np.sum(adata[:, mito_genes].X, axis=1) / np.sum(adata.X, axis=1)
# add the total counts per cell as observations-annotation to adata
adata.obs['n_counts'] = adata.X.sum(axis=1)

sc.pl.violin(adata, ['n_genes', 'n_counts', 'percent_mito'],jitter=0.4, multi_panel=True)
sc.pl.scatter(adata, x='n_counts', y='percent_mito')
sc.pl.scatter(adata, x='n_counts', y='n_genes')

adata = adata[adata.obs.n_genes < 2500, :]
adata = adata[adata.obs.percent_mito < 0.05, :]

#Normalize the data
sc.pp.normalize_total(adata, target_sum=1e4)

sc.pp.log1p(adata)

adata.raw = adata

#identify highly variable genes
sc.pp.highly_variable_genes(adata, min_mean=0.0125, max_mean=3, min_disp=0.5)

sc.pl.highly_variable_genes(adata)

adata = adata[:, adata.var['highly_variable']]

sc.pp.regress_out(adata, ['n_counts', 'percent_mito'])

sc.pp.scale(adata, max_value=10)

#PCA
sc.tl.pca(adata, svd_solver='arpack')

sc.pl.pca_variance_ratio(adata, log=True)

adata.write(results_file)
adata


#Neighborhood graph
sc.pp.neighbors(adata, n_neighbors=10, n_pcs=40)

sc.tl.umap(adata)

#Feature plots
sc.pl.umap(adata,size=cell_constant)
#plt.savefig("umap.pdf", format="pdf", dpi='figure')
#Clustering and neighborhood graph
sc.tl.louvain(adata)

sc.pl.umap(adata, color='louvain', size=cell_constant)

sc.tl.rank_genes_groups(adata, 'louvain', method='wilcoxon')
sc.pl.rank_genes_groups(adata, n_genes=25, sharey=False)

adata.write(results_file)


df = pd.DataFrame(adata.uns['rank_genes_groups']['names']).head(5)
index = df.index
coumns = df.columns
values = df.values

df_genes=list()
for i in range(0, len(df.columns)):
    col=str(i)
    df_genes.append(df[col])
    
result = adata.uns['rank_genes_groups']
groups = result['names'].dtype.names

for group in groups:
    group = group.split("_")[0]
    print(group)
#save top 50 markers for each cluster as csv
count=0
os.mkdir(outdir+'/rnk')
os.mkdir(outdir+'/label_data')
top = []
for group in groups:
    df = pd.DataFrame(
            {group + '_' + key[:1]: result[key.split("_")[0]][group]
            for key in ['names', 'pvals']}).head(50)
    rows = df[group+"_n"]
    rows = [name.split("_")[0] for name in rows]
    df[group+"_n"] = rows
    df[group+"_p"] = df[group+"_p"].astype('float')
    df.to_csv(outdir+'/rnk/'+str(count)+'.rnk', sep="\t", index=False, header=False)
    try:
        gseapy.prerank(outdir+'/rnk/'+str(count)+'.rnk', species+".gmt", outdir+'/label_data/'+str(count)+'_folder')
    except:
        pass
    if os.path.isfile(outdir+'/label_data/'+str(count)+'_folder/gseapy.prerank.gene_sets.report.csv'):
        this_top = pd.read_csv(outdir+'/label_data/'+str(count)+'_folder/'+'gseapy.prerank.gene_sets.report.csv', header=0)
        label = this_top['Term'].iloc[-1]
        top.append(str(label))
    else:
        top.append("unknown" + str(count))
    print(count)
    count+=1

#print('labels: ', top)
#sc.pl.umap(adata, color=top, size=cell_constant) 
  
export_de = pd.DataFrame(
            {group + '_' + key[:1]: result[key][group]
            for group in groups for key in ['names', 'pvals']}).head(50)

export_de.to_csv(outdir+'/'+'top_50.csv')


#Get top 10 markers from each cluster and append to list
top_5 = list()
for i in range(0,len(df_genes)):
    for j in range(0,5):
        top_5.append(df_genes[i][j])
 
adata.rename_categories('louvain', top)       
sc.pl.umap(adata, color='louvain', legend_loc='on data', legend_fontsize=8, save='_cluster_labelled.pdf',size=cell_constant)

sc.pl.rank_genes_groups_matrixplot(adata, n_genes=3, standard_scale='var', cmap='Blues')

sc.pl.rank_genes_groups_heatmap(adata, n_genes=3, standard_scale='var', cmap='brg', swap_axes='True')

sc.pl.heatmap(adata, top_5, groupby='louvain',swap_axes=True,save="_top5.pdf")

adata.write(results_file, compression='gzip')
adata.write_csvs(results_file[:-5], )

