<?php

require __DIR__ . '/vendor/autoload.php';

/**
 * The main entry point 
 * 
 *  $data contains the following props 
 *  $type = $data['type']
 *  $jwt = $data['authorizationToken'];
 *  $method = $data['methodArn'];
 * 
 */


 function index($data){
    $verify = \Popcorn\Auth\Token::decode($data['authorizationToken']);

    echo "<pre>";print_r($verify);

    if($verify->error){
        return handle_failure($verify, $data);
    }

    return handle_success($verify, $data);
 }


 function handle_success($decoded, $request){
    $result = [
        'principalId'	=> $decoded->sub,
        'policyDocument' => [
            'Version'   => '2012-10-17',
            'Statement' => [
                [
                    'Action'   => 'execute-api:Invoke',
                    'Effect'   => 'Allow',
                    'Resource' => $request['methodArn'],
                ]
            ],
        ]
    ];

    return json_encode($result);
}


function handle_failure($decoded, $request){
    $result = [
        'principalId'	=> $decoded->sub,
        'policyDocument' => [
            'Version'   => '2012-10-17',
            'Statement' => [
                [
                    'Action'   => 'execute-api:Invoke',
                    'Effect'   => 'Allow',
                    'Resource' => $request['methodArn'],
                ]
            ],
        ]
    ];

    return json_encode($result);

}

