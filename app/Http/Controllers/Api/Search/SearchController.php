<?php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\FlightSchedule as FlightScheduleResource;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use thiagoalessio\TesseractOCR\TesseractOCR;
use DateTime;

use App\Models\Airports;
use App\Models\DarmawisataSession;
use App\Models\DarmawisataRequest;
use App\Models\FlightBookingSession;
use App\Models\FlightTripSession;
use App\Models\FlightPassengerSession;
use App\Models\FlightAddonsSession;
use App\Models\FlightDetailsSession;

use App\Models\FlightBooking;
use App\Models\FlightTrip;
use App\Models\FlightPassenger;
use App\Models\FlightAddons;
use App\Models\FlightAddonsMeal;
use App\Models\FlightBookingDetails;
use App\Models\Passengers;
use App\Models\PaymentMethod;


class SearchController extends Controller
{
    public function __construct()
    {
        $this->username="HAYQ18MKPK";
        $this->password="HAQQQ8MKPK";
        $this->client = new Client([
            'verify' => false,
            'base_uri' => 'https://61.8.74.42:7080/h2h/',
            'timeout'  => 150.0
        ]);
    }

    public function searchAll(Request $request)
    {
        
    }
}
