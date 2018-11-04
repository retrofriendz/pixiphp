<?php

abstract class CreateGameResult extends Enum {
 const Success=1;
 const AlreadyInitiated=2;
 const InAnotherGame=3;
 static function name($n) {
  switch(intval($n)) {
   case CreateGameResult::Success: return 'You created a new game.';
   case CreateGameResult::AlreadyInitiated: return 'You already initiated a game.';
   case CreateGameResult::InAnotherGame: return 'You are already busy with another game.';
   default: return 'Unknown'; break;
  }
 }
};

abstract class AddUserToGameResult extends Enum {
 const Success=1;
 const GameNotInLobby=2;
 const UserIsInitiator=3;
 const UserAlreadyInGame=4;
 const UserInAnotherGame=5;
 static function name($n) {
  switch(intval($n)) {
   case AddUserToGameResult::Success: return 'Player has been added to the game.';
   case AddUserToGameResult::GameNotInLobby: return 'That game has already been started or has ended.';
   case AddUserToGameResult::UserIsInitiator: return 'That player initiated this game.';
   case AddUserToGameResult::UserAlreadyInGame: return 'That player is already in this game.';
   case AddUserToGameResult::UserInAnotherGame: return 'That player is already busy with another game.';
   default: return 'Unknown'; break;
  }
 }
};

abstract class GameStatus extends Enum {
 const Lobby=0;
 const InProgress=1;
 const Completed=2;
 static function name($n) {
  switch(intval($n)) {
   case GameStatus::Lobby: return 'Lobby';
   case GameStatus::InProgress: return 'In Progress';
   case GameStatus::Completed: return 'Completed';
   default: return 'Unknown'; break;
  }
 }
};

 class Game extends Model {

  static function JoinableGames( $user ) {
   global $database;
   $g=new Game($database);
   return $g->By("Status",GameStatus::Lobby);
  }

  static function CanCreateGame( $user ) {
   global $database;
   $g=new Game($database);
   $uid = $user["ID"];
   $user_games = $g->Select("Initiator = $uid AND Status <> 2");
   if ( count($user_games) > 0 ) return CreateGameResult::AlreadyInitiated;
   $user_games = $g->Select('Status <> 2 AND r_Users LIKE \'|%'.$uid.'|%\'');
   if ( count($user_games) > 0 ) return CreateGameResult::InAnotherGame;
   User::IncrementHistoryValue($user,"GamesCreated");
   return CreateGameResult::Success;
  }

  static function CreateGame( $user ) {
   global $database;
   $g=new Game($database);
   $now=strtotime('now');
   return $g->Get($g->Insert(array(
    "LastState"=>"{}",
    "r_Users"=>($user["ID"]."|"),
    "Initiator"=>$user["ID"],
    "Created"=>$now,
    "LastPlayed"=>$now,
    "TurnCount"=>0,
    "Name"=>Game::CreateGameName($user),
    "Settings"=>"{}",
    "PlayerLimit"=>2,
    "Status"=>GameStatus::Lobby,
    "Ready"=>""
   )));
  }

  static function CreateGameName( $user ) {
   return User::GetDisplayName($user)."'s Game";
  }

  static function InitiatedByUser( $user ) {
   global $database;
   $g=new Game($database);
   return $g->By( "Initiator", $user["ID"] );
  }

  static function CompletedByUser( $user ) {
   global $database;
   $g=new Game($database);
   $uid=$user["ID"];
   return $g->Select('Status = 2 AND (r_Users LIKE \'|%'.$uid.'|%\' OR Initiator = $uid)');
  }

  static function PlayingGame($user) {
   $games=$g->Select("Status = 1 AND (Initiator = $uid OR r_Users LIKE '%|$uid|%')");
   if ( count($games) > 0 ) return array_pop($games);
   else return NULL;
  }

  static function ActiveLobby($user) {
   $games=$g->Select("Status = 0 AND (Initiator = $uid OR r_Users LIKE '%|$uid|%')");
   if ( count($games) > 0 ) return array_pop($games);
   else return NULL;
  }

  // In case more than one
  static function ActiveGamesForUser( $user ) {
   global $database;
   $g=new Game($database);
   $uid=$user;
   return $g->Select("Status = 0 AND (Initiator = $uid OR r_Users LIKE '%|$uid|%')");
  }

  // From the originating user's Join Button
  static function AddUserToGame( $user, $game ) {
   if ( intval($game["Status"]) !== 0 ) return AddUserToGameResult::GameNotInLobby;
   if ( intval($user["ID"]) === intval($game["Initiator"]) ) return AddUserToGameResult::UserIsInitiator;
   $user_ids = explode("|",$game["r_Users"]);
   foreach ( $user_ids as $uid )
    if ( intval($uid) === intval($user["ID"]) )
     return AddUserToGameResult::UserAlreadyInGame;
   global $database;
   $g=new Game($database);
   $active_games_for_user = $g->ActiveGamesForUser($user);
   if ( count($active_games_for_user) > 0 ) return AddUserToGameResult::UserInAnotherGame;
   $uid=$user["ID"];
   $g->Set($game["ID"],array("r_Users"=>($game["r_Users"].$uid."|")));
   User::IncrementHistoryValue($user,"GamesJoined");
   return AddUserToGameResult::Success;
  }

  static function AbortGames( $user, $list ) {
   $aborter=$user;
   foreach ( $list as $game ) {
    if ( intval($game["Status"]) === 1 ) {
     // TODO: Ask User, handle re-host
    } else {
     $users_in_game = explode("|",$game["r_Users"]);
     foreach ( $users_in_game as $participant_id ) {
      if ( intval($participant) === intval($aborter["ID"]) ) continue;
      Notification::Write($participant_id,
       array("message"=>"The initiator of this game has rescinded the game.",
             "type"=>"Game",
             "originator"=>User::GetDisplayName($aborter),
             "code"=>0
       )
      );
     }
    }
   }
  }

  static function Abort( $aborter, $game ) {
  }

  static public function SetGameSettingValue($user,$game,$setting,$value) {
   if ( intval($game["Initiator"]) !== intval($user["ID"]) ) return FALSE;
   $json=json_decode($game["settings"],true);
   $json[$setting]=$value;
   global $database;
   $m=new Game($database);
   $m->Set($game["ID"],array("settings"=>json_encode($json)));
   return TRUE;
  }

 };
