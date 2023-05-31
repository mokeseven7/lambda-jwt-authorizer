<?php

namespace Popcorn\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use UnexpectedValueException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;
use stdClass;

class Token {

    public static function strip_bearer($jwt){
        if ( strpos( $jwt,'Bearer ' ) === 0 ) {
            $jwt = str_replace( 'Bearer ', '', $jwt );
        }

        return $jwt;
    }

    public static function decode($raw_jwt){
        
        $jwt = Token::strip_bearer($raw_jwt);

        foreach(KeyManager::instance()->keys() as $jwk){
            
            try{
                return JWT::decode($jwt, new Key($jwk, 'RS256'));
            }catch(\Exception $e){
                $error = new \stdClass;
                $error->message = $e->getMessage();
                return $error;
            }
            
        }
    }


    

}