<?php

namespace App\Http\Controllers;

use Foo\DataProviderIssue2922\SecondHelloWorldTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Phaza\LaravelPostgis\Geometries\LineString;
use Phaza\LaravelPostgis\Geometries\Point;
use App\Address;
use Phaza\LaravelPostgis\Geometries\Polygon;

class AddressController extends Controller
{

    public function createPolygon(Request $request) {

        $coords = $request['coords'];
        $userId = 1;
        $polygon = [];

        foreach ($coords as $point) {
            array_push($polygon, new Point($point['latitude'], $point['longitude']));
        }
        array_push($polygon, new Point($coords[0]['latitude'], $coords[0]['longitude']));
        $polygon = new LineString($polygon);
        logger()->debug($polygon);

        $polygon = new Polygon([$polygon]);

        $location = DB::table('_216district')->whereRaw('ST_Contains(geom, ST_GeomFromText(?, 4326))', [$polygon->toWKT()])->first();


        $duplicate = Address::whereRaw('ST_Intersects(polygon, ST_GeographyFromText(?))', [$polygon->toWKT()])->count();

        if( $duplicate > 0 ) {
            return response()->json([
                'status' => 400,
                'message' => 'Duplicate address. Please modify the boundary markers'
            ]);
        }

        $address = new Address();
        $address->user_id = $userId;
        $address->name = $request['name'];
        $address->type = $request['type'];
        $address->region = $location->region;
        $address->district = $location->district;
        $address->polygon = $polygon;
        $address->digital_address = $this->generateDigitalAddress($location->region,$location->dist_code);
        $address->save();

        return response()->json([
            'status' => 200,
            'message' => 'Digital Address generated successfully',
            'body' => [
                'name' => $address->name,
                'type' => $address->type,
                'region' => $address->region,
                'district' => $address->district,
                'digital_address' => $address->digital_address
                ]
        ]);
    }

    public function generateDigitalAddress($region, $district) {

        if(strcasecmp('Greater Accra', $region) == 0) {
            $region = 'GA';
        }else if(strcasecmp('Central', $region) == 0) {
            $region = 'CE';
        }else if(strcasecmp('Ashanti', $region) == 0) {
            $region = 'AS';
        }else if(strcasecmp('Volta', $region) == 0) {
            $region = 'VO';
        }else if(strcasecmp('Eastern', $region) == 0) {
            $region = 'EA';
        }else if(strcasecmp('Western', $region) == 0) {
            $region = 'WE';
        }else if(strcasecmp('Upper East', $region) == 0) {
            $region = 'UE';
        }else if(strcasecmp('Upper West', $region) == 0) {
            $region = 'UW';
        }else if(strcasecmp('Northern', $region) == 0) {
            $region = 'NO';
        }else if(strcasecmp('Brong Ahafo', $region) == 0) {
            $region = 'BA';
        }

        do{
            $code = rand(1, 100000);
            $digital = $region.'-'.$district.'-'.$code;

            $duplicate = Address::where('digital_address', $digital)->count();

        }while($duplicate);
        return $digital;
    }

    public function getUserAddresses($userId) {

        logger()->debug($userId);
        $addresses = Address::select('name', 'id', 'type', 'digital_address')->where('user_id','=', $userId)->get();

        if(!empty($addresses)) {

            return response()->json([
                'status' => 200,
                'message' => 'User addresses found',
                'body' => $addresses
            ]);
        }else {
            return response()->json([
                'status' => 400,
                'message' => 'No saved addresses',
            ]);
        }
    }

    public function getAddress($digital_address) {
        $location = Address::where('digital_address','=', $digital_address)->first();
//        $centercoord = Address::selectRaw('ST_AsText(ST_Centroid(polygon))')->where('digital_address', '=', $digital_address)->get();
////
//        $centercoord = substr($centercoord,6);
//        $centercoord = substr($centercoord,-1);
//        $coords = explode(" ", $centercoord);

        if(!empty($location)) {
            return response()->json([
                'status' => 200,
                'message' => 'Address found',
                'body' => $location,
//                'centerCoord' => $centercoord

            ]);
        }
        return response()->json([
            'status' => 400,
            'message' => 'Address not found',
        ]);

    }

    public function getAllAddresses() {
        $addresses = Address::all();
        if(!empty($addresses)) {
            return response()->json([
                'status' => 200,
                'message' => 'Address found',
                'body' => $addresses
            ]);
        }
        return response()->json([
            'status' => 400,
            'message' => 'No address not found',
        ]);

    }
}
