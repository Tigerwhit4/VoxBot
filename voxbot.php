//<?php
// Prevent PHP from stopping the script after 30 sec
set_time_limit(0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// settings
$chan = "#puregoldenboy";
$server = "puregoldenboy.jtvirc.com";
$port = 6667;
$nick = "voxbot";
$broadcaster = "puregoldenboy";
$broadcasternick = "PGB";

$version = "1.0";
$welcome = "Voxbot v".$version." is now moderating this channel. Contact Voxletum on league for assistance.";

//
function printlog($info)
	{
		echo $info."\n";
		flush();
	}
function permission_list()
	{
		$permitted = array();
		$left = "";
		$right = "";
		$contents = file_get_contents("db/permitted.txt");
		$contents = explode("\n", $contents);
		foreach($contents as $value)
		{
			$timestamp = trim(substr($value, strpos($value, "::")+2));
			$name = trim(substr($value, 0, -12));
			if(time() < ($timestamp+35))
			{
				array_push($permitted, $name);
			}
		}
		return $permitted;
	}
$mods = array();
$socket = fsockopen("$server", $port);
fputs($socket,"USER $nick $nick $nick $nick :$nick\n");
fputs($socket,"PASS qwertyuiop\n");
fputs($socket,"NICK $nick\n");
fputs($socket,"JOIN ".$chan."\n");

//fputs($socket, "PRIVMSG ".$chan." :".$welcome."\n");

while(1) {
	while($data = fgets($socket)) {
        	echo $data . "\n";
	        flush();
			
			//check for mod addition
			if (strpos($data, 'jtv MODE '.$chan.' +o ') < 10 && (strpos($data, 'jtv MODE '.$chan.' +o ') !== false))
			{
				//moderator added
				$modname = trim(substr($data, (9+strlen($chan)+4)));
				array_push($mods, $modname);
				print_r($mods);
				if ($modname == $broadcaster)
				{
					printlog(">> welcomed broadcaster: ".$nick);
					fputs($socket, "PRIVMSG ".$chan." :Hello ".$broadcasternick."! \n");
				}
			}


			//check for mod removal
			if (strpos($data, 'jtv MODE '.$chan.' -o ') < 10 && (strpos($data, 'jtv MODE '.$chan.' -o ') !== false))
			{
				//moderator removed
				$modname = trim(substr($data, (9+strlen($chan)+4)));
				$pos = array_search($modname, $mods);
				unset($mods[$pos]);
				print_r($mods);
			}


			$loc = strpos($data, $chan);
			$message = substr($data, $loc + strlen($chan) + 2);
			$message = rtrim($message);

        	$ex = explode(' ', $data);
			//echo "data: "; print_r($data);

		$rawcmd = explode(':', $ex[3]);

	        $channel = $ex[2];
		$nicka = explode('@', $ex[0]);
		$nickb = explode('!', $nicka[0]);
		$nickc = explode(':', $nickb[0]);

		$host = $nicka[1];
		$nick = $nickc[1];
	        if($ex[0] == "PING"){
        		fputs($socket, "PONG ".$ex[1]."\n");
	        }

		$args = NULL; for ($i = 4; $i < count($ex); $i++) { $args .= $ex[$i] . ' '; }

		if ($rawcmd[1] == "!roulette") {
			//fputs($socket, "PRIVMSG ".$channel." :MD5 ".md5($args)."\n");
			fputs($socket, "PRIVMSG ".$channel." :I don't have a roulette game yet, Sorry! \n");
		}
		elseif ($rawcmd[1] == "!permit") {
			fputs($socket, "PRIVMSG ".$channel." :".trim($args).", You are now permitted to post a link for 30 seconds! \n");
			printlog(">> added ".trim($args)." to permission list");
			$handle = fopen("db/permitted.txt", "a");
			fwrite($handle, strtolower(trim($args))."::".time()."\n");
			fclose($handle);
			unset($handle);
			permission_list();
		}
		elseif (strtolower($message) == "!status") {
			//fputs($socket, "PRIVMSG ".$channel." :MD5 ".md5($args)."\n");
			fputs($socket, "PRIVMSG ".$channel." :Hello ".ucfirst($nick)."! I am currently moderating the chat. \n");
			printlog(">> status was checked: ".$nick);
		}
		elseif ((strpos(strtolower($message), "playlist") !== false)) {
			
			if (($time = file_get_contents("db/playlistspam.txt") < (time()-120)))
			{
			printlog(">> provided music playlist: ".$nick);
			fputs($socket, "PRIVMSG ".$channel." :PGB's Playlist is: http://grooveshark.com/playlist/Puregoldenboy/37507000 \n");
			$handle = fopen("db/playlistspam.txt", "w");
			fwrite($handle, time());
			fclose($handle);
			unset($handle);
			}
		}
		elseif ((strpos(strtolower($message), "closet") !== false)) {
			
			if (($time = file_get_contents("db/closet.txt") < (time()-120)))
			{
			printlog(">> made closet remark: ".$nick);
			fputs($socket, "PRIVMSG ".$channel." :PGB does stream from his closet. He is the only closet streamer in League of Legends. \n");
			$handle = fopen("db/closet.txt", "w");
			fwrite($handle, time());
			fclose($handle);
			unset($handle);
			}
		}
		elseif ((strpos(strtolower($message), "twitter") !== false) || (strpos($message, "facebook") !== false)) {
			
			if (($time = file_get_contents("db/infospam.txt") < (time()-120)))
			{
			printlog(">> provided contact info: ".$nick);
			fputs($socket, "PRIVMSG ".$channel." :You can follow PGB on Twitter: @puregoldenboy and on Facebook.com/PeeGeeBee \n");
			$handle = fopen("db/infospam.txt", "w");
			fwrite($handle, time());
			fclose($handle);
			unset($handle);
			}
		}
		elseif ((strpos(strtolower($message), "donate") !== false) || (strpos(strtolower($message), "donation") !== false)) {
			if (($time = file_get_contents("db/donatespam.txt") < (time()-180)))
			{
			printlog(">> provided donation data: ".$nick);
			fputs($socket, "PRIVMSG ".$channel." :Please support the Pancreatic Cancer Action Network: https://www.firstgiving.com/fundraiser/RobertWaybright/KeeptheMemoryAlive \n");
			$handle = fopen("db/donatespam.txt", "w");
			fwrite($handle, time());
			fclose($handle);
			unset($handle);
			}
		}
		elseif ((strpos(strtolower($message), "http://") !== false) || (strpos(strtolower($message), "www.") !== false) || (strpos(strtolower($message), ".com") !== false) || (strpos(strtolower($message), ".net") !== false) || (strpos(strtolower($message), ".org") !== false) || (strpos(strtolower($message), "bit.ly") !== false) || (strpos(strtolower($message), "tinyurl.com") !== false)) {
			//fputs($socket, "PRIVMSG ".$channel." :MD5 ".md5($args)."\n");
			$permitted = permission_list();
			if (((array_search($nick, $mods) !== false) == false) && ((array_search($nick, $permitted) !== false) == false))
			{
				printlog(">> banned link against: ".$nick);
				fputs($socket, "PRIVMSG ".$channel." :Sorry ".ucfirst($nick).", but links are not allowed in this chat without moderator permission. \n");
				fputs($socket, "PRIVMSG ".$channel." :.timeout ".$nick." 1\n");
				$handle = fopen("db/links.txt", "a");
				fwrite($handle, $message."\n");
				fclose($handle);
				unset($handle);
				unset($permitted);
				$permitted = array();
			}
		}
	}
}
//?>