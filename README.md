# Lambda Custom Authorizer 

This repository is a second example of how the [mokeseven7/phpbuilder](https://github.com/mokeseven7/phpbuilder) construct could be used. In this use case, we build a php Docker image for lambda, and use it as a custom authorizer in api gateway. 

![alt text](./assets/auth.png)


The Authorizer returns an IAM policy with either an effect or allow statement based on the outcome of the authentication. 

It verifies the JWT by checking the JWK (periodically polling the /key endpoints, which discoverable via standard .well-known url), and verifying the kid matches what it was signed with. 

The IAM policies are kept simple for the purpose of this demo, but will be something akin to: 

> **AUTH ALLOW:**
```json

{
  "principalId": "<sub_from_jwt>", 
  "Version":"2012-10-17",
  "Statement": [
    {
      "Action" :"execute-api:Invoke",
      "Effect":"Allow",
      "Resource":"<arn_from_request",
    }
  ]
}

```
> **AUTH DENY:**
```json

{
  "principalId": "<sub_from_jwt>", 
  "Version":"2012-10-17",
  "Statement": [
    {
      "Action" :"execute-api:Invoke",
      "Effect":"Allow",
      "Resource":"<arn_from_request">,
    }
  ]
}

```
## Pre-requisites  

If you want to follow along, you'll need the following: 

1. An aws account & the aws cli configured with an admin user. 

2. Node and pnpm/yarn/npm version 16 or above installed on your local machine. 

3. The aws-cdk package installed globally. 

4. Docker installed on you local machine. 

5. A okta developer account (free tier is fine).


## Setup

Sign into your okta developer account, navigate to applications, and choose new "Api Services" Application and click next.   

![alt text](./assets/okta_1.png)


Give your application a name, and click next. 

Dont worry about grabbing the credentials yet, you can access the client secret at any time. 

Next, on the left hand nav bar, click `security`, and then click `Api` at the very bottom of the list.

You should a default authorization server that looks something like this: 

![alt text](./assets/okta_2.png)

Click into the default authorization server, (which will serve us fine for the purposes of this demo) and then click into scopes. 

Create a new scope called tenant, name it tenant, give it a description, and click save. 

![alt text](./assets/okta_3.png)

You should now see your new scope populated in the scopes list. 

At this point, you should now be able to get a JWT from the default authorization server using the application `client_id` and `client_secret` from step one, your developer `auth_server_ url`, in this case, you can see mine in all the screenshots, which is 'okta-dev-1234567890' (which is totally real, feel free to haxor me). 

Grab the above 3 values, and construct a test curl as such: 

```bash
# Fill in the values with your application and dev values, make sure not to include the https when you copy the account id. 
curl -X POST 'https://[client_id]:<client_secret>@[okta_account]/oauth2/default/v1/token' \ 
-d grant_type=client_credentials \
-d scope=tenant

#A fully formed url would appear as such:
https://client1:password1@okta-dev-1234567890/oauth2/default/v1/token

#A full request would appear as such: 
curl -s -X POST 'https://client1:password1@okta-dev-1234567890/oauth2/default/v1/token' \
-d grant_type=client_credentials \
-d scope=tenant 
```

If successfull, the curl should return you a JWT with the scope that you created early in the setup process.



![alt text](./assets/terminal_curl.png)


## Project Structure

Here is the directory layout of the most important files:

```
  | - /bin
  | - /lib
  |   | - custom_authorizer-stack.ts
  | - /runtime
  |   | - bootstrap
  | - /src
  |   | - index.php
  |   | - composer.json
  |   | - Config.php
  |   | - KeyManager.php
  |   | - ClientFactory.php
  |   | - KeyFactory.php
  Dockefile
  pakage.json

```

The `@mikemcgrath/phpbuilder` is a wrapper construct I created to make the docker part easier, you can learn more about it [here](https://github.com/mokeseven7/phpbuilder) if interested. For now, just know its making the deployment of docker images to ecr easier. 

## Deployment

1. In `src/Config.php` fill in your authorization server value. 

2. run the build command with `npm run build`

3. Make sure the docker vm is running on your local machine, and synth the cloudformation with `cdk synth`.   
**Note:** If you've never deployed a cdk project before, you will need to run `cdk bootstrap` before running the above command. The bootstrap command will create a few IAM roles it needs. 

4. run the deploy command. `cdk deploy`


At this point you can go into the console, create a new lambda function, and assign the container that was just created to it. 

![alt text](./assets/function.png)

Normally this part would obviously be done with the cdk also, but this demo is just to highlight the auth portion, and how to achieve it all in php. 


Once the function is ready, create a new Api Gateway test event, and you should be able to successfully test. 

