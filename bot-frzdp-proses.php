<?php
function prosesApiMessage($sumber)
{
    $updateid = $sumber['update_id'];

    if (isset($sumber['message'])) {
        $message = $sumber['message'];

        if (isset($message['text'])) {
            prosesPesanTeks($message);
        } elseif (isset($message['sticker'])) {
            prosesPesanSticker($message);
        } else {
            // gak di proses silakan dikembangkan sendiri
        }
    }

    if (isset($sumber['callback_query'])) {
        prosesCallBackQuery($sumber['callback_query']);
    }

    return $updateid;
}

function prosesPesanSticker($message)
{
}

function prosesCallBackQuery($message)
{
    $message_id = $message['message']['message_id'];
    $chatid = $message['message']['chat']['id'];
    $data = $message['data'];

    if ($data == 'site' or $data == 'genset') {
    	$inkeyboard = [
                [
                    ['text' => 'RTPO Bukittinggi', 'callback_data' => 'Bukittinggi'],
                ],
                [
                    ['text' => 'RTPO Padang', 'callback_data' => 'Padang'],
                ],
                [
                    ['text' => 'RTPO Solok', 'callback_data' => 'Solok'],
                ],
                [
                    ['text' => 'CANCEL', 'callback_data' => '!hide'],
                ],
            ];
    }

    if ($data == 'Padang') {
    	$inkeyboard = [
                [
                    ['text' => 'Tridaya', 'callback_data' => 'fmc_tri'],
                    ['text' => 'Muara Riau', 'callback_data' => 'fmc_mr'],
                ],
                [
                    ['text' => 'PJT', 'callback_data' => 'fmc_pjt'],
                    ['text' => 'DMT', 'callback_data' => 'fmc_dmt'],
                ],
                [
                    ['text' => 'CANCEL', 'callback_data' => '!hide'],
                ],
            ];
    } else if ($data == 'Bukittinggi') {
    	$inkeyboard = [
                [
                    ['text' => 'Depotel', 'callback_data' => 'fmc_dpt'],
                    ['text' => 'INTI', 'callback_data' => 'fmc_inti'],
                ],
                [
                    ['text' => 'PJT', 'callback_data' => 'fmc_pjt'],
                    ['text' => 'DMT', 'callback_data' => 'fmc_dmt'],
                ],
                [
                    ['text' => 'CANCEL', 'callback_data' => '!hide'],
                ],
            ];
    } else if ($data == 'Solok') {
    	$inkeyboard = [
                [
                    ['text' => 'Muara Riau', 'callback_data' => 'fmc_mr'],
                ],
                [
                    ['text' => 'DMT', 'callback_data' => 'fmc_dmt'],
                ],
                [
                    ['text' => 'CANCEL', 'callback_data' => '!hide'],
                ],
            ];
    }

    if (substr($data, 0, 3) == 'fmc') {
    	$inkeyboard = [
                [
                    ['text' => 'Approved', 'callback_data' => 'approved'],
                    ['text' => 'Rejected', 'callback_data' => 'rejected'],
                ],
                [
                    ['text' => 'Not Yet', 'callback_data' => 'notyet'],
                    ['text' => 'Need Check', 'callback_data' => 'check'],
                ],
                [
                    ['text' => 'CANCEL', 'callback_data' => '!hide'],
                ],
            ];
    }

    if ($data == 'approved' or $data == 'rejected' or $data == 'notyet' or $data == 'check') {
    	$inkeyboard = [
                [
                    ['text' => 'Approved', 'callback_data' => 'Approved'],
                    ['text' => 'Rejected', 'callback_data' => 'Rejected'],
                ],
                [
                    ['text' => 'Not Yet', 'callback_data' => 'notyet'],
                    ['text' => 'Need Check', 'callback_data' => 'check'],
                ],
                [
                    ['text' => 'CANCEL', 'callback_data' => '!hide'],
                ],
            ];
    }

    $text = '*'.date('H:i:s').'* data baru : '.$data;
    editMessageText($chatid, $message_id, $text, $inkeyboard, true);
    $messageupdate = $message['message'];
    $messageupdate['text'] = $data;
    prosesPesanTeks($messageupdate);
}

function prosesPesanTeks($message)
{
	$pesan = $message['text'];
	$text_input = preg_replace('/\s\s+/', ' ', $pesan); 
    $command = explode(' ',$text_input,4);
	
	$chatid = $message['chat']['id'];
	$fromid = $message['from']['id'];
	$msg_id = $message['message_id'];
	isset($message["from"]["username"])
		? $chatuser = $message["from"]["username"]
		: $chatuser = '';
	isset($message["from"]["last_name"]) 
		? $namakedua = ' '.$message["from"]["last_name"] 
		: $namakedua = '';
	isset($message["chat"]["username"])
		? $user_name = $message["chat"]["username"]
		: $user_name = '';
	$namauser = $message["from"]["first_name"].$namakedua;

	if ($command[0] == '/start') {
		$text = "*ASEP BOT*".
				"\n\nBOT asisten untuk mempermudah akses informasi network REG01.".
				"\nUntuk penggunaan, silahkan ketik /help".
				"\n\nby *Asep Jajang Nurjaya* credit to *Fariz Dwi Pratama*".
				"\nRTPO Banda Aceh - NSA Aceh - R01";
				;
		sendApiMsg($chatid, $text, $msg_id, 'Markdown');
	}

	if ($command[0] == '/password'){ //password di setting disini
		sendApiAction($chatid);
		$text = tambah_user($chatid, $chatid, $chatuser, 'User');
		$text_1 = "*ID Anda telah teregistrasi.*\nSilakan ketik /help untuk melihat command yang tersedia.";
		sendApiMsg($chatid, $text_1, $msg_id, 'Markdown');
					//sendApiMsg($input[0], $text_1, false, 'Markdown');
				} 

				
	if (cek_user_existing($fromid) == 'yes') {
		switch (true) {
			case $command[0] == '/id':
				sendApiAction($chatid);
				$user = show_user($chatid);
				$text = "🆔 *".$chatid."*".
	        		"\n├👤 Nama : *".$namauser."*".
	        		"\n├🗣 Username : `".$chatuser."`".
	        		"\n├🔖 Domain : ".$user[0]["User_Domain"].
	        		"\n└🔰 User type : ".$user[0]["User_Type"];
				sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				break;

			case $command[0] == '/help':
				sendApiAction($chatid);
				$text = "Hi, `$namauser`, berikut menu yang dapat digunakan:".
						"\n\n🔎 */ec*\nUntuk mempermudah remark EC Monita.".
						"\n\n🔎 */omc*\nUntuk mempermudah remark multiple EC Monita.".
						"\n\n🔎 */ava*\nCapture summary BTS down Monita (under maintenance).".
						"\n\n🔎 */wali*\nCek daftar site down berdasarkan data di Monita per Wali Site.".
						"\n\n🔎 */down*\nCek daftar site down berdasarkan data di Monita per RTPO.".
						"\n\n🔎 */pg*\nCek daftar site down kategori Platinum & Gold berdasarkan data di Monita per RTPO.".
						"\n\n🔎 */cacti (under maintenance)*\nCapture web cacti.";
						
				sendApiMsg($chatid, $text, false, 'Markdown');
				break;

			case $command[0] == '/ava':
				$text = "mohon menunggu";
				sendApiMsg($chatid, $text, false, 'Markdown');
				sendApiAction($chatid);
				exec('python C:/xampp/htdocs/Monita/capture.py');
				sendApiPhoto($chatid, 'C:/xampp/htdocs/Monita/tmp/monita.png', 'Monita Summary BTS Down');
				break;

				case $command[0] == '/coba111':
				$text = "sebentar";
				sendApiMsg($chatid,$text,false, 'Markdown');
				sendApiAction($chatid);
				$url_cacti="https://bit.ly/2JdqqL7";
				shell_exec("python C:/xampp/htdocs/Monita/test.py $url_cacti");
				sendApiPhoto($chatid, 'C:\xampp\htdocs\Monita\tmp\cacti_crop.jpg','berhasil');
				break;


			case $command[0] == '/cacti2111':
				$text = "mohon menunggu sedang akses web cacti";
				sendApiMsg($chatid, $text, false, 'Markdown');
				sendApiAction($chatid);
				exec('python C:/xampp/htdocs/Monita/cacti.py');
				sendApiPhoto($chatid, 'C:/xampp/htdocs/monita/tmp/cacti.png', 'capture');
				break;

			case $command[0] == '/agata':
				sendApiAction($chatid);
				if (isset ($command[1])){
					$text = agata ($command[1]);
					if (is_string($text)) {
						sendApiMsg($chatid, $text, $msg_id, 'Markdown');
					} else {
						for ($i = 0; $i < count($text); $i++) {
							sendApiMsg($chatid, $text[$i], $msg_id, 'Markdown');
						}
					}
				} else {
					$text = "input tidak ada";
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}
				break;
				
			case $command[0] == '/wali':
				sendApiAction($chatid);
				if (isset($command[1])) {
					$text = monita_clue($command[1],1);
					if (is_string($text)) {
						sendApiMsg($chatid, $text, $msg_id, 'Markdown');
					} else {
						for ($i = 0; $i < count($text); $i++) {
							sendApiMsg($chatid, $text[$i], $msg_id, 'Markdown');
						}
					}
				} else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*/wali [domain walisite]*".
							"\nContoh: /wali asep";
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}
				break;

			case $command[0] == '/down':
				sendApiAction($chatid);
				if (isset($command[1])) {
					$text = monita_clue($command[1],2);
					if (is_string($text)) {
						sendApiMsg($chatid, $text, $msg_id, 'Markdown');
					} else {
						for ($i = 0; $i < count($text); $i++) {
							sendApiMsg($chatid, $text[$i], $msg_id,'Markdown');
						}
					}
				} else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*/down [RTPO]*".
							"\nContoh: /down bna";
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}
				break;

		/*	case $command[0] == '/ec';
				sendApiAction($chatid);
				if (isset($command[1])) {
					$bts_id = $command[1];
					$web_ec = 'http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?action=All-All-All-All&filter[0][field]=regional_name&filter[0][data][type]=string&filter[0][data][value]=sumbagut&filter[0][field]=bts_id&filter[0][data][type]=string&filter[0][data][value]='.$bts_id.'&start=0&limit=20';
					$data_ec = datajson($web_ec);
					for ($i = 0; $i < $data_ec->jml; $i++){
					$pic = walisite($data_ec->data[$i]->wali_name);
					
				if ($command[2] == 'pln_off' or $command[2] == 'hw_du' or $command[2] == 'hw_ru' or $command[2] == 'trm_tn' or $command[2] == 'trm_metro' or $command[2] == 'need_check' or $command[2] == 'mcb_trip' or $command[2] == 'trm_cn' or $command[2] == 'simpul_problem' or $command[2] == 'cgl' or $command[2] == 'commcase' or $command[2]== 'battery_degraded' or $command[2]== 'rru_faulty' or $command[2]== 'duw_faulty' or $command[2]== 'bb_faulty' or $command[2]== 'fo_cut_telkom' or $command[2]== 'genset_fail' or $command[2]== 'ats_fail' or $command[2]== 'optic_rru' or $command[2]== 'tn_telkom' or $command[2]== 'cn_telkom'){
					$id_ticket = remark($command[2]);
					comment($data_ec->data[$i]->id_ticket, $id_ticket, $pic, $data_ec->data[$i]->bts_id, $data_ec->data[$i]->tgl_alarm, $data_ec->data[$i]->status_alarm, $command[2]);
					$text = "data berhasil di input";
				}
					else{
						$text = "maaf remark ec belum tersedia".
						"\nUntuk sementara pilihan hanya:".
							"\n*POWER:*".
							"\n*pln_off*".
							"\n*mcb_trip*".
							"\n*battery_degraded*".
							"\n*genset_fail*".
							"\n*ats_fail*".
							"\n".
							"\n*HARDWARE:*".
							"\n*hw_du (jika digital unit/baseband problem)*".
							"\n*hw_ru (jika RRU Problem)*".
							"\n*optic_rru*".
							"\n*rru_faulty*".
							"\n*duw_faulty*".
							"\n*bb_faulty*".
							"\n". 
							"\n*TRANSMISI:*".
							"\n*trm_tn*". 
							"\n*trm_metro*".
							"\n*fo_cut_telkom*".
							"\n*trm_cn*".
							"\n*cn_telkom*".
							"\n*tn_telkom*".
							"\n*simpul_problem*".
							"\n".
							"\n*OTHER:*".					 
							"\n*need_check*".
							"\n*cgl*".
							"\n*commcase*".
							
							"\nContoh: *//*ec nad001 trm_tn*";
					}
				}
				sendApiMsg($chatid, $text, $msg_id, 'Markdown');
			}
					
					else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*//*ec site_id remark*".
							"\nContoh: *//*ec nad001 pln_off*".
							"\nUntuk sementara pilihan hanya:".
							"\n*POWER:*".
							"\n*pln_off*".
							"\n*mcb_trip*".
							"\n*battery_degraded*".
							"\n*genset_fail*".
							"\n*ats_fail*".
							"\n".
							"\n*HARDWARE:*".
							"\n*hw_du (jika digital unit/baseband problem)*".
							"\n*hw_ru (jika RRU Problem)*".
							"\n*optic_rru*".
							"\n*rru_faulty*".
							"\n*duw_faulty*".
							"\n*bb_faulty*".
							"\n". 
							"\n*TRANSMISI:*".
							"\n*trm_tn*". 
							"\n*trm_metro*".
							"\n*fo_cut_telkom*".
							"\n*trm_cn*".
							"\n*cn_telkom*".
							"\n*tn_telkom*".
							"\n*simpul_problem*".
							"\n".
							"\n*OTHER:*".					 
							"\n*need_check*".
							"\n*cgl*".
							"\n*commcase*".
							"\nContoh: *//*ec nad001 trm_tn*";
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}
				break;*/
        
        case $command[0] == '/ec';
				sendApiAction($chatid);
				if (isset($command[1]) and isset($command[2]) and isset($command[3])) {
					$bts_id = $command[1];
					$web_ec = 'http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?action=All-All-All-All&filter[0][field]=regional_name&filter[0][data][type]=string&filter[0][data][value]=sumbagut&filter[0][field]=bts_id&filter[0][data][type]=string&filter[0][data][value]='.$bts_id.'&start=0&limit=20';
					$data_ec = datajson($web_ec);
					for ($i = 0; $i < $data_ec->jml; $i++){
					$pic = walisite($data_ec->data[$i]->wali_name);
	

						if ($command[2] == 'power'){
							$id_ticket = remark_power($command[3]);
							comment3($data_ec->data[$i]->id_ticket, $id_ticket, $pic, $data_ec->data[$i]->bts_id, $data_ec->data[$i]->tgl_alarm, $data_ec->data[$i]->status_alarm, $command[2],$command[3]);
							$text = "data berhasil di input";
						}
						
						elseif ($command[2] == 'transport'){
							$id_ticket = remark_transport($command[3]);
							comment3($data_ec->data[$i]->id_ticket, $id_ticket, $pic, $data_ec->data[$i]->bts_id, $data_ec->data[$i]->tgl_alarm, $data_ec->data[$i]->status_alarm, $command[2],$command[3]);
							$text = "data berhasil di input";
						}
						elseif ($command[2] == 'ran'){
							$id_ticket = remark_ran($command[3]);
							comment3($data_ec->data[$i]->id_ticket, $id_ticket, $pic, $data_ec->data[$i]->bts_id, $data_ec->data[$i]->tgl_alarm, $data_ec->data[$i]->status_alarm, $command[2],$command[3]);
							$text = "data berhasil di input";
						}
						elseif ($command[2] == 'other'){
							$id_ticket = remark_other($command[3]);
							comment3($data_ec->data[$i]->id_ticket, $id_ticket, $pic, $data_ec->data[$i]->bts_id, $data_ec->data[$i]->tgl_alarm, $data_ec->data[$i]->status_alarm, $command[2],$command[3]);
							$text = "data berhasil di input";
						}	
						else{
							$text = "Penulisan *salah* atau *kategori tidak terdaftar*".
									"\n ==============================".
									"\n pilih kategori *power, transport, ran dan other*".
									"\n contoh penulisan:".
									"\n */ec <site_id> <category> <free_text>*".
									"\n */ec nad001 transport fo_cut_link_darussalam*";
							}
					

					}

					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}
					
					else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n */ec <site_id> <category> <free_text>*".
							"\n */ec nad001 transport fo_cut_link_darussalam*";
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}
				break;
        
        case $command[0] == '/omc';
				sendApiAction($chatid);
				if (isset($command[1]) and isset($command[2]) and isset($command[3])) {
					$multiple = explode('-',$command[1]);
					for ($j=0; $j < count($multiple); $j++) { 
					$bts_id = $multiple[$j];
					$web_ec = 'http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?action=All-All-All-All&filter[0][field]=regional_name&filter[0][data][type]=string&filter[0][data][value]=sumbagut&filter[0][field]=bts_id&filter[0][data][type]=string&filter[0][data][value]='.$bts_id.'&start=0&limit=20';
					$data_ec = datajson($web_ec);
					for ($i = 0; $i < $data_ec->jml; $i++){
					
						if ($command[2] == 'power'){
							$id_ticket = remark_power($command[3]);
							comment4($data_ec->data[$i]->id_ticket, $id_ticket, $data_ec->data[$i]->bts_id, $data_ec->data[$i]->tgl_alarm, $data_ec->data[$i]->status_alarm, $command[2],$command[3]);
							$text = "data berhasil di input";
						}
						
						elseif ($command[2] == 'transport'){
							$id_ticket = remark_transport($command[3]);
							comment4($data_ec->data[$i]->id_ticket, $id_ticket, $data_ec->data[$i]->bts_id, $data_ec->data[$i]->tgl_alarm, $data_ec->data[$i]->status_alarm, $command[2],$command[3]);
							$text = "data berhasil di input";
						}
						elseif ($command[2] == 'ran'){
							$id_ticket = remark_ran($command[3]);
							comment4($data_ec->data[$i]->id_ticket, $id_ticket, $data_ec->data[$i]->bts_id, $data_ec->data[$i]->tgl_alarm, $data_ec->data[$i]->status_alarm, $command[2],$command[3]);
							$text = "data berhasil di input";
						}
						elseif ($command[2] == 'other'){
							$id_ticket = remark_other($command[3]);
							comment4($data_ec->data[$i]->id_ticket, $id_ticket, $data_ec->data[$i]->bts_id, $data_ec->data[$i]->tgl_alarm, $data_ec->data[$i]->status_alarm, $command[2],$command[3]);
							$text = "data berhasil di input";
						}	
						else{
							$text = "Penulisan *salah* atau *kategori tidak terdaftar*".
									"\n ==============================".
									"\n pilih kategori *power, transport, ran dan other*".
									"\n contoh penulisan multiple dipisahkan oleh -:".
									"\n */omc <site_id-site_id-site_id> <category> <free_text>*".
									"\n */omc nad001-nad002-nad003-dst transport fo_cut_link_darussalam*";
							}
					

						}
					}

				sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}
					
					else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n contoh penulisan multiple dipisahkan oleh -:".
							"\n */omc <site_id-site_id-site_id> <category> <free_text>*".
							"\n */omc nad001-nad002-nad003-dst transport fo_cut_link_darussalam*";
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
					}
				break;

/*case $command[0] == '/omc';
				sendApiAction($chatid);
				if (isset($command[1])) {
					$multiple = explode(',',$command[1]);
					for ($j=0; $j<count($multiple); $j++){
					$bts_id = $multiple[$j];
					$web_ec = 'http://10.35.105.112/MONITA/AREA01/c_frame/get_current_list_alarm_a1?action=All-All-All-All&filter[0][field]=regional_name&filter[0][data][type]=string&filter[0][data][value]=sumbagut&filter[0][field]=bts_id&filter[0][data][type]=string&filter[0][data][value]='.$bts_id.'&start=0&limit=20';
					$data_ec = datajson($web_ec);
					for ($i = 0; $i < $data_ec->jml; $i++){
					
				if ($command[2] == 'pln_off' or $command[2] == 'hw_du' or $command[2] == 'hw_ru' or $command[2] == 'trm_tn' or $command[2] == 'trm_metro' or $command[2] == 'need_check' or $command[2] == 'mcb_trip' or $command[2] == 'trm_cn' or $command[2] == 'simpul_problem' or $command[2] == 'cgl' or $command[2] == 'commcase' or $command[2]== 'battery_degraded' or $command[2]== 'rru_faulty' or $command[2]== 'duw_faulty' or $command[2]== 'bb_faulty' or $command[2]== 'fo_cut_telkom' or $command[2]== 'genset_fail' or $command[2]== 'ats_fail' or $command[2]== 'optic_rru' or $command[2]== 'tn_telkom' or $command[2]== 'cn_telkom'){
					$id_ticket = remark($command[2]);
					comment2($data_ec->data[$i]->id_ticket, $id_ticket, $data_ec->data[$i]->bts_id, $data_ec->data[$i]->tgl_alarm, $data_ec->data[$i]->status_alarm, $command[2]);
					$text = "data berhasil di input";
				}
					else{
						$text = "maaf remark ec belum tersedia".
						"\nUntuk sementara pilihan hanya:".
							"\n*POWER:*".
							"\n*pln_off*".
							"\n*mcb_trip*".
							"\n*battery_degraded*".
							"\n*genset_fail*".
							"\n*ats_fail*".
							"\n".
							"\n*HARDWARE:*".
							"\n*hw_du (jika digital unit/baseband problem)*".
							"\n*hw_ru (jika RRU Problem)*".
							"\n*optic_rru*".
							"\n*rru_faulty*".
							"\n*duw_faulty*".
							"\n*bb_faulty*".
							"\n". 
							"\n*TRANSMISI:*".
							"\n*trm_tn*". 
							"\n*trm_metro*".
							"\n*fo_cut_telkom*".
							"\n*trm_cn*".
							"\n*cn_telkom*".
							"\n*tn_telkom*".
							"\n*simpul_problem*".
							"\n".
							"\n*OTHER:*".					 
							"\n*need_check*".
							"\n*cgl*".
							"\n*commcase*".
							
							"\nContoh: *//*ec nad001 trm_tn*";
					}
				}
			}
				sendApiMsg($chatid, $text, $msg_id, 'Markdown');
			}
					
					else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*//*ec site_id remark*".
							"\nContoh: *//*ec nad001 pln_off*".
							"\nUntuk sementara pilihan hanya:".
							"\n*POWER:*".
							"\n*pln_off*".
							"\n*mcb_trip*".
							"\n*battery_degraded*".
							"\n*genset_fail*".
							"\n*ats_fail*".
							"\n".
							"\n*HARDWARE:*".
							"\n*hw_du (jika digital unit/baseband problem)*".
							"\n*hw_ru (jika RRU Problem)*".
							"\n*optic_rru*".
							"\n*rru_faulty*".
							"\n*duw_faulty*".
							"\n*bb_faulty*".
							"\n". 
							"\n*TRANSMISI:*".
							"\n*trm_tn*". 
							"\n*trm_metro*".
							"\n*fo_cut_telkom*".
							"\n*trm_cn*".
							"\n*cn_telkom*".
							"\n*tn_telkom*".
							"\n*simpul_problem*".
							"\n".
							"\n*OTHER:*".					 
							"\n*need_check*".
							"\n*cgl*".
							"\n*commcase*".
							"\nContoh: *//*ec nad001 trm_tn*";
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}
				break;*/
				
			case $command[0] == '/pg':
				sendApiAction($chatid);
				if (isset($command[1])) {
				$text = prioritas($command[1]);
				if (is_string($text)) {
						sendApiMsg($chatid, $text, $msg_id, 'Markdown');
					}
					else{
				for ($i = 0; $i < count($text[0]); $i++) {
					sendApiMsg($chatid, $text[0][$i], $msg_id, 'Markdown');
				}
				}
					}
				else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*/pg [rtpo]*".
							"\nContoh: /pg bna";
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}
				break;

			case $command[0] == 'weathermap':
			$text = "mohon menunggu";
			sendApiMsg($chatid, $text, false, 'Markdown');
			sendApiAction($chatid);
				if (isset($command[1])){
					$output = capture_cacti($command[1],$command[1]);
					sendApiPhoto($chatid, 'C:/xampp/htdocs/monita/tmp/cacti_crop.jpg', $output);
				} else {
					$text = 'input tidak ada atau salah';
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}


			case $command[0] == '/rr':
			$text = "sedang mengakses cacti, mohon menunggu";
			sendApiMsg($chatid, $text, false, 'Markdown');
				sendApiAction($chatid);
				if (isset($command[1])) {
					$command[1] = strtolower($command[1]);
					$input = explode('-',$command[1],2);
					if (isset($input[0]) && isset($input[1])) {
						$output = capture_cacti($input[0],$input[1]);
						sendApiPhoto($chatid, 'C:/xampp/htdocs/monita/tmp/cacti_crop.jpg', $output);	
						if (count($output) == 2) {
							sendApiPhoto($chatid, $output[1], $output[0]);
						} else {
							sendApiMsg($chatid, $output, $msg_id, 'Markdown');
						}
					} else {
						$text = "*Input salah*";
					}
				} else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*/rr [ran_router_1-ran_router_2]*".
							"\nContoh: /rr blanti.1-bbuat2.1".
							"\n\nNote: Sementara hanya untuk link area NSA Padang, dengan list sebagai berikut:\nalhpjg.1, alhpjg.2\nbbuat2.1, bbuat2.2\nbkttgi.1, bkttgi.2\nblanti.1, blanti.2\nbtmrau.1, btmrau.2\nkmbang.1, kmbang.2\nkpcina.1, kpcina.2\nmrpyan.1\npainan.1, painan.2\npdgpjg.1, pdgpjg.2\nplngki.1, plngki.2\npraman.1, praman.2\npsaman.1, psaman.2\npykbh2.1\nsolok.1, solok.2";
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}				
				break;

			case $command[0] == '/ran':
				sendApiAction($chatid);
				if (isset($command[1])) {
					$command[1] = strtolower($command[1]);
					$input = explode('-',$command[1],2);
					if (isset($input[0]) && isset($input[1])) {
						$output = random_web($input[0],$input[1]);
						if (is_string($output)) {
							sendApiMsg($chatid, $output, $msg_id, 'Markdown');
						} else {
							for ($i = 0; $i < count($output); $i++) {
								copy($output[$i]['Link'], 'C:\xampp\htdocs\monita\tmp\file.jpg');
								$file = 'C:\xampp\htdocs\monita\tmp\file.jpg';
								sendApiPhoto($chatid, $file, $output[$i]['Description']);
							}
						}
					} else {
						$text = "*Input salah*";
						sendApiMsg($chatid, $text, $msg_id, 'Markdown');
					}
				} else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*/ran [ran_router_1-ran_router_2]*".
							"\nContoh: /ran kambang-painan".
							"\n\nNote: Sementara hanya untuk link area NSA Padang, dengan list sebagai berikut:\nalahanpanjang\nbandarbuat\nbatangmarau\nbelanti\nbukittinggi\nkambang\nkampungcina\npadangpanjang\npainan\npalangki\npariaman\npasaman\npayakumbuh\nsolok";
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				}
				break;

			case $command[0] == '/cacti11':
				$keyboard = [
				[	'/weathermap'	],
        		[ '/banda'],
        		[ '/binjai', '/medan' ],
        		[ '/siantar', '/kisaran' ],
        		[ '/kabanjahe', '/nias' ],
        		[  '/ranto',  '/sidempuan'],
        		[  '/sibolga', '/singkil' ],
        		[  '/lhokseumawe', '/takengon'],
        		[  '/meulaboh','/langsa'],
    			];
    		sendApiKeyboard($chatid, 'silahkan pilih', $keyboard);
    		sleep(2);
    		break;

    		case $command[0] == '/weathermap':
    			$keyboard = [
    			['weathermap ran_sumut'],
    			['weathermap ran_aceh'],
    			['weathermap ran_new'],
    			['weathermap network'],
    			['weathermap ran_inner_medan'],
    			['weathermap ahz_lambaro'],
    			['weathermap ipbb_medan_aceh'],
    			['weathermap ipbb_medan_siantar'],
    			];
    		sendApiKeyboard($chatid, 'silahkan pilih', $keyboard);
    		sleep(2);
    		break;

			case $command[0] == '/banda':
				$keyboard = [
       			['/rr bandaran1-bandaran2'],
				['/rr lambaro-stosigli'],
				['/rr lambaro-sigli'],
				['/rr stosigli-sigli'],
				['/rr sigli-bireun'],
				['/rr lambaro-darussalam'],
				['/rr darussalam-lampenerut'],
				['/rr lambaro-lampenerut'],
				['/rr lambaro-simelue'],
				['/rr lambaro-mbo'],
				['/rr stosigli-lhk'],
				['/rr lambaro-bpd'],
				['/rr lambaro-singkil'],
				['/rr lambaro-bireun'],

    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/binjai':
				$keyboard = [
        		['/rr binran1-binran2'],
				['/rr binjai-ahz'],
				['/rr binjai-kualasimpang'],
				['/rr binjai-stabat'],
				['/rr binjai-lhokseumawe'],
	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/medan':
				$keyboard = [
        		['/rr mdnran1-mdnran2'],
				['/rr ahz-johorujung'],
				['/rr ahz-puba'],
				['/rr ahz-lubukpakam'],
				['/rr ahz-tembung'],
				['/rr ahz-tebingtinggi'],
				['/rr ahz-kabanjahe'],
				['/rr ahz-binjai'],
				['/rr ahz-kisaran'],
				['/rr ahz-rantauprapat'],
				['/rr ahz-kampungpajak'],
				['/rr ahz-langsa'],
				['/rr ahz-sidempuan'],
				['/rr ahz-tarutung'],
				['/rr ahz-sibolga'],
				['/rr ahz-nias'],
				['/rr ahz-singkil'],
				['/rr ahz-blangpidie'],
				['/rr ahz-idirayeuk'],
				['/rr ahz-kualasimpang'],

	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/siantar':
				$keyboard = [
        		['/rr siaran1-siaran2'],
				['/rr siantar-tebing'],
				['/rr siantar-kisaran'],
				['/rr siantar-porsea'],
				['/rr siantar-sibolga'],
				['/rr tebing-ahz'],
				['/rr tebing-kisaran'],
				['/rr siantar-tarutung'],
				['/rr porsea-tarutung'],
				['/rr siantar-ranto'],
				['/rr siantar-sidempuan'],
				['/rr siantar-nias'],
				['/rr siantar-kabanjahe'],

	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/kisaran':
				$keyboard = [
        		['/rr kisran1-kisran2'],
				['/rr kisaran-tjbalai'],
				['/rr kisaran-ranto'],
				['/rr kisaran-kppajak'],
				['/rr kisaran-tebing'],
				['/rr kisaran-ahz'],
				['/rr kisaran-siantar'],

	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/kabanjahe':
				$keyboard = [
        		['/rr kbjran1-kbjran2'],
				['/rr kbj-babusalam'],
				['/rr kbj-ahz'],
				['/rr kbj-siantar'],
	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/nias':
				$keyboard = [
        		['/rr niasran1-niasran2'],
				['/rr nias-siantar'],
				['/rr nias-singkil'],
				['/rr nias-ahz'],
	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/ranto':
				$keyboard = [
        		['/rr rantoran1-rantoran2'],
				['/rr ranto-kppajak'],
				['/rr ranto-kisaran'],
				['/rr ranto-payabungan'],
				['/rr ranto-sidempuan'],
				['/rr ranto-siantar'],
				['/rr ranto-ahz'],
				['/rr kppajak-ahz'],
	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/sidempuan':
				$keyboard = [
        		['/rr pspran1-pspran2'],
				['/rr sidempuan-panyabungan'],
				['/rr sidempuan-ranto'],
				['/rr panyabungan-ranto'],
				['/rr sidempuan-sibolga'],
				['/rr sidempuan-siantar'],
				['/rr sidempuan-ahz'],
	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/sibolga':
				$keyboard = [
        		['/rr sbgran1-sbgran2'],
				['/rr sibolga-sidempuan'],
				['/rr sibolga-siantar'],
				['/rr sibolga-tarutung'],
				['/rr tarutung-siantar'],
				['/rr tarutung-porsea'],
				['/rr tarutung-ahz'],
				['/rr sibolga-ahz'],
	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/singkil':
				$keyboard = [
        		['/rr sklran1-sklran2'],
				['/rr singkil-simelue'],
				['/rr singkil-bpd'],
				['/rr singkil-nias'],
				['/rr simelue-lambaro'],
				['/rr singkil-lambaro'],
				['/rr singkil-ahz'],
	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/lhokseumawe':
				$keyboard = [
        		['/rr lhkran1-;hkran2'],
				['/rr bireun-lambaro'],
				['/rr bireun-TKN'],
				['/rr lhk-bireun'],
				['/rr lhk-airbersih'],
				['/rr lhk-langsa'],
				['/rr lhk-tkn'],
				['/rr lhk-binjai'],
				['/rr lhk-kualasimpang'],
				['/rr lhk-stosigli'],
	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/takengon':
				$keyboard = [
        		['/rr tknran1-tknran2'],
				['/rr babussalam-kbj'],
				['/rr tkn-bireun'],
				['/rr tkn-lhk'],
	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/meulaboh':
				$keyboard = [
        		['/rr mboran1-mboran2'],
				['/rr mbo-sp4jrm'],
				['/rr mbo-bpd'],
				['/rr bpd-sp4jrm'],
				['/rr bpd-singkil'],
				['/rr mbo-lambaro'],
				['/rr bpd-lambaro'],
				['/rr bpd-ahz'],
				['/rr sp4jrm-lambaro'],
	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/langsa':
				$keyboard = [
        		['/rr lgsran1-lgsran2'],
				['/rr langsa-idirayeuk'],
				['/rr langsa-kualasimpang'],
				['/rr kualasimpang-binjai'],
				['/rr langsa-lhok'],
				['/rr kualasimpang-lhok'],
				['/rr langsa-ahz'],
				['/rr idirayeuk-ahz'],
				['/rr kualasimpang-ahz'],
	    		];
    		sendApiKeyboard($chatid, 'tombol pilihan', $keyboard);
			sleep(2);			 
			break;

			case $command[0] == '/dms111':
				sendApiAction($chatid);
				if (isset($command[1])) {
					$command[1] = strtoupper($command[1]);
					$text = dms($command[1]);
					sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				} else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*/dms [Site ID]*".
							"\nContoh: /dms PAD001";
					sendApiMsg($chatid, $text, $msg_id,'Markdown');
				}
				break;

			case $command[0] == '/cari111':
				sendApiAction($chatid);
				if (isset($command[1])) {
					$clue = $command[1];
					if (isset($command[2])) {
						$clue .= " ".$command[2];	
					}
					$text = cari_dms($clue); 					
					if (is_string($text)) {
						sendApiMsg($chatid, $text, $msg_id, 'Markdown');
					} else {
						for ($i = 0; $i < count($text); $i++) {
							sendApiMsg($chatid, $text[$i], $msg_id, false);
						}
					}
				} else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*/cari [kata kunci nama site]*".
							"\nContoh: /cari pelabuhan";
					sendApiMsg($chatid, $text, $msg_id,'Markdown');
				}
				break;

			// case $command[0] == '/unlock':
			// 	sendApiAction($chatid);
			// 	if (isset($command[1])) {
			// 		$command[1] = strtoupper($command[1]);
			// 		$text = unlock_fanelia($command[1], 'site');
			// 		sendApiMsg($chatid, $text, $msg_id);
			// 	} else {
			// 		$text = "*Input tidak ada!*".
			// 				"\n----------------------------------".
			// 				"\nFormat penulisan command:".
			// 				"\n*/unlock [SIK]*".
			// 				"\nContoh: /unlock 22180407171110";
			// 		sendApiMsg($chatid, $text, $msg_id, 'Markdown');
			// 	}				
			// 	break;

			// case $command[0] == '/unlock1':
			// 	sendApiAction($chatid);
			// 	if (isset($command[1])) {
			// 		$command[1] = strtoupper($command[1]);
			// 		$text = unlock_fanelia($command[1], 'site');
			// 		sendApiMsg($chatid, $text, $msg_id);
			// 	} else {
			// 		$text = "*Input tidak ada!*".
			// 				"\n----------------------------------".
			// 				"\nFormat penulisan command:".
			// 				"\n*/unlock1 [Site ID]*".
			// 				"\nContoh: /unlock1 PAD001";
			// 		sendApiMsg($chatid, $text, $msg_id, 'Markdown');
			// 	}				
			// 	break;

			// case $command[0] == '/unlock2':
			// 	sendApiAction($chatid);
			// 	if (isset($command[1])) {
			// 		$command[1] = strtoupper($command[1]);
			// 		$text = unlock_fanelia($command[1], 'genset');
			// 		sendApiMsg($chatid, $text, $msg_id);
			// 	} else {
			// 		$text = "*Input tidak ada!*".
			// 				"\n----------------------------------".
			// 				"\nFormat penulisan command:".
			// 				"\n*/unlock2 [Site ID]*".
			// 				"\nContoh: /unlock2 PAD001";
			// 		sendApiMsg($chatid, $text, $msg_id, 'Markdown');
			// 	}				
			// 	break;

			// case $command[0] == '.ec':
			// 	sendApiAction($chatid);
			// 	if (isset($command[1])) {
			// 		$command[1] = strtoupper($command[1]);
			// 		$command[2] = strtoupper($command[2]);
			// 		$text[0] = ec($GLOBALS['userid'][$fromid], $command[1], $command[2]);
			// 		$text[1] = false;
			// 	} else {
			// 		$text[0] = "*Input tidak ada!*".
			// 				"\n----------------------------------".
			// 				"\nFormat penulisan command:".
			// 				"\n*.ec [Site ID from Monita]<space>[YES/NO]*:".
			// 				"\nContoh: .ec PNN005D,PNN005G,PNN005W YES".
			// 				"\n\nNote: Sementara hanya untuk site list dari wali site masing-masing dan _engineer comment_ untuk `PLN#POWER OFF# `";
			// 		$text[1] = 'Markdown';
			// 	}
			// 	sendApiMsg($chatid, $text[0], $msg_id, $text[1]);
			// 	break;

			case $command[0] == '.add':
				sendApiAction($chatid);
				if (isset($command[1])) {
					$input = explode('|',$command[1],3);
					$text = tambah_user($chatid, $input[0], $input[1], $input[2]);
					$text_1 = "*ID Anda telah teregistrasi.*\nSilakan ketik /help untuk melihat command yang tersedia.";
					sendApiMsg($input[0], $text_1, false, 'Markdown');
				} else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*.add [ID Telegram]|[Domain]|[User Type]*".
							"\nContoh: .add 12345678|farizpra|User";
				}
				sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				break;

			case $command[0] == '.del':
				sendApiAction($chatid);
				if (isset($command[1])) {
					$text = delete_user($chatid, $command[1]);
				} else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*.del [ID Telegram]*".
							"\nContoh: .del 12345678";
				}
				sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				break;

			case $command[0] == '.upd':
				sendApiAction($chatid);
				if (isset($command[1])) {
					$input = explode('|',$command[1],3);
					$text = update_user($chatid, $input[0], $input[1], $input[2]);
				} else {
					$text = "*Input tidak ada!*".
							"\n----------------------------------".
							"\nFormat penulisan command:".
							"\n*.upd [ID Telegram]|[Domain]|[User Type]*".
							"\nContoh: .upd 12345678|farizpra|User";
				}
				sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				break;

			case $command[0] == '.list':
				sendApiAction($chatid);
				$text = show_all($chatid);
				sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				break;

			case $command[0] == '!admin':
				sendApiAction($chatid);
				$text = show_admin();
				sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				break;

			case $command[0] == '.bc':
				sendApiAction($chatid);
				$pesan = $command[1];
				if (isset($command[2])){
					$pesan .= " ".$command[2];	
				}
				$result = broadcast($chatid, $pesan);
				print_r($result);
				for ($i = 0; $i < count($result[0]); $i++) {
					sendApiMsg($result[0][$i], $result[1], false, 'Markdown');	
				}				
				break;	

			// case $command[0] == '!clockin':
			// 	sendApiAction($chatid);
			// 	$text = hris('clock_in', $command[1], $command[2]);
			// 	sendApiMsg($chatid, $text[0], $msg_id, 'Markdown');
			// 	sendApiMsg($chatid, $text[1], false, 'Markdown');
			// 	break;
				
			// case $command[0] == '!clockout':
			// 	sendApiAction($chatid);
			// 	$text = hris('clock_out', $command[1], $command[2]);
			// 	sendApiMsg($chatid, $text[0], $msg_id, 'Markdown');
			// 	sendApiMsg($chatid, $text[1], false, 'Markdown');
			// 	break;

			case $command[0] == '@admin':
				sendApiAction($chatid);					
				$text = "List command for admin:".
							"\n*.add*".
							"\n*.del*".
							"\n*.upd*".
							"\n*.list*".
							"\n*.bc*";
				sendApiMsg($chatid, $text, $msg_id, 'Markdown');
				break;
						
			
	        case $command[0] == 'reg':
	        	sendApiAction($chatid);
	        	$text = "$chatid|$user_name|User";
	        	sendApiMsg('206067320', $text, false);
	        	sendApiMsg($chatid, 'Please wait for the response from admin.', false);
	        	break;

			case preg_match("/\/echo (.*)/", $command[0]):
				sendApiAction($chatid);
				preg_match("/\/echo (.*)/", $command[0], $hasil);
				$text = '*Echo:* '.$hasil[0];
				sendApiMsg($chatid, $command[0], false, 'Markdown');
				break;

			default:
				break;
		}
	} else if (cek_user_existing($fromid) == 'no') {
		sendApiAction($chatid);
		$text = "Hai, `$namauser`.\n".
				"ID kamu adalah: `$fromid`\n".
				"----------------------------------\n".
				"*Maaf ID kamu belum terdaftar.*".
				"\n *Silahkan ketikkan Password*";
		/*$inkeyboard = [
				[
					['text' => 'Contact @ajnurjaya', 'url' => 'http://telegram.me/ajnurjaya'],
				],[
					['text' => 'REGISTER', 'callback_data' => 'reg'],
				]
			];*/
		sendApiMsg($chatid, $text, $msg_id, 'Markdown');
		//sendApiMsg('206067320', "$namauser `$chatuser` ($fromid) trying to access BOT.", false, 'Markdown');
		

	}
}