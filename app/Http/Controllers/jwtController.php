<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class jwtController extends Controller
{

    protected $key = "example_key";
    protected $payload;
    public function gettokenencode($d)
    {
        $this->payload=array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => 1356999524,
            "nbf" => time(),
            "data"=>$d
        );
        $jwt = JWT::encode($this->payload, $this->key, 'HS256');

        return $jwt;
    }

    public function gettokendecode($token)
    {
        JWT::$leeway = 60;
        $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
        $decoded_array = (array) $decoded;
        $decoded_data = (array) $decoded_array['data'];
        return $decoded_data;
    }
}

