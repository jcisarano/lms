<?php

namespace App\Models\Aclamate;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;
//use Illuminate\Support\Facades\Storage;

class Aclamate extends Model
{
    const ACLAMATE_DOMAIN = "triage.aptima.com";
    const ACLAMATE_URL = "https://triage.aptima.com/api";
    const LOGIN_ENDPOINT = "/authentication/login";
    const LOGOUT_ENDPOINT = "/authentication/logout";
    const METRIC_ENDPOINT = "/data/feature/%s/%s/current";
    const SENSORKIT_ENDPOINT = "/sensorkits/name/%s";
    const SENSORKIT_ALL_ENDPOINT = "/sensorkits";
    const PERSON_ENDPOINT = "/persons/sensorkit/%s";
    const EVENT_ENDPOINT = "/rpc/data/event";
    const PERSON_INFO_ENDPOINT = "/persons/info/%s";
    
    public function LogIn($username,$password,$rememberme=true)
    {
        $endpoint = Aclamate::ACLAMATE_URL . Aclamate::LOGIN_ENDPOINT;

        $client = new \GuzzleHttp\Client();
        $jar = new \GuzzleHttp\Cookie\CookieJar;

        try
        {    
            $response = $client->request("POST",$endpoint,
                [
                    "form_params"=>[
                        "username"=>$username,
                        "password"=>$password,
                        "rememberMe"=>$rememberme
                        ],
                    "http_errors"=>false,
		    "cookies" => $jar,
                    "curl"=>[
                        CURLOPT_SSL_CIPHER_LIST=>"DEFAULT@SECLEVEL=1"
                    ]
                ]
            );            
        } catch (\GuzzleHttp\Exception\RequestException $e)
        {
            Log::error("Aclamate LogIn Error: " . $e->getMessage());
        }catch (\GuzzleHttp\Exception\ConnectException $e)
        {
            Log::error("Unable to connect to {$endpoint}, Error message: " . $e->getMessage());
        }
        
        $sessionToken = "";
        $bSuccess = false;
        if(isset($response) && $response->getStatusCode() == 200)
        {
            $token = $jar->getCookieByName("JSESSIONID");
            if(!is_null($token))
            {
                $sessionToken = $token->getValue();
            }
            return array("success"=>true,"token"=>$sessionToken);             
        }
        elseif (isset($response))
        {
            $content = trim($response->getBody()->getContents());
            return array("success"=>false,"token"=>"","message"=>$content); 
        }    
        else
        {
            return array("success"=>false,"token"=>"","message"=>"Unable to connect to {$endpoint}"); 
        }
    }
    
    public function LogOut()
    {
        $endpoint = Aclamate::ACLAMATE_URL . Aclamate::LOGOUT_ENDPOINT;
        
        $client = new \GuzzleHttp\Client();
        try
        {
            $response = $client->request("GET",$endpoint,
                [
                    "curl"=>[
                        CURLOPT_SSL_CIPHER_LIST=>"DEFAULT@SECLEVEL=1"
                    ]
                ]
            );
        } catch (\GuzzleHttp\Exception\RequestException $e)
        {
            Log::error("Aclamate LogOut Error: " . $e->getMessage());
        }
        
        $bodyContents = $response->getBody()->getContents();
        $logoutMessage = "logged out";
        $bSuccess = false;
        if (strcasecmp($bodyContents, $logoutMessage) == 0)
        {
            $bSuccess = true;
        }
            
        return array("success"=>$bSuccess); 
    }
    
    /**
      * Check if server is online
      * @return: Current epoch timestamp in milliseconds
      */
    public function Lifesign()
    {
        $endpoint = Aclamate::ACLAMATE_URL;
        $value = $this->GetData(NULL,$endpoint);

        if(is_numeric($value))
        {
            return array("success"=>true, "timestamp"=>$value);  
        }
        
        return array("success"=>false); 
    }
    
    /*
        Calculate average round trip to Aclamate server
    */
    public function GetRemoteServerTimeOffset($token, $numPings = 5)
    {
        $endpoint = Aclamate::ACLAMATE_URL;
        $client = new \GuzzleHttp\Client();
        
        $totalTime = 0;
        $pingCount = 0;
        $jar = \GuzzleHttp\Cookie\CookieJar::fromArray(
            [
                'JSESSIONID' => $token
            ],
            Aclamate::ACLAMATE_DOMAIN
        );          
        for($ii = 0; $ii < $numPings; $ii++)
        {
            try
            {
                $response = $client->request("GET",$endpoint,
                    [
                        "curl"=>[
                            CURLOPT_SSL_CIPHER_LIST=>"DEFAULT@SECLEVEL=1"
                        ],
                        "cookies" => $jar
                    ]
                );    
                
            } catch (\GuzzleHttp\Exception\RequestException $e)
            {
                Log::error("Aclamate GetRemoteServerTimeOffset Error: " . $e->getMessage());
            }  

            $serverTime = $response->getBody()->getContents();
            if(is_numeric($serverTime))
            {
                $localTime = round(microtime(true) * 1000);
                $totalTime += ($localTime - $serverTime) / 2;
                $pingCount++;
            }
        }
        
        if($pingCount > 0)
        {
            $avgOffset = round($totalTime / $pingCount);
            return array("success"=>true, "offset" => $avgOffset);
        }
        
        return array("success"=>false);        
    }  
    
    public function RequestMetricDataForPerson($token,$personId,$feature,$duration=10000,$source=null)
    {
        //Log::Debug("Aclamate token: " . print_r($token, true));
        //Log::Debug("Aclamate personId: " . print_r($personId, true));
        //Log::Debug("Aclamate feature: " . print_r($feature, true));

        //TODO: handle more than one feature or no features
        $endpoint = Aclamate::ACLAMATE_URL . Aclamate::METRIC_ENDPOINT;
        $endpoint = sprintf($endpoint,$personId,$duration);

        $contents = "";
        $client = new \GuzzleHttp\Client();
        $jar = \GuzzleHttp\Cookie\CookieJar::fromArray(
            [
                'JSESSIONID' => $token
            ],
            Aclamate::ACLAMATE_DOMAIN
        );        
        try
        {
            $response = $client->request("GET",$endpoint,
                [
                    "query"=> [
                        "feature"=>$feature
                    ],
                    "cookies" => $jar,
                    "curl"=>[
                        CURLOPT_SSL_CIPHER_LIST=>"DEFAULT@SECLEVEL=1"
                    ]
                ]
            );
            $contents = $response->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e)
        {
            Log::error("Aclamate RequestMetricDataForPerson " . $e->getMessage());
        }
       
        $contents = json_decode($contents);
        if($contents == null)
        {
            return array("success"=>false);
        }
        //Log::Debug("Aclamate response contents: " . print_r($contents, true));

        $bAllNullValues = true;
        foreach($contents as $cc)
        {
            if(is_null($cc))
            {
                continue;
            }
            
            $bAllNullValues = false;
            
            //TODO: will all features have these values?
            //converting from milliseconds to seconds to support JSON parser in Unity
            $cc->creationTimestamp /= 1000;
            $cc->timestampReceived /= 1000;
        }
        
        if($bAllNullValues)
        {
            return array("success"=>false);
        }
        
        return array("success"=>true,"contents"=>$contents);
    }
    
    public function GetSensorKitByName($token,$sensorKitName)
    {
        $endpoint = Aclamate::ACLAMATE_URL . Aclamate::SENSORKIT_ENDPOINT;
        $endpoint = sprintf($endpoint,$sensorKitName);
        $contents = $this->GetData($token,$endpoint);

        if($contents == null)
        {
            return array("success"=>false);
        }
        return array("success"=>true,"contents"=>$contents);
    }
    
    public function GetAllActiveSensorkits($token)
    {
        $url = Aclamate::ACLAMATE_URL . Aclamate::SENSORKIT_ALL_ENDPOINT;
        $contents = $this->GetData($token,$url);
        if($contents == null)
        {
            return array("success"=>false);
        }
        
        $results = array();
        foreach($contents as $key=>$value)
        {
            if(is_object($value) 
                && property_exists($value, "banConnectionStatus") 
                && property_exists($value->banConnectionStatus, "lastUpdateTimestamp"))
            {
              $value->banConnectionStatus->lastUpdateTimestamp /= 1000;
            }
        }
                
        return array("success"=>true,"contents"=>$contents);
    }
        
    public function GetPersonForSensorKit($token,$sensorKitName)
    {
        $endpoint = Aclamate::ACLAMATE_URL . Aclamate::PERSON_ENDPOINT;
        $endpoint = sprintf($endpoint,$sensorKitName);
        $contents = $this->GetData($token,$endpoint);

        if($contents == null)
        {
            return array("success"=>false);
        }

        return array("success"=>true,"contents"=>$contents);        
    }
    
    public function GetPersonInfo($token,$personId)
    {
        //Log::debug("GetPersonInfo {$personId}");
        $endpoint = Aclamate::ACLAMATE_URL . Aclamate::PERSON_INFO_ENDPOINT;
        $endpoint = sprintf($endpoint,$personId);    

        $contents = $this->GetData($token,$endpoint);
        if($contents == null)
        {
            return array("success"=>false);
        }
        
        $return = (array)$contents->profile;
        foreach($return as $key=>$value)
        {
            if($value == null)
            {
                $return[$key] = "";
            }
        }

        $return["confinedSpaceExpirationDate"] /= 1000;

        return array("success"=>true,"contents"=>(array)$return);
    }
    
    public static function ReduceInt(&$array)
    {
        foreach($array as $key=>$value)
        {
            if(is_object($value))
            {
                $value = (array)$value;
            }
            if(is_array($value))
            {
                self::ReduceInt($value);
            }
            else
            {
                if((stripos($key,"time")!==false || stripos($key,"date")!==false)
                    && is_numeric($value))
                {
                    $array[$key] = $value / 1000;
                    Log::debug("{$key}::{$value}");
                }                
            }
        }
    }

    protected function GetData($token,$endpoint)
    {
        $client = new \GuzzleHttp\Client();
        $jar = \GuzzleHttp\Cookie\CookieJar::fromArray(
            [
                'JSESSIONID' => $token
            ],
            Aclamate::ACLAMATE_DOMAIN
        );         
        try
        {
            $response = $client->request("GET",$endpoint,
                [
                    "curl"=>[
                        CURLOPT_SSL_CIPHER_LIST=>"DEFAULT@SECLEVEL=1"
                    ],                       
                    "cookies" => $jar
                ]
            );
            $contents = $response->getBody()->getContents();
        } catch (\GuzzleHttp\Exception\RequestException $e)
        {
            Log::error("Aclamate error for {$endpoint} " . $e->getMessage());
            return null;
        }
        
        $contents = json_decode($contents);
        return $contents;
    }
    
    public function SetEventForPerson($token,$personId,$eventType,$subType,$description,$timeStamp,$timeOffset)
    {
        $endpoint = Aclamate::ACLAMATE_URL . Aclamate::EVENT_ENDPOINT;
        $description = str_replace(",","",$description); //strip commas in case of csv file download
        
        $body = array("@type" => "event",
                        "creationTimestamp" => Aclamate::CalcRemoteServerTime($timeStamp,$timeOffset),
                        "entity" => 
                            array(
                                "@type"=>"csms_person",
                                "id"=>$personId
                            ),
                        "eventType" => $eventType,
                        "description" => $description,
                        "subType" => $subType
                    );
      
        $client = new \GuzzleHttp\Client();
        $jar = \GuzzleHttp\Cookie\CookieJar::fromArray(
            [
                'JSESSIONID' => $token
            ],
            Aclamate::ACLAMATE_DOMAIN
        ); 
        
        try
        {
            $response = $client->request(
                "POST",
                $endpoint,
                [
                    "curl" => [
                        CURLOPT_SSL_CIPHER_LIST=>"DEFAULT@SECLEVEL=1"
                    ],                    
                    "cookies" => $jar,
                    "json" => $body
                ]);
        } catch (\GuzzleHttp\Exception\RequestException $e)
        {
            Log::error("Aclamate SetEventForPerson: " . $e->getMessage());
        }
        
        $status = $response->getStatusCode();
        $bSuccess = false;
        if ($status % 200 < 100)
        {
            $bSuccess = true;
        }
        
        return array("success"=>$bSuccess,"status"=>$status); 
    }
    
    public function CalcRemoteServerTime($clientTime,$serverOffset)
    {
        return round($clientTime + $serverOffset);
    }
}
