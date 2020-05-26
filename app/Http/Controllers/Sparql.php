<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Sparql extends Controller
{
    //

    public function getLocation($location) {

        $client = new \GuzzleHttp\Client();
        $headers = ['Accept' => 'application/sparql-results+json'];
        $sparql = 'query=select *{?name a dbo:Place;foaf:name "'.$location.'"@en ;geo:lat ?lat;geo:long ?long}';
//        echo $sparql;
//        $sparql = 'query=@Prefix lgdo: <http://linkedgeodata.org/ontology/>.@Prefix geom: <http://geovocab.org/geometry#>.'.
//                  '@Prefix ogc:<http://www.opengis.net/ont/geosparql#>.Select * From <http://linkedgeodata.org>{?s a lgdo:Amenity;'.
//                   'rdfs:label ?l;geom:geometry [ogc:asWKT ?g ] .Filter(bif:st_intersects (?g, bif:st_point (12.372966, 51.310228), 0.1)).}}';

// Send an asynchronous request.
        $res = $client->get("http://www.dbpedia.org/sparql?".$sparql, ["headers" => $headers]);

        $responseString = $res->getBody()->getContents();
        $responseString = str_replace("php"," ",$responseString);
        $responseJson = \GuzzleHttp\json_decode($responseString);
        $code = $res->getStatusCode();

        if($code == 200) {
            $list = [];
            $name = $responseJson->results;
            if( empty($name) ) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Location not found'
                ], 200);
            }
            $name = $name->bindings;
            $count = 0;
            foreach ($name as $n) {
                $val = str_replace("http://dbpedia.org/resource/", "", $n->name->value);
                $new = [
                    'id' => $count,
                    'name' => str_replace('_', " ", $val),
                    'longitude' => $n->long->value,
                    'latitude' => $n->lat->value
                ];
                array_push($list, $new);
                $count++;
            }
            return response()->json([
                'status' => 200,
                'message' => 'Location found',
                'body' => $list
            ], 200);
        }

        return response()->json([
            'status' => 400,
            'message' => 'Search failed'
        ], 200);
    }

}
