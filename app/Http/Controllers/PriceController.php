<?php

namespace App\Http\Controllers;

use DB;
use App\Location;
use App\Price;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class PriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($location_id)
    {
        if ( $location_id ) {
            $prices = Price::where('location_id', $location_id);
        } else {
            $prices = Price::all();
        }
        
        return view('prices.index', compact('prices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($location_id)
    {
        $location = Location::find($location_id);
        
        return view('prices.create', compact('location'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store($location_id, Request $request)
    {
        $location = Location::find($location_id);

        // create the new Location
		$price = new Price();
        $price->type = $request->type;
        $price->price = $request->price;
        $price->price_date = $request->price_date;

        $price->location()->associate($location);

        $price->save();

        return redirect()->action('LocationController@show', $price->location->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($location_id, $id)
    {
        return view('prices.show', [ 'price' => Price::find($id) ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($location_id, $id)
    {
        return view('prices.edit', [ 'price' => Price::find($id) ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $location_id, $id)
    {
        $location = Location::find($location_id);
        $price = Price::find($id);

        $price->type = $request->type;
        $price->price = $request->price;
        $price->price_date = $request->price_date;

        $price->location()->associate($location);

        $price->save();

        return redirect()->action('LocationController@show', $price->location->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function stats($method)
    {
        switch ($method) {
            case 'average-sales':
                $return = Price::where('type', 2)
                               ->select(DB::raw('AVG(price) AS amount, MONTH(`price_date`) AS month'))
                               ->groupBy(DB::raw('MONTH(`price_date`)'))
                               ->get();
                break;
            case 'count-sales':
                $return = Price::where('type', 2)
                               ->select(DB::raw('COUNT(*) AS count, MONTH(`price_date`) AS month'))
                               ->groupBy(DB::raw('MONTH(`price_date`)'))
                               ->get();
                break;
            case 'count-listing':
                $return = Price::where('type', 1)
                    ->select(DB::raw('COUNT(*) AS count, MONTH(`price_date`) AS month'))
                    ->groupBy(DB::raw('MONTH(`price_date`)'))
                    ->get();
                break;
            case 'list-sales':
                $return = Price::where('prices.type', 2)
                    ->with('location')
                    ->orderBy('price_date', 'desc')
                    ->limit(10)
                    ->get();
                break;
            default:
                $return = [];
                break;
        }
        
        return response()->json( $return );
    }
}
