<?php

namespace App\Http\Controllers\Api\v1\Weather;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WeatherController extends Controller
{
    /**
    * Displays the weather based on a VAT number
    */
    public function Index(Request $request)
    {
        $country = $request->input('country', 'dk');
        $vat = $request->input('vat');

        $apiUrl = "https://cvrapi.dk/api?country={$country}&search={$vat}";

        // Initialize cURL session for the CVR API
        $cvrCurl = curl_init($apiUrl);
        curl_setopt($cvrCurl, CURLOPT_URL, $apiUrl);
        curl_setopt($cvrCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cvrCurl, CURLOPT_USERAGENT, 'CleanManager project');

        // Execute cURL session for the CVR API
        $cvrResponse = curl_exec($cvrCurl);

        // Check if the cURL request was successful
        if ($cvrResponse !== false) {
            // Decode the JSON response from the CVR API
            $cvrData = json_decode($cvrResponse, true);

            // Get the city from the CVR API response
            $city = $cvrData['city'];

            $weatherUrl = "https://vejr.eu/api.php?location={$city}&degree=C"; 

            $weatherResponse = Http::get($weatherUrl);

            // Check if the weather cURL request was successful
            if ($weatherResponse !== false) {
                // Decode the JSON response from the weather API
                $weatherData = json_decode($weatherResponse, true);
                
                // Return both API responses
                return response([

                    "success" => true,
                    "data" => [
                        "city" => $city,
                        "temperature" => $weatherData['CurrentData']['temperature'] . "°C",
                        "skyText" => $weatherData['CurrentData']['skyText'],
                        "humidity" => "Humidity: " . $weatherData['CurrentData']['humidity'] . "%",
                        "windText" => "Wind: " . $weatherData['CurrentData']['windText'],
                    ]
                ], 200);
            } else { 
                // If weather http request fails, return an error response
                return response()->json(['error' => 'Failed to fetch data from the weather API']);
            }
        } else {
            // If cvrapi cURL request fails, return an error response
            return response()->json(['error' => 'Failed to fetch data from the CVR API']);
        }
    }
}