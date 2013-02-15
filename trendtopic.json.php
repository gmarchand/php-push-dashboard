<?php

class trendtopic {
	
	//http://fr.dbpedia.org/sparql
	//http://dbpedia.org/sparql
	//http://data.linkedmdb.org/sparql
	
	
/* /dbpedia
 * SELECT DISTINCT ?filmName WHERE {
    ?film foaf:name ?filmName .
    ?film dbpedia-owl:starring ?actress .
    ?actress foaf:name ?name.
    FILTER(regex(?name, "Nicole.*Kidman.*", "i"))
}
 */
	function findMovie(string $title){
		$sparql = "
PREFIX m: <http://data.linkedmdb.org/resource/movie/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

SELECT ?actorName ?directorName ?filmTitle WHERE {
  ?film rdfs:label ?filmTitle;
        m:director ?dir;
        m:actor ?actor.
  ?film rdfs:label "Toy Story".
  ?actor m:actor_name ?actorName.
  ?dir m:director_name ?directorName.
}
";
		
	}
}

?>