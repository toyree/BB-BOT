<?php
$access_token = 'LvT9xy2S/PpEYV13/nYmuiKPmCN32IgDIedHiPx/Yblb/o56FfA1O/jm5nQPkWfLlbGe0+qqULp/RcnNz6lYX6VVX22NejRzzxfPINneai29WceiQFJ04BMUhSLeJoJd/ffZW+lFzjwFudtrgD1FBQdB04t89/1O/w1cDnyilFU=';

// Get POST body content
$content = file_get_contents('php://input');
// Parse JSON
$events = json_decode($content, true);
// Validate parsed JSON data
if (!is_null($events['events'])) {
	// Loop through each event
	foreach ($events['events'] as $event) {
		// Reply only when message sent is in 'text' format
		if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
			// Get text sent
			$texts = $event['message']['text'];
			if($texts == 'Hi' || $texts == 'Hello'){
				$text = "ว่าไงสัส";
			}elseif($texts == 'video'){
				$text = 'บริการ ส่ง Video สุดฮา ถึงมือท่านในทันที';
				$text_video = 'https://www.youtube.com/watch?v=ykPKcRfwrnY';
				$text_preview = 'https://i.ytimg.com/vi/ykPKcRfwrnY/hqdefault.jpg';
			}else{
				$text = $event['message']['text'];
			}
			
			// Get replyToken
			$replyToken = $event['replyToken'];

			// Build message to reply back
		if($texts == 'video'){
			$messages = [
				'type' => 'text',
				'text' => $text
			];

			$messages = [
				'type' => 'text',
				'originalContentUrl' => $text_video,
				'previewImageUrl' => $text_preview
			];
		}else{
			$messages = [
				'type' => 'text',
				'text' => $text
			];
		}
			// Make a POST Request to Messaging API to reply to sender
			$url = 'https://api.line.me/v2/bot/message/reply';
			$data = [
				'replyToken' => $replyToken,
				'messages' => [$messages],
			];
			$post = json_encode($data);
			$headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$result = curl_exec($ch);
			curl_close($ch);

			echo $result . "\r\n";
		}
	}
}
echo "OK";
?>