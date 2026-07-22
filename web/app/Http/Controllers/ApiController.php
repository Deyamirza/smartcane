<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\SensorLog;
use App\Models\GpsLog;
use App\Models\SosEvent;
use App\Models\Notification;
use App\Models\User;

class ApiController extends Controller
{

    public function logData(Request $request)
    {

        $validated = $request->validate([

            'mac_address' => 'required|string',

            'distance_cm' => 'nullable|numeric',

            'latitude' => 'nullable|numeric',

            'longitude' => 'nullable|numeric',

            'sos_status' => 'nullable|string|in:active,inactive',

        ]);



        /*
        |--------------------------------------------------------------------------
        | DEVICE
        |--------------------------------------------------------------------------
        */


        $device = Device::where(
            'mac_address',
            $validated['mac_address']
        )->first();



        if(!$device){

            return response()->json([

                'status'=>'failed',

                'message'=>'Device tidak ditemukan'

            ],404);

        }



        if($device->status !== 'active'){

            return response()->json([

                'status'=>'failed',

                'message'=>'Device tidak aktif atau dinonaktifkan'

            ],403);

        }



        $response = [

            'status'=>'success',

            'logged'=>[]

        ];




        /*
        |--------------------------------------------------------------------------
        | SENSOR DATA
        |--------------------------------------------------------------------------
        */


        if(isset($validated['distance_cm'])){


            $distance = $validated['distance_cm'];


            SensorLog::create([

                'id_device'=>$device->id_device,

                'distance_cm'=>$distance,

                'obstacle_detected'=>
                    ($distance > 0 && $distance <=100)
                    ? 'yes'
                    : 'no',

                'recorded_at'=>now()

            ]);


            $response['logged'][]='sensor_log';


        }




        /*
        |--------------------------------------------------------------------------
        | GPS DATA
        |--------------------------------------------------------------------------
        */


        if(

            isset($validated['latitude']) &&

            isset($validated['longitude']) &&

            $validated['latitude'] != 0 &&

            $validated['longitude'] != 0

        ){


            GpsLog::create([

                'id_device'=>$device->id_device,

                'latitude'=>$validated['latitude'],

                'longitude'=>$validated['longitude'],

                'accuracy_m'=>3,

                'recorded_at'=>now()

            ]);



            $response['logged'][]='gps_log';


        }




        /*
        |--------------------------------------------------------------------------
        | SOS PROCESS
        |--------------------------------------------------------------------------
        */


        if(isset($validated['sos_status'])){


            /*
            |--------------------------------------------------------------------------
            | SOS ACTIVE
            |--------------------------------------------------------------------------
            */


            if($validated['sos_status']=='active'){


                $alreadyActive = SosEvent::where(

                    'id_device',

                    $device->id_device

                )
                ->where(
                    'status',
                    'active'
                )
                ->exists();



                if(!$alreadyActive){



                    /*
                    Ambil GPS terakhir
                    */


                    $gps = GpsLog::where(

                        'id_device',

                        $device->id_device

                    )
                    ->orderBy(
                        'recorded_at',
                        'desc'
                    )
                    ->first();





                    $latitude = 
                    $validated['latitude']
                    ?? 
                    ($gps->latitude ?? null);



                    $longitude =
                    $validated['longitude']
                    ??
                    ($gps->longitude ?? null);





                    /*
                    Jangan pakai koordinat default
                    */


                    if(

                        $latitude===null ||

                        $longitude===null

                    ){

                        return response()->json([

                            'status'=>'failed',

                            'message'=>'GPS belum FIX'

                        ],400);

                    }





                    $sos = SosEvent::create([


                        'id_device'=>$device->id_device,


                        'latitude'=>$latitude,


                        'longitude'=>$longitude,


                        'status'=>'active',


                        'triggered_at'=>now()


                    ]);





                    /*
                    Telegram
                    */


                    $messageId =
                    \App\Services\TelegramService::sendSosAlert(

                        $device,

                        $latitude,

                        $longitude,

                        $sos->id_sos

                    );




                    if($messageId){


                        $sos->update([

                            'telegram_message_id'=>$messageId

                        ]);

                    }






                    foreach(User::all() as $user){


                        Notification::create([

                            'id_sos'=>$sos->id_sos,

                            'id_user'=>$user->id_user,

                            'telegram_chat_id'=>env(
                                'TELEGRAM_CHAT_ID'
                            ),

                            'delivery_status'=>
                                $messageId
                                ?
                                'sent'
                                :
                                'failed',

                            'sent_at'=>now()

                        ]);


                    }




                    $response['logged'][]='sos_activated';


                }


            }




            /*
            |--------------------------------------------------------------------------
            | SOS OFF
            |--------------------------------------------------------------------------
            */


            else {



                $events = SosEvent::where(

                    'id_device',

                    $device->id_device

                )
                ->where(
                    'status',
                    'active'
                )
                ->get();




                foreach($events as $sos){


                    \App\Services\TelegramService::resolveSosAlert(

                        $device,

                        $sos

                    );



                    $sos->update([

                        'status'=>'resolved',

                        'resolved_at'=>now()

                    ]);

                }



                $response['logged'][]='sos_deactivated';


            }


        }




        return response()->json($response);


    }

}