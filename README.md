# topicanalysis
Topic analysis with the help of systematic mapping study and latent dirichlet allocation

These are the source files for topic analysis with the help of systematic mapping study
and latent dirichlet allocation on a set of scientific articles for a scientific field's
category which could be category="Parsing" for scientific field="compiler building".
The prerequisite for latent dirichlet allocation the latent dirichlet allocation package
for "R" which is described in https://cran.r-project.org/web/packages/lda/lda.pdf.
This is in short what the tool does: It fetches scientific articles attributes like
"author", title, "conference", "year", "abstracttext" and "fulltext" to its table
"search_results" for a "topic analysis environment". Then the values for "abstracttext"
and "fulltext" must be preprocessed. Then an "environment for latent dirichlet allocation"
must be created for a "topic analysis environment". Then a selection of scientific articles
leads to the input of the execution of latent dirichlet allocation. The execution consists of
fetching the selected input fulltexts or abstracttexts to the input folder for the latent
dirichlet allocation. Then latent dirichlet allocation must be started with a R-script
that was prepared by this tool before while executing "latent dirichlet allocation environment"
before. After "R" is finished with latent dirichlet allocation computations an Output-HTML-file
can be used to embed in a webpage.