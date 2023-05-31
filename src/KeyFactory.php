<?php

namespace Popcorn\Auth;

use Strobotti\JWK\KeySetFactory;

class KeyFactory {

    public static function create(){
        return new KeySetFactory();
    }

}