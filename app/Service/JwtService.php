<?php

namespace App\Service;

use Illuminate\Http\Request;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class jwtService
{

    protected $key;
    protected $payload;
    public function gettokenencode($d)
    {
        $this->key=config('constant.secret');
        $this->payload=array(
            "iss" => config('constant.required_claims.iss'),
            "aud" => config('constant.required_claims.aud'),
            "iat" => config('constant.required_claims.iat'),
            "nbf" => config('constant.required_claims.nbf'),
            "data"=>$d
        );

        $jwt = JWT::encode($this->payload,$this->key,config('constant.algo'));

        return $jwt;
    }

    public function gettokendecode($token)
    {
        $this->key=config('constant.secret');
        JWT::$leeway = 60;
        $decoded = JWT::decode($token, new Key($this->key,config('constant.algo')));
        $decoded_array = (array) $decoded;
        $decoded_data = (array) $decoded_array['data'];
        return $decoded_data;
    }
}

