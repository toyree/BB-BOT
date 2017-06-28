<?php
$access_token = 'LvT9xy2S/PpEYV13/nYmuiKPmCN32IgDIedHiPx/Yblb/o56FfA1O/jm5nQPkWfLlbGe0+qqULp/RcnNz6lYX6VVX22NejRzzxfPINneai29WceiQFJ04BMUhSLeJoJd/ffZW+lFzjwFudtrgD1FBQdB04t89/1O/w1cDnyilFU=';

$url = 'https://api.line.me/v1/oauth/verify';

$headers = array('Authorization: Bearer ' . $access_token);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$result = curl_exec($ch);
curl_close($ch);

echo $result;

?>