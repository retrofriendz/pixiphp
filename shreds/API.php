<?php

 

 class API {
  var $headers,
   $session_id,$session,$session_token,
   $database,$auth,$user,$is_admin,
   $json,$getpost,
   $TODAY;

  function __construct( $endpoint, $action, $j, $g ) {
   global $headers,$session_id,$session,$session_token,$database,$auth,$user,$is_admin;
   $this->headers = $headers;
   $this->session_id = $session_id;
   $this->session = $session;
   $this->session_token = $session_token;
   $this->database = $database;
   $this->auth = $auth;
   $this->user = $user;
   $this->is_admin = $is_admin;
   $this->json = $j;
   $this->getpost = $g;
   $this->TODAY = strtotime('now');
   $this->Action($endpoint,$action,$j,$g);
  }

  ///////////////// Response types

  static function Respond($json) {
   echo json_encode($json);
   die;
  }

  static function Success($message,$json=NULL) {
   echo json_encode(array("result"=>"success","message"=>$message,"values"=>$json));
   die;
  }

  static function Data($type,$values) {
   API::Respond(array("result"=>"success","type"=>$type,"values"=>$values));
  }

  /////////////// Process actions requested by remote client

  function Action( $endpoint, $action, $j, $g ) {
   switch ($endpoint) {
    default:
    case "index": $this->Endpoint_Index($action,$j,$g); break;
   }
   API::Respond(array("result"=>"failure","message"=>"Invalid API endpoint requested: $endpoint.","code"=>102));
  }

  /////////////// Endpoint selectors

  function Endpoint_Index($action,$j,$g) {
   switch ( $action ) {
    case "datetime": $this->API_DateTime(); break;
    case "identify": $this->API_Identify(); break;
    case "login": $this->API_Login($j); break;
    case "logout": $this->API_Logout(); break;
    case "forgot": $this->API_Forgot($j); break;
    case "me": $this->API_MyProfile($j); break;
    case "create": $this->API_Create($j); break;
    case "friend": $this->API_Friend($j); break;
    case "join": $this->API_JoinLobby($j); break;
    case "list": $this->API_ListGames($j); break;
    case "leave": $this->API_LeaveLobby($j); break;
    case "ready": $this->API_ReadyUp($j); break;
    case "start": $this->API_StartGame($j); break;
    case "notes": $this->API_Notify($j); break;
    case "chat": $this->API_Chat($j); break;
    case "play": $this->API_PlayTurn($j); break;
    case "update": $this->API_UpdateGame($j); break;
    case "lobby": $this->API_Game($j); break; // perform queries on the active game 
    default: Auth::EndTransmit("No action requested",-1); break;
   }
  }

  ///////////////// Individual Actions / Responses

  function API_Identify() {
   API::Respond(
    array(
     "result"=>"success",
     "values"=>array(
      "host"=>$_SERVER["HTTP_HOST"],
      "method"=>$_SERVER["REQUEST_METHOD"],
      "post"=>$_POST,
      "headers"=>$this->headers,
      "session_id"=>$this->session_id,
      "session"=>$this->session,
      "admin"=>$this->is_admin
     )
    )
   );
  }

  function API_Login($j) {
   if ( !isset($j["username"]) || !isset($j["password"]) ) Auth::EndTransmit("Not enough parameters for action 'login'",102,$g);
   $un=$j["username"];
   $pw=$j["password"];
   global $session_id,$session_token,$auth,$is_admin;
   $session_id=Auth::Login($un,$pw);
   if ( $session_id === FALSE ) Auth::EndTransmit("Could not log in, email not validated or no password set, check for password reset email or activation link",102);
   if ( $session_id === NULL )  Auth::EndTransmit("Invalid username/password.",102);
   API::Respond(array("result"=>$auth,"session"=>$session_token,"admin"=>$is_admin));
  }

  function API_Logout() {
   if ( is_null($this->session) ) Auth::EndTransmit("Not logged in",-1);
   if ( Auth::Logout($this->session) ) Auth::EndTransmit("Logged out.",1);
   else Auth::EndTransmit("Session had expired.",-1);
  }

  function API_Forgot($j) {
   if ( !isset($j["email"]) ) Auth::EndTransmit("Not enough parameters for action 'forgot'",102);
   $em=$j["email"];
   $user=User::FindByEmail($em);
   if ( false_or_null($user) ) Auth::EndTransmit("No such user with that email address.",102);
   if ( isset($j["to"]) || isset($j["key"]) ) {
    if ( !isset($j["to"]) ) Auth::EndTransmit("Not enough parameters for action 'forgot'",102);
    if ( !isset($j["key"]) ) Auth::EndTransmit("Not enough parameters for action 'forgot'",102);
    if ( strcmp($j["key"],$user["forgotKey"]) !== 0 ) Auth::EndTransmit("That forgot key was invalid.",102);
    if ( $this->TODAY > intval($user["forgetExpires"]) ) {
     Auth::Forgot($user);
     Auth::EndTransmit("That key to reset your password was expired. Check your email for a new key.",102);
    }
    $result=Auth::SetPassword($user,$j["to"]);
    if ( $result === TRUE ) API::Success("Your password has been set.  You may now login.", $result);
    Auth::EndTransmit("Your suggested new password was not secure enough.  Try another.",102);
   } else { // Standard "forgot" request.
    $result=Auth::Forgot($user);
    API::Success("Forgot code sent to your email.  Please check your email.", $result);
   }
  }

  function API_MyProfile( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
   $info=RemoveKeys($this->auth,array("password","forgotKey","forgotExpires","activationKey","activationExpires"));
   $info["games"]=array(
    "active"=>User::ActiveGamesForUser($this->user),
    "lobby"=>User::ActiveLobby($this->user),
    "playing"=>User::PlayingGame($this->user)
   );
   API::Data("myself",$info);
  }

  function API_DateTime() {
   API::Data("datetime",array("timestamp"=>$this->TODAY,"date"=>date('m/d/Y h:m:s.v a')));
  }

  function API_Create( $j ) {
   $type = $j["type"];
   switch ( $type ) {
    case "user": $this->API_CreateUser($j); break;
    case "game": $this->API_CreateGame($j); break;
    default: Auth::EndTransmit("Bad type or no type provided for 'create' action",102);
   }
  }

  function API_CreateUser( $j ) { 
   if ( !(is_null($this->session) || is_null($this->auth)) ) Auth::EndTransmit("Already logged in",-1);
   if ( !isset($j["username"])
     || !isset($j["password"])
     || !isset($j["confirm"])
     || !isset($j["email"]) ) Auth::EndTransit("Invalid request 'create' 'user': not enough parameters.",102);
   if (!filter_var($j["email"], FILTER_VALIDATE_EMAIL)) Auth::EndTransmit("Email not valid.",CreateUserResult::EmailNotValid);
   if ( strcmp($j["password"],$j["confirm"]) !== 0 ) Auth::EndTransit("Confirmation did not match password.",CreateUserResult::PasswordNotConfirmed);
   $user=User::FindByUsername($j["username"]);
   if ( !false_or_null($user) ) Auth::EndTransit("Username already in use.",CreateUserResult::UsernameAlreadyInUse);
   if ( !preg_match('/^[a-zA-Z0-9]{5,}3/', $$j["username"]) ) Auth::EndTransmit("Invalid username: less than 3 characters or not alpha-numeric",CreateUserResult::UsernameNotValid);
   if ( ($result=passwordcheck($j["username"],$j["password"])) !== FALSE ) Auth::EndTransmit("Invalid password: ".$result,CreateUserResult::PasswordNotSecure);
   $user=User::CreateNew($j["username"],$j["password"],$j["email"]);
   if ( false_or_null($user) ) Auth::EndTransmit("Invalid request 'create' 'user': incorrect username or password.",102);
   Auth::EndTransmit("User created successfully.",CreateUserResult::Success);
  }

  // Player wants to initiate a new game
  function API_CreateGame( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
   if ( ($result=Game::CanCreateGame($this->user)) === CreateGameResult::Success )
    API::Data("game",Game::CreateGame($this->user));
   else Auth::EndTransmit(CreateGameResult::name($result),$result);
  }

  // Player wants to add, remove or block another player
  function API_Friend( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
  }

  // Player wants to join a game
  function API_JoinLobby( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
  }

  // Player wants a list of joinable games
  function API_ListGames( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
   API::Data("gamelist",Game::JoinableGames($this->user));
  }

  // Player wants to leave a game's lobby
  function API_LeaveLobby( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
  }

  // Player wants to indicate they are ready to start the game
  function API_ReadyUp( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
  }

  // Player, who is initiator, wants to start the game
  function API_StartGame( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
  }

  // Player wants any pending notifications sent
  function API_Notify( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
  }

  // Player requesting chats or sending a chat
  function API_Chat( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
   $lobby=Game::ActiveLobby($this->user);
   if ( !false_or_null($lobby) ) {

   }
  }

  // Player is sending their turn data
  function API_PlayTurn( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
  }

  // Player wants updated status of the game
  function API_Update( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
  }

  // Game-related, like getting settings, settings modifications

  function API_GameSetting( $game, $j ) {
   if ( !isset($j["name"]) ) Auth::EndTransmit("API_GameSetting: No 'name' provided",-1);
   switch ( $j["name"] ) {
    default: break;
   }
  }

  function API_Lobby( $j ) {
   if ( is_null($this->session) || is_null($this->auth) ) Auth::EndTransmit("Not logged in",-1);
   $game=Game::ActiveLobby($this->user);
   if ( false_or_null($game) ) Auth::EndTransmit("No lobby");
   foreach ( $game as $g ) {
    if ( isset($j["type"]) ) {
     switch ( $j["type"] ) {
      case "set": API_GameSetting($game,$j); break;
      case "get": API::Data("gamesettings",json_decode($game["settings"])); break;
      default: Auth::EndTransmit("API_Lobby: Invalid 'type'",-1); break;
     }
    }
   }
  }

 };
