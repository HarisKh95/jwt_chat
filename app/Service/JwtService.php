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
        $this->key=config('contants.secret');
        $this->payload=array(
            "iss" => config('contants.required_claims.iss'),
            "aud" => config('contants.required_claims.aud'),
            "iat" => config('contants.required_claims.iat'),
            "nbf" => config('contants.required_claims.nbf'),
            "data"=>$d
        );

        $jwt = JWT::encode($this->payload,$this->key,config('contants.algo'));

        return $jwt;
    }

    public function gettokendecode($token)
    {
        $this->key=config('contants.secret');
        JWT::$leeway = 60;
        $decoded = JWT::decode($token, new Key($this->key,config('contants.algo')));
        $decoded_array = (array) $decoded;
        $decoded_data = (array) $decoded_array['data'];
        return $decoded_data;
    }
}

