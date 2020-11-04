<?php
require_once 'medoo.php';
include('Net/SSH2.php');

$database_2 = new medoo([
	'database_type' => 'sqlite',
	'database_file' => 'frzdpbot.db'
]);

function myPre($value)
{
    echo '<pre>';
    print_r($value);
    echo '</pre>';
}

function apiRequest($method, $data)
{
    if (!is_string($method)) {
        error_log("Nama method harus bertipe string!\n");
        return false;
    }

    if (!$data) {
        $data = [];
    } elseif (!is_array($data)) {
        error_log("Data harus bertipe array\n");
        return false;
    }

    $url = 'https://api.telegram.org/bot'.$GLOBALS['token'].'/'.$method;
    $auth = base64_encode('user:password');//proxy autentikasi, masukan disini

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n" . "Proxy-Authorization: Basic $auth",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'proxy' => 'tcp://10.59.82.2:8080'
        ],
    ];
    $context = stream_context_create($options);

    $result = file_get_contents($url, false, $context);

    return $result;
   
}

function getApiUpdate($offset)
{
    $method = 'getUpdates';
    $data['offset'] = $offset;

    $result = apiRequest($method, $data);

    $result = json_decode($result, true);
    if ($result['ok'] == 1) {
        return $result['result'];
    }

    return [];
}

function getChat($chatid)
{
    $method = 'getChat';
    $data['chat_id'] = $chatid;
    $result = apiRequest($method, $data);
    return $result;
}

function sendApiMsg($chatid, $text, $msg_reply_id = true, $parse_mode = false, $disablepreview = false)
{
    $method = 'sendMessage';
    $data = ['chat_id' => $chatid, 'text'  => $text];

    if ($msg_reply_id) {
        $data['reply_to_message_id'] = $msg_reply_id;
    }
    if ($parse_mode) {
        $data['parse_mode'] = $parse_mode;
    }
    if ($disablepreview) {
        $data['disable_web_page_preview'] = $disablepreview;
    }

    $result = apiRequest($method, $data);
}


function sendApiAction($chatid, $action = 'typing')
{
    $method = 'sendChatAction';
    $data = [
        'chat_id' => $chatid,
        'action'  => $action,

    ];
    $result = apiRequest($method, $data);
}

function sendApiPhoto($chatid, $photo, $caption)
{
    $bot_url = 'https://api.telegram.org/bot'.$GLOBALS['token'].'/';
    $url = $bot_url."sendPhoto?chat_id=".$chatid;
    $post_fields = array('chat_id'   => $chatid,
    			'photo'     => new CURLFile(realpath($photo)),
    			'caption' => $caption
	);
    $ch = curl_init(); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  	  "Content-Type:multipart/form-data"
	));
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$output = curl_exec($ch);
	unlink($photo);
}

function sendPhoto($chatid, $photo, $caption)
{
    $bot_url = 'https://api.telegram.org/bot'.$GLOBALS['token'].'/';
    $url = $bot_url."sendPhoto?chat_id=".$chatid;
    $post_fields = array('chat_id'   => $chatid,
    			'photo'     => $photo,
    			'caption' => $caption
	);
    $ch = curl_init(); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  	  "Content-Type:multipart/form-data"
	));
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$output = curl_exec($ch);
}

function sendPhoto2($chat_id, $photo, $caption = null, $reply_to_message_id = null, $reply_markup = null)
	{
		$data = compact('chat_id', 'photo', 'caption', 'reply_to_message_id', 'reply_markup');
		if (((!is_dir($photo)) && (filter_var($photo, FILTER_VALIDATE_URL) === FALSE))) {
			return $this->sendRequest('sendPhoto', $data);
		}
		return $this->uploadFile('sendPhoto', $data);
	}

function sendApiDocument($chatid, $idfile, $caption)
{
    $bot_url = 'https://api.telegram.org/bot'.$GLOBALS['token'].'/';
    $url = $bot_url."sendDocument?chat_id=".$chatid;
    $post_fields = array('chat_id'   => $chatid,
    			'document'     => new CURLFile(realpath($idfile)),
    			'caption' => $caption
	);
    $ch = curl_init(); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  	  "Content-Type:multipart/form-data"
	));
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$output = curl_exec($ch);
}

function sendApiKeyboard($chatid, $text, $keyboard = [], $inline = false)
{
    $method = 'sendMessage';
    $replyMarkup = [
        'keyboard'        => $keyboard,
        'resize_keyboard' => true,
        'one_time_keyboard' => true,
    ];

    $data = [
        'chat_id'    => $chatid,
        'text'       => $text,
        'parse_mode' => 'Markdown',
    ];

    $inline
    ? $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard])
    : $data['reply_markup'] = json_encode($replyMarkup);

    $result = apiRequest($method, $data);
}


function editMessageText($chatid, $message_id, $text, $keyboard = [], $inline = false)
{
    $method = 'editMessageText';
    $replyMarkup = [
        'keyboard'        => $keyboard,
        'resize_keyboard' => true,
    ];

    $data = [
        'chat_id'    => $chatid,
        'message_id' => $message_id,
        'text'       => $text,
        'parse_mode' => 'Markdown',
    ];

    $inline
    ? $data['reply_markup'] = json_encode(['inline_keyboard' => $keyboard])
    : $data['reply_markup'] = json_encode($replyMarkup);

    $result = apiRequest($method, $data);
}

function sendApiHideKeyboard($chatid, $text)
{
    $method = 'sendMessage';
    $data = [
        'chat_id'       => $chatid,
        'text'          => $text,
        'parse_mode'    => 'Markdown',
        'reply_markup'  => json_encode(['hide_keyboard' => true]),
    ];
    $result = apiRequest($method, $data);
}

function sendApiSticker($chatid, $sticker, $msg_reply_id = false)
{
    $method = 'sendSticker';
    $data = [
        'chat_id'  => $chatid,
        'sticker'  => $sticker,
    ];

    if ($msg_reply_id) {
        $data['reply_to_message_id'] = $msg_reply_id;
    }

    $result = apiRequest($method, $data);
}

function grab_page($site){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8");
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($ch, CURLOPT_URL, $site);
    ob_start();
    return curl_exec ($ch);
    ob_end_clean();
    curl_close ($ch);
}

function login($url,$data){
    $fp = fopen("cookie.txt", "w");
    fclose($fp);
    $login = curl_init();
    curl_setopt($login, CURLOPT_COOKIEJAR, "cookie.txt");
    curl_setopt($login, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($login, CURLOPT_TIMEOUT, 40000);
    curl_setopt($login, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($login, CURLOPT_URL, $url);
    curl_setopt($login, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8");
    curl_setopt($login, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($login, CURLOPT_POST, TRUE);
    curl_setopt($login, CURLOPT_POSTFIELDS, $data);
    ob_start();
    return curl_exec ($login);
    ob_end_clean();
    curl_close ($login);
    unset($login);    
}

function monita($web_monita) {
	$data_monita = file_get_contents($web_monita);
	$replace = array(
				'success:' => '"success":',
				'jml:' => '"jml":',
				'data:' => '"data":'
				);
	$data = strtr($data_monita, $replace);
	$data_json = json_decode($data);
	return $data_json;
}

function monita_clue($clue, $type) {
	if ($type == 1) {
		$web_monita = "http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?_dc=1533910639050&action=All-All-All-All&filter[0][field]=wali_name&filter[0][data][type]=string&filter[0][data][value]=".$clue."&page=1&start=0&limit=10000";
		$text_awal = "👤 Wali : *";
	} else if ($type == 2) {
		$web_monita = "http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?_dc=1533910639050&action=All-All-All-All&filter[0][field]=technical_area_name&filter[0][data][type]=string&filter[0][data][value]=".$clue."&page=1&start=0&limit=10000";
		$text_awal = "👥 RTPO : *";
	}
	$data_json = monita($web_monita);
	if ($data_json->jml > 0){
		$j = -1;
		for ($i = 1; $i <= $data_json->jml; $i++) {
			if (($i-1) % 20 == 0){
				++$j;
				$text_text[$j] = '';
			}
			$freetext = preg_replace("/\r|\n/", " ", $data_json->data[$i-1]->freetext);
			$freetext = str_replace("_", " ", $freetext);
			$text = "\n".$i.". *".
					$data_json->data[$i-1]->bts_id." ".
					$data_json->data[$i-1]->site_name."* | ".
					$data_json->data[$i-1]->class_name." | ".
					$data_json->data[$i-1]->tower_provider_name." | ".
					$data_json->data[$i-1]->status_alarm." | ".
					$data_json->data[$i-1]->tgl_alarm." | ".
					$data_json->data[$i-1]->aging." | ".
					$freetext."\n➖";
			if ($i == 1) {
				$text = $text_awal.$clue.
					"*\n⬇️ Jumlah site down : ".$data_json->jml.
					"\n〰〰〰〰〰〰〰".$text;
			}
			$text_text[$j] .= $text;
		}
		$output = $text_text;
	} else {
		$output = $text_awal.$clue.
					"*\n⬇️ Jumlah site down : ".$data_json->jml.
					"\n〰〰〰〰〰〰〰\n*C L E A R* 👍";
	}
	return $output;
}

function multiple($input) {
	$web_monita = "http://10.35.105.112/dev1/MONITA/R01/AVABoard/load_data/get_alarm_like?regional=Sumbagteng";
	$text_awal = "📉 Filter :";
	$n = 0;
	if ($input[0] != '-') {
		$n += 1;
		$web_monita .= "&filter[$n][field]=technical_area&filter[$n][data][type]=string&filter[$n][data][value]=".$input[0];
		$text_awal .= "\n- RTPO : *".$input[0]."*"; 
	}

	if ($input[1] != '-') {
		$n += 1;
		$web_monita .= "&filter[$n][field]=pic_alias&filter[$n][data][type]=string&filter[$n][data][value]=".$input[1];
		$text_awal .= "\n- Wali Site : *".$input[1]."*";
	}

	if ($input[2] != '-') {
		$n += 1;
		$web_monita .= "&filter[$n][field]=tower_provider&filter[$n][data][type]=string&filter[$n][data][value]=".$input[2];
		$text_awal .= "\n- Tower Provider : *".$input[2]."*";
	}

	if ($input[3] != '-') {
		$n += 1;
		$web_monita .= "&filter[$n][field]=bts_id&filter[$n][data][type]=string&filter[$n][data][value]=".$input[3];
		$text_awal .= "\n- Site ID : *".$input[3]."*";
	}

	if ($input[4] != '-') {
		$n += 1;
		$web_monita .= "&filter[$n][field]=status_parsial&filter[$n][data][type]=string&filter[$n][data][value]=".$input[4];
		$text_awal .= "\n- Status : *".$input[4]."*";
	}

	$web_monita .= "&page=1&start=0&limit=10000";
	$data_json = monita($web_monita);

	if ($data_json->jml > 0){
		$j = -1;
		for ($i = 1; $i <= $data_json->jml; $i++) {
			if (($i-1) % 20 == 0){
				++$j;
				$text_text[$j] = '';
			}
			$engineer_comment = preg_replace("/\r|\n/", " ", $data_json->data[$i-1]->engineer_comment);
			$engineer_comment = str_replace("_", " ", $engineer_comment);
			$text = "\n".$i.". *".
					$data_json->data[$i-1]->bts_id." ".
					$data_json->data[$i-1]->site_name."* | ".
					$data_json->data[$i-1]->high_revenue." | ".
					$data_json->data[$i-1]->tower_provider." | ".
					$data_json->data[$i-1]->status_parsial." | ".
					$data_json->data[$i-1]->tgl_alarm." | ".
					$data_json->data[$i-1]->aging." | ".
					$engineer_comment."\n➖";
			if ($i == 1) {
				$text = $text_awal.
					"\n⬇️ Jumlah site down : *".$data_json->jml."*".
					"\n〰〰〰〰〰〰〰".$text;
			}
			$text_text[$j] .= $text;
		}
		$output = $text_text;
	} else {
		$output = $text_awal.
					"\n⬇️ Jumlah site down : ".$data_json->jml."*".
					"\n〰〰〰〰〰〰〰\n*C L E A R* 👍";
	}
	return $output;
}


function dirnet($nsa){
$data_json_diamond = monita("http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?_dc=1533910639050&action=All-All-All-All&filter[0][field]=departement_name&filter[0][data][type]=string&filter[0][data][value]=".$nsa."&filter[1][field]=class_name&filter[1][data][type]=string&filter[1][data][value]=diamond&page=1&start=0&limit=10000");
$data_json_platinum = monita("http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?_dc=1533910639050&action=All-All-All-All&filter[0][field]=departement_name&filter[0][data][type]=string&filter[0][data][value]=".$nsa."&filter[1][field]=class_name&filter[1][data][type]=string&filter[1][data][value]=platinum&page=1&start=0&limit=10000");
$data_json_gold = monita("http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?_dc=1533910639050&action=All-All-All-All&filter[0][field]=departement_name&filter[0][data][type]=string&filter[0][data][value]=".$nsa."&filter[1][field]=class_name&filter[1][data][type]=string&filter[1][data][value]=gold&page=1&start=0&limit=10000");
$obj_merged = array_merge((array) $data_json_diamond->data,(array) $data_json_platinum->data, (array) $data_json_gold->data);
$jumlah = $data_json_diamond->jml + $data_json_platinum->jml + $data_json_gold->jml;
if ($jumlah != 0){
		$j = -1;
		for ($i = 1; $i <= $jumlah; $i++) {
			if (($i-1) % 20 == 0){
				++$j;
				$text_text[$j] = '';
			}
			$freetext = preg_replace("/\r|\n/", " ",$obj_merged[$i-1]->freetext);
			$freetext = str_replace("_", " ", $freetext);
			$text = "\n".$i.". *".
					$obj_merged[$i-1]->bts_id." ".
					$obj_merged[$i-1]->site_name."* | ".
					$obj_merged[$i-1]->technical_area_name." | ".
					$obj_merged[$i-1]->class_name." | ".
					$obj_merged[$i-1]->status_alarm." | ".
					$obj_merged[$i-1]->tgl_alarm." | ".
					$obj_merged[$i-1]->aging." | ".
					$freetext."\n➖";
			if ($i == 1) {
				$text = "🏠 NS : ".$nsa.
					"\n🏆 Category : Diamond-Platinum-Gold".
					"\n⬇️ Site down : ".$jumlah.
					"\n〰〰〰〰〰〰〰".$text;
			}
			$text_text[$j] .= $text;
		}
		$output[0] = $text_text;
	} else {
		$output = "🏠 NSA : ".$nsa.
					"\n🏆 Category : Diamond-Platinum-Gold".
					"\n⬇️ Site down : ".$jumlah.
					"\n〰〰〰〰〰〰〰\n*C L E A R* 👍 Mantap euy!";
	}	
	return $output;

}


function prioritas($nsa) {
	$data_json_platinum = monita("http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?_dc=1533910639050&action=All-All-All-All&filter[0][field]=technical_area_name&filter[0][data][type]=string&filter[0][data][value]=$nsa&filter[1][field]=class_name&filter[1][data][type]=string&filter[1][data][value]=platinum&page=1&start=0&limit=10000");
	$data_json_gold = monita("http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?_dc=1533910639050&action=All-All-All-All&filter[0][field]=technical_area_name&filter[0][data][type]=string&filter[0][data][value]=$nsa&filter[1][field]=class_name&filter[1][data][type]=string&filter[1][data][value]=gold&page=1&start=0&limit=10000");
	$obj_merged = array_merge((array) $data_json_platinum->data, (array) $data_json_gold->data);
	$jumlah = $data_json_platinum->jml + $data_json_gold->jml;
	if ($jumlah != 0){
		$j = -1;
		for ($i = 1; $i <= $jumlah; $i++) {
			if (($i-1) % 20 == 0){
				++$j;
				$text_text[$j] = '';
			}
			$freetext = preg_replace("/\r|\n/", " ",$obj_merged[$i-1]->freetext);
			$freetext = str_replace("_", " ", $freetext);
			$text = "\n".$i.". *".
					$obj_merged[$i-1]->bts_id." ".
					$obj_merged[$i-1]->site_name."* | ".
					$obj_merged[$i-1]->technical_area_name." | ".
					$obj_merged[$i-1]->class_name." | ".
					$obj_merged[$i-1]->status_alarm." | ".
					$obj_merged[$i-1]->tgl_alarm." | ".
					$obj_merged[$i-1]->aging." | ".
					$freetext."\n➖";
			if ($i == 1) {
				$text = "🏠 RTPO : ".$nsa.
					"\n🏆 Category : Platinum & Gold".
					"\n⬇️ Site down : ".$jumlah.
					"\n〰〰〰〰〰〰〰".$text;
			}
			$text_text[$j] .= $text;
		}
		$output[0] = $text_text;
	} else {
		$output = "🏠 NSA : ".$nsa.
					"\n🏆 Category : Platinum & Gold".
					"\n⬇️ Site down : ".$jumlah.
					"\n〰〰〰〰〰〰〰\n*C L E A R* 👍 Mantap euy!";
	}	
	return $output;
}


function random_web($ran_1,$ran_2) {
	
	global $database_2;
	$data_1 = $database_2->select('ran_router_random_nsa_padang',[
		'Description',
		'Link'
	],[
		'AND' => [
			'Source_1' => $ran_1,
			'Source_2' => $ran_2,
		]
	]);
	
	$data_2 = $database_2->select('ran_router_random_nsa_padang',[
		'Description',
		'Link'
	],[
		'AND' => [
			'Source_1' => $ran_2,
			'Source_2' => $ran_1,
		]
	]);
	
	if (count($data_1)==0 && count($data_2)==0) {
		$data = "*Input yang dimasukkan salah / tidak ada di database.*";
	} else {
		$data = array_merge($data_1, $data_2);
	}
	return $data;
}

#-----disini bagian remark EC----------------------------------------------------------------
function comment($ticket, $kode, $pic, $btsid, $tglalarm, $status, $remark) {
	$web_ec = 'http://10.35.105.112/MONITA/AREA01_BACKEND/monita_ticket/api_monita.php?id_ticket='.$ticket.'&id_pc='.$kode.'&alias='.$pic.'&freetext='.$remark.'&input_from=Web Monita&status_kirim_email=NO&flag=mbp&bts_id='.$btsid.'&tgl_alarm='.$tglalarm.'&status_alarm='.$status;
	$web_ec = str_replace(' ', '%20', $web_ec);
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8");
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_URL, $web_ec);
	curl_setopt($ch, CURLOPT_POST, TRUE);
    ob_start();
    curl_exec($ch);
    ob_end_clean();
    curl_close($ch);
}

function comment2($ticket, $kode, $btsid, $tglalarm, $status, $remark) {
	$web_ec = 'http://10.35.105.112/MONITA/AREA01_BACKEND/monita_ticket/api_monita.php?id_ticket='.$ticket.'&id_pc='.$kode.'&alias=asepnur&freetext='.$remark.'&input_from=Web Monita&status_kirim_email=NO&flag=mbp&bts_id='.$btsid.'&tgl_alarm='.$tglalarm.'&status_alarm='.$status;
	$web_ec = str_replace(' ', '%20', $web_ec);
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8");
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_URL, $web_ec);
	curl_setopt($ch, CURLOPT_POST, TRUE);
    ob_start();
    curl_exec($ch);
    ob_end_clean();
    curl_close($ch);
}

function comment3($ticket, $kode, $pic, $btsid, $tglalarm, $status, $remark1, $remark2) {
	$web_ec = 'http://10.35.105.112/MONITA/AREA01_BACKEND/monita_ticket/api_monita.php?id_ticket='.$ticket.'&id_pc='.$kode.'&alias='.$pic.'&freetext='.$remark1.'_'.$remark2.'&input_from=Web Monita&status_kirim_email=NO&flag=mbp&bts_id='.$btsid.'&tgl_alarm='.$tglalarm.'&status_alarm='.$status;
	$web_ec = str_replace(' ', '%20', $web_ec);
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8");
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_URL, $web_ec);
	curl_setopt($ch, CURLOPT_POST, TRUE);
    ob_start();
    curl_exec($ch);
    ob_end_clean();
    curl_close($ch);
}

function comment4($ticket, $kode, $btsid, $tglalarm, $status, $remark1, $remark2) {
	$web_ec = 'http://10.35.105.112/MONITA/AREA01_BACKEND/monita_ticket/api_monita.php?id_ticket='.$ticket.'&id_pc='.$kode.'&alias=omc01&freetext='.$remark1.'_'.$remark2.'&input_from=Web Monita&status_kirim_email=NO&flag=mbp&bts_id='.$btsid.'&tgl_alarm='.$tglalarm.'&status_alarm='.$status;
	$web_ec = str_replace(' ', '%20', $web_ec);
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8");
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_URL, $web_ec);
	curl_setopt($ch, CURLOPT_POST, TRUE);
    ob_start();
    curl_exec($ch);
    ob_end_clean();
    curl_close($ch);
}

function remark_ec ($bts_id){
$web_ec = 'http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?action=All-All-All-All&filter[0][field]=regional_name&filter[0][data][type]=string&filter[0][data][value]=sumbagut&filter[0][field]=bts_id&filter[0][data][type]=string&filter[0][data][value]='.$bts_id.'&start=0&limit=20';
return $web_ec;
}

function datajson($web_ec) {
	$web_ec = str_replace(' ', '%20', $web_ec);
	$output = file_get_contents($web_ec);
	$replace = array(
		'success:' => '"success":',
		'jml:' => '"jml":',
		'data:' => '"data":'
	);
	$data = strtr($output, $replace);
	$data_json = json_decode($data);
	return $data_json;
}

function agata($siteid){
	$web_agata = 'http://10.35.105.77/AGATAarea01/load_data_summary/panel_id_data_nos_network_profile_site?_dc=1545405127893&filter[0][field]=site_id&filter[0][data][type]=string&filter[0][data][value]='.$siteid.'&page=1&start=0&limit=2000';
	$web_agata_rto='http://10.35.105.77/AGATAarea01/load_data_summary/panel_id_data_rto_network_profile_site?_dc=1545710252357&filter[0][field]=site_id&filter[0][data][type]=string&filter[0][data][value]='.$siteid.'&page=1&start=0&limit=2000';
	$data_json = monita($web_agata);
	$data_json2 = monita($web_agata_rto);
	
			$text = 
					$data_json->data[0]->site_id." ".
					$data_json->data[0]->site_name.
					"\n===============".
					"\n*Class: *".$data_json->data[0]->class_name.
					"\n*Wali: *".$data_json->data[0]->wali_name.
					"\n*RTPO: *".$data_json->data[0]->technical_area_name.
					"\n*Alamat: *".$data_json->data[0]->alamat.
					"\n*TP: *".$data_json->data[0]->tower_provider_name.
					"\n*Jenis tower: *".$data_json->data[0]->jenis_tower_name.
					"\n*Ketinggian: *".$data_json->data[0]->height_tower_name.
					"\n*Band_conf: *".$data_json2->data[0]->band_conf.
          "\n*Transport: *".$data_json2->data[0]->transport_type.
					"\n*Akhir kontrak: *".$data_json->data[0]->akhir_periode_kontrak.
					"\nGoogle Maps : http://maps.google.com/?q=@".$data_json2->data[0]->latitude.",".$data_json2->data[0]->longitude;		
			
			$text_proses = preg_replace('/_/', ' ', $text);		
			$output[0] = $text_proses;
	
	return $output;
}



function walisite($pic) {
	switch ($pic) {
		case 'Asep Jajang Nurjaya':
			$wali = 'asepnur';
			break;

		case 'Zaki Mubarak':
			$wali = 'zakimub';
			break;

		case 'Rudito Prayogo':
			$wali = 'ruditopra';
			break;

		case 'Fernando Saragih':
			$wali = 'fernandosar';
			break;

		case 'Afiq Riyanda Putra':
			$wali = 'afiqput';
			break;

		case 'Rifqi Z Rosyad':
			$wali = 'rifqiros';
			break;

		case 'Syahlan Hutagaol':
			$wali = 'syahlanhut';
			break;

		case 'Amir Miftahudin':
			$wali = 'amirmit';
			break;

		case 'Monang Parsaroan Tamba':
			$wali = 'monangt';
			break;

		case 'Bagus Haryanto':
			$wali = 'bagushar';
			break;

		case 'Azfar Muhammad':
			$wali = 'azfarmuh';
			break;

		case 'Hendra Irawan':
			$wali = 'hendraira';
			break;

		case 'Ray Putra Tarigan':
			$wali = 'raytai';
			break;

		case 'Putu Bramantya':
			$wali = 'putubra';
			break;

		case 'Mukhlis':
			$wali = 'mukhlispra';
			break;

		case 'Junaidi':
			$wali = 'junaidisir';
			break;

		case 'Ridha Mj':
			$wali = 'ridhajoh';
			break;

		case 'Ivan Setiawan Situmorang':
			$wali = 'ivansit';
			break;

		case 'Yoga Dwi Haryoko':
			$wali = 'yogahar'; 
			break;

		case 'Affandi Panjaitan':
			$wali = 'affandypan';
			break;

		case 'Feri S Tarigan':
			$wali = 'feritar';
			break;

		case 'Adriyadi Subiyakto':
			$wali = 'adriyadisub';
			break;

		case 'Arya':
			$wali = 'nicolaskun';
			break;

		case 'Ikhsan':
			$wali = 'ikhsanrah';
			break;

		case 'Melky':
			$wali = 'melkisit';
			break;

		case 'Roy Naldo Nathanael':
			$wali = 'r01_omc_1';
			break;

		case 'Benny William S':
			$wali = 'bennywil';
			break;

		case 'Vincentius Vicky':
			$wali = 'r01_omc_1';
			break;

		case 'Deni Destian':
			$wali = 'denihaa';
			break;

		case 'Kevin Kristian':
			$wali = 'kevinkri';
			break;

		case 'Kristanto Robby Winner':
			$wali = 'kristantowin';
			break;
	}

	return $wali;
}

function remark_power($free_teks){

	if (preg_match("/pln/i", $free_teks)){
		$kode = '7';
	}
	elseif (preg_match("/genset/i", $free_teks)){
		$kode = '1';
	}
	elseif (preg_match("/ats/i", $free_teks)){
		$kode = '3';
	}
	elseif (preg_match("/battery/i", $free_teks)){
		$kode = '12';
	}
	elseif (preg_match("/rectifier/i", $free_teks)){
		$kode = '1';
	}
	elseif (preg_match("/kwh/i", $free_teks)){
		$kode = '8';
	}
	elseif (preg_match("/acpdb/i", $free_teks)){
		$kode = '8';
	}
	elseif (preg_match("/bbm/i", $free_teks)){
		$kode = '2';
	} else{
		$kode = '7';
	}

	return $kode;

}

function remark_transport($free_teks){
	

	if (preg_match("/ptn/i", $free_teks)){
		$kode = '31';
	}
	elseif (preg_match("/cn/i", $free_teks)){
		$kode = '24';
	}
	elseif (preg_match("/sdh/i", $free_teks)){
		$kode = '27';
	}
	elseif (preg_match("/tn/i", $free_teks)){
		$kode = '25';
	}
	elseif (preg_match("/idr/i", $free_teks)){
		$kode = '3';
	}
	elseif (preg_match("/quatro/i", $free_teks)){
		$kode = '32';
	}
	elseif (preg_match("/satelite/i", $free_teks)){
		$kode = '43';
	}
	elseif (preg_match("/flicker/i", $free_teks)){
		$kode = '47';
	}
	elseif (preg_match("/looping/i", $free_teks)){
		$kode = '38';
	}
	elseif (preg_match("/config/i", $free_teks)){
		$kode = '42';
	}
	elseif (preg_match("/cut/i", $free_teks)){
		$kode = '34';
	}
	elseif (preg_match("/metro/i", $free_teks)){
		$kode = '34';
	}
	elseif (preg_match("/radioip/i", $free_teks)){
		$kode = '89';
	} else{
		$kode = '22';
	}

	return $kode;

}

function remark_ran($free_teks){
	

	if (preg_match("/duw/i", $free_teks)){
		$kode = '51';
	}
	elseif (preg_match("/rru/i", $free_teks)){
		$kode = '52';
	}
	elseif (preg_match("/dug/i", $free_teks)){
		$kode = '53';
	}
	elseif (preg_match("/optic/i", $free_teks)){
		$kode = '57';
	}
	elseif (preg_match("/hang/i", $free_teks)){
		$kode = '63';
	}
	elseif (preg_match("/bb/i", $free_teks)){
		$kode = '58';
	}
	elseif (preg_match("/baseband/i", $free_teks)){
		$kode = '58';
	}
	 else{
		$kode = '58';
	}

	return $kode;

}

function remark_other($free_teks){
	
	if (preg_match("/activity/i", $free_teks)){
		$kode = '92';
	}
	elseif (preg_match("/commcase/i", $free_teks)){
		$kode = '73';
	}
	elseif (preg_match("/cgl/i", $free_teks)){
		$kode = '73';
	}
	elseif (preg_match("/stolen/i", $free_teks)){
		$kode = '80';
	}
	elseif (preg_match("/akses/i", $free_teks)){
		$kode = '74';
	} else{
		$kode = '92';
	}

	return $kode;

}

function remark($free_teks){
	switch ($free_teks){
		case 'pln_off':
			$kode='7';
			break;

		case 'hw_du':
			$kode='51';
			break;
			
		case 'duw_faulty':
			$kode='51';
			break;
		
		case 'bb_faulty':
			$kode='51';
			break;

		case 'hw_ru':
			$kode='52';
			break;
			
		case 'rru_faulty':
			$kode='52';
			break;

		case 'genset_fail':
			$kode='1';
			break;
			
		case 'ats_fail':
			$kode='5';
			break;
			
		case 'optic_rru':
			$kode='57';
			break;
			
		case 'tn_telkom':
			$kode='89';
			break;
			
		case 'cn_telkom':
			$kode='88';
			break;

		case 'trm_tn':
			$kode='25';
			break;

		case 'trm_metro':
			$kode='34';
			break;
		
		case 'fo_cut_telkom':
			$kode='34';
			break;
	
		case 'need_check':
			$kode='92';
			break;

		case 'mcb_trip':
			$kode='8';
			break;

		case 'trm_cn':
			$kode='24';
			break;

		case 'simpul_problem':
			$kode='30';
			break;

		case 'cgl':
			$kode='73';
			break;

		case 'commcase':
			$kode='74';
			break;

		case 'battery_degraded':
			$kode='19';
			break;
		
		case 'activity':
			$kode='92';
			break;
			
		case 'akses_rawan':
			$kode='104';
			break;
			
		case 'akses_longsor':
			$kode='104';
			break;
			
		case 'akses_banjir':
			$kode='104';
			break;
			
		case 'recty_problem':
			$kode='20';
			break;
			
		case 'module_recty_faulty':
			$kode='20';
			break;
			
		case 'kabel_dc_short':
			$kode='20';
			break;
			
		case 'kabel_ac_short':
			$kode='8';
			break;
			
		case 'trafo_problem':
			$kode='7';
			break;
			
		case 'bbm_empty':
			$kode='2';
			break;
			
		case 'optic_putus':
			$kode='57';
			break;
	}
	return $kode;
}


#----------akhir dari remark EC---------------------------------------------------------------


/*function cacti($ran_1,$ran_2) {
	global $database_2;
	$data = $database_2->select('router_nsa_padang',[
		'RAN_Router_1',
		'Port_1',
		'Description_1',
		'RAN_Router_2',
		'Port_2',
		'Description_2',
		'Bandwidth',
		'ID'],[
		'Router_Name_1' => $ran_1,
		'Router_Name_2' => $ran_2
	]);

	if (isset($data)){
		//copy("http://10.33.192.70/cacti/graph_image.php?local_graph_id=".$id."&graph_height=300&graph_width=900",'C:\xampp\htdocs\Monita\tmp\file.jpg');
		$url_cacti=$data[7];
		$result = shell_exec("python C:/xampp/htdocs/Monita/test.py $url_cacti");
				$output[1] = 'C:\xampp\htdocs\Monita\tmp\cacti_crop.jpg';
	}
	return $output;
}*/

function capture_cacti($ran_1,$ran_2){
	global $database_2;
	$data = $database_2-> select('ran_router_r01',['link','description'],[
		'AND' =>[
			'router_1' => $ran_1,
			'router_2' => $ran_2,
				]
		]);
	if (isset($data)){
		$url_cacti= $data[0]['link'];
		$hasil = shell_exec("python C:/xampp/htdocs/Monita/test.py $url_cacti");
		$output1 = $data[0]['description'];
	}
	return $output1;
}

function cek_admin($iduser){
	global $database_2;
	$cek = $database_2->select('user','User_Type',['ID_Telegram' => $iduser]);
	if ($cek[0] == 'Admin') {
		$a =  'yes';
	} else {
		$a =  'no';
	}
	return $a;
}

function cek_user($iduser){
	global $database_2;
	$cek = $database_2->select('user','User_Type',['ID_Telegram' => $iduser]);
	if ($cek[0] == 'Admin' or $cek[0] == 'TSel') {
		$a =  'yes';
	} else {
		$a =  'no';
	}
	return $a;
}

function cek_user_existing($iduser){
	global $database_2;
	$cek = $database_2->select('user','User_Domain',['ID_Telegram' => $iduser]);
	if (isset($cek[0])){
		$a =  'yes';
	} else {
		$a =  'no';
	}
	return $a;
}

function tambah_user($iduser1, $iduser, $domain, $type){
	global $database_2;
	//if (cek_admin($iduser1) ==  'yes') {
		if (cek_user_existing($iduser)){
			if ((strlen($iduser) >= 8) and isset($domain) and isset($type)){
				$lastid = $database_2->insert('user',[
					'ID_Telegram' => $iduser,
					'User_Domain' => $domain,
					'User_Type' => $type,
				]);
				$hasil = $iduser.' berhasil di registrasi, silahkan ketik /help';
			} else {
				$hasil = 'Please check the completeness of the ID to be registered!';
			}
		} else {
			$hasil = $iduser.' duplicate!';
		}
	//} else {
		//$hasil = 'You are not an admin!';
	//}
	return $hasil;
}

function update_user($iduser1, $iduser2, $domain2, $type2){
	global $database_2;
	if (cek_admin($iduser1) ==  'yes'){
		if (cek_user_existing($iduser2) == 'no'){
			$hasil = $iduser2.' not found!';
		} else {
			$database_2->update('user',[
				'User_Domain' => $domain2,
				'User_Type' => $type2,
				],[
				'ID_Telegram' => $iduser2,
			]);
			$hasil = $iduser2.' successfully updated!';
		}
	} else {
		$hasil = 'You are not an admin!';
	}
	return $hasil;
}

function delete_user($iduser1, $iduser2){
	global $database_2;
	if (cek_admin($iduser1) ==  'yes') {
		if (cek_user_existing($iduser2) == 'no'){
			$hasil = $iduser2.' not found!';
		} else {
			$database_2->delete('user',['ID_Telegram' => $iduser2]);
			$hasil = $iduser2.' successfully deleted!';
		}
	} else {
		$hasil = 'You are not an admin!';
	}
	return $hasil;
}

function show_admin(){
	global $database_2;
	$datas = $database_2->select('user',[
		'ID_Telegram',
		'User_Domain',
		'User_Type',],[
		'User_Type' => 'Admin',
	]);
	$result = "*List Admin BOT* :";
	for ($i = 1; $i <= count($datas); $i++) {
		$user_admin = getChat($datas[$i-1]['ID_Telegram']);
		$user_admin = json_decode($user_admin, true);
		
		isset($user_admin["result"]["username"])
			? $chatuser = $user_admin["result"]["username"]
			: $chatuser = '';
		isset($user_admin["result"]["last_name"]) 
			? $namakedua = ' '.$user_admin["result"]["last_name"] 
			: $namakedua = '';
		$namauser = $user_admin["result"]["first_name"].$namakedua;

		$text = "\n$i.  ".$namauser." - `".$datas[$i-1]['User_Domain']."` - @".$chatuser;

		$result .= $text;
	}
	return $result;
}

function show_all($iduser1) {
	global $database_2;
	if (cek_admin($iduser1) ==  'yes') {
		$datas = $database_2->select('user',"*");
		$result = "*List User BOT* :";
		for ($i = 1; $i <= count($datas); $i++){
			$text = "\n$i.  `".$datas[$i-1]['User_Domain']."`";
			$result .= $text;
		}
	} else {
		$result = 'You are not an admin!';
	}
	return $result;
}

function show_user($iduser) {
	global $database_2;
	$hasil = $database_2->select('user',"*",["ID_Telegram"=>$iduser]);
	return $hasil;
}

function broadcast($iduser1, $pesan) {
	global $database_2;
	if (cek_admin($iduser1) ==  'yes') {
		$hasil_1 = $database_2->select('user',"ID_Telegram",["User_Type"=>'User']);
		$hasil_2 = $database_2->select('user',"ID_Telegram",["User_Type"=>'TSel']);
		$hasil[0] = array_merge($hasil_1, $hasil_2);
	}
	if (count($pesan) <= 4096) {
		$hasil[1] = $pesan;
	} else {
		$hasil[0] = $iduser1;
		$hasil[1] = "Your message is more than 4096 characters!";
	}
	return $hasil;
}
