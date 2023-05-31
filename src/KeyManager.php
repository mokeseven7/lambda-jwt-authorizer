<?php

namespace Popcorn\Auth;

use Popcorn\Auth\ClientFactory;
use Popcorn\Auth\KeyFactory;
use Psr\Http\Message\ResponseInterface;

class KeyManager {

    private $keys;
	private $valid_until;

    /** @var \Popcorn\Auth\KeyManager */
    protected static $instance;

    /**
	 * @return \Popcorn\Auth\KeyManager
	 */
	public static function instance() {
		if ( static::$instance === null ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

    public function __construct() {
		$this->keys = [];
		$this->valid_until = 0;
	}

    public function keys(){

        
        if(self::instance()->valid() || empty(self::instance()->keys)){
           self::instance()->fetch_new_keys();
        }

        return self::instance()->keys;
    }


    private function valid(){
        if(!empty(self::instance()->keys)){
            foreach(self::instance()->keys as $key){
                return ( time() > self::instance()->valid_until ) ?  true : false;
            }
        }
    }


    private function fetch_new_keys(){
        $promise = ClientFactory::create()->then(function(ResponseInterface $response){
            
            $exp = $response->getHeaderLine('expires');
        
            $keys = KeyFactory::create()->createFromJSON($response->getBody()->getContents());
            
            if(!empty($keys)){
                foreach($keys->getKeys() as $k){
                  $pems[] = (new \Strobotti\JWK\KeyConverter())->keyToPem($keys->getKeyById($k->getKeyId()));
                }

                $this->keys = $pems;
                $this->valid_until = $exp;
            }    
        });

        $promise->wait();
    }

}