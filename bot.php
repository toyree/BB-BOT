<?php
// กรณีต้องการตรวจสอบการแจ้ง error ให้เปิด 3 บรรทัดล่างนี้ให้ทำงาน กรณีไม่ ให้ comment ปิดไป
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
 
// include composer autoload
require_once 'vendor/autoload.php';
 
// การตั้งเกี่ยวกับ bot
require_once 'bot_settings.php';
 
// กรณีมีการเชื่อมต่อกับฐานข้อมูล
//require_once("connect.php");

///////////// ส่วนของการเรียกใช้งาน class ผ่าน namespace
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
//use LINE\LINEBot\Event;
//use LINE\LINEBot\Event\BaseEvent;
//use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\ImagemapActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder ;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
 
// เชื่อมต่อกับ LINE Messaging API
$httpClient = new CurlHTTPClient(LINE_MESSAGE_ACCESS_TOKEN);
$bot = new LINEBot($httpClient, array('channelSecret' => LINE_MESSAGE_CHANNEL_SECRET));
$message = new MultiMessageBuilder();

// คำสั่งรอรับการส่งค่ามาของ LINE Messaging API
$content = file_get_contents('php://input');
 
// แปลงข้อความรูปแบบ JSON  ให้อยู่ในโครงสร้างตัวแปร array
$events = json_decode($content, true);
if(!is_null($events)){
    // ถ้ามีค่า สร้างตัวแปรเก็บ replyToken ไว้ใช้งาน
    $replyToken = $events['events'][0]['replyToken'];
    $typeMessage = $events['events'][0]['message']['type'];
    $userMessage = $events['events'][0]['message']['text'];
    $userMessage = strtolower($userMessage);

    //Get User ID and Type
    $userId = $events['events'][0]['source']['userId'];
    $userType = $events['events'][0]['source']['type']; //user

    list($first, $mid, $last, $lastf, $confirm) = explode(' ', $userMessage);
    switch ($typeMessage){
        case 'text':
            switch ($first) {
                case "t":
                    $textReplyMessage = "TR Autobot";
                    $replyData = new TextMessageBuilder($textReplyMessage);
                    break;
                case "help":
                    $textReplyMessage = "คำสั่งเบื้องต้น  Rate=> เช็คอัตราแลกเปลี่ยน   Cal=>คำนวณเงินตามอัตราแลกเปลี่ยน   Tel=>ตรวจเบอร์     Move=>เช็คตำแหน่ง";
                    $replyData = new TextMessageBuilder($textReplyMessage);
                    break;
                case "i":
                    $picFullSize = 'https://immense-journey-74194.herokuapp.com/pic1.jpg';
                    $picThumbnail = 'https://immense-journey-74194.herokuapp.com/pict1.jpg';
                    $replyData = new ImageMessageBuilder($picFullSize,$picThumbnail);
                    break;
                case "v":
                    $picThumbnail = 'https://immense-journey-74194.herokuapp.com/test01.jpg';
                    $videoUrl = "https://immense-journey-74194.herokuapp.com/test01.mp4";                
                    $replyData = new VideoMessageBuilder($videoUrl,$picThumbnail);
                    break;
                case "a":
                    $audioUrl = "https://www.mywebsite.com/simpleaudio.mp3";
                    $replyData = new AudioMessageBuilder($audioUrl,27000);
                    break;
                case "l":
                    $placeName = "ที่ตั้งร้าน";
                    $placeAddress = "แขวง พลับพลา เขต วังทองหลาง กรุงเทพมหานคร ประเทศไทย";
                    $latitude = 13.780401863217657;
                    $longitude = 100.61141967773438;
                    $replyData = new LocationMessageBuilder($placeName, $placeAddress, $latitude ,$longitude);              
                    break;
                        
                //Add Rate By Toy -- START --               
                case "rate":
                    IF($mid == '' || $mid == 'help' ){
                        $textReplyMessage = 'การใช้คำสั่ง Rate ให้พิมพ์ตามรูปแบบนี้     rate-<currency คั้งต้น>-<currency ปลายทาง>';
                        $replyData = new TextMessageBuilder($textReplyMessage);
                        break;
                        }elseif($mid <> '' && $mid <> 'help'){
                            $url = 'https://openexchangerates.org/api/latest.json?app_id=f23f7281781e426a9464af98371f1ae4';
                            $data = file_get_contents($url);
                            $result = json_decode($data);
                
                        if ($last == '') {
                            $up_m = strtoupper($mid);
                            $rate1 = $result->{'rates'}->$up_m;
                            if($rate1 == null){
                                $textReplyMessage = "คุณระบุ สกุลเงิน ไม่ถูกต้อง โปรดตรวจสอบอีกครั้ง";
                            }else{
                                $textReplyMessage = $up_m." Rate". "Today \n 1 USD is : " . $result->{'rates'}->$up_m." ".$up_m;
                            }
                        }else{
                            $up_m = strtoupper($mid);
                            $up_l = strtoupper($last);
                            $rate1 = $result->{'rates'}->$up_l;
                            $rate2 = $result->{'rates'}->$up_m;

                            if($rate1 == null || $rate2 == null){
                                $textReplyMessage = "คุณระบุ สกุลเงิน ไม่ถูกต้อง โปรดตรวจสอบอีกครั้ง";
                            }else{
                                $rates = $result->{'rates'}->$up_l / $result->{'rates'}->$up_m;
                                $textReplyMessage = "$up_l Rate". "Today \n 1 $up_m is : " . $rates ." ". $up_l;
                            }
                        }
                
                        $replyData = new TextMessageBuilder($textReplyMessage);
                        break;
                        }
                
                //Add Rate By Toy -- END --
        
                //Add Calculate Price with exchange rate --START--
                case "cal":
                    IF($mid == '' || $mid == 'help' ){
                        $textReplyMessage = 'การใช้คำสั่ง Cal ให้พิมพ์ตามรูปแบบนี้     cal-<ราคาสินค้า>-<currency คั้งต้น>-<currency ปลายทาง>';
                        $replyData = new TextMessageBuilder($textReplyMessage);
                        $textReplyMessage = 'หรือใช้คำสั่งในรูแปป  cal-<ราคาสินค้า>-<currency คั้งต้น> ระบบจะ default เป็น THB ให้เสมอ เช่น cal-100-jpy';
                        $replyData2 = new TextMessageBuilder($textReplyMessage);
                        break;
                    }elseif($mid <> '' && $mid <> 'help' && $last <> ''){
                        $url = 'https://openexchangerates.org/api/latest.json?app_id=f23f7281781e426a9464af98371f1ae4';
                        $data = file_get_contents($url);
                        $result = json_decode($data);

                         if ($lastf == '') {
                          $up_l = strtoupper($last);
                          $rate1 = $result->{'rates'}->$up_l;
                          if($rate1 == null){
                             $textReplyMessage = "คุณระบุ สกุลเงิน ไม่ถูกต้อง โปรดตรวจสอบอีกครั้ง"; 
                          }else{
                             $rates = $result->{'rates'}->THB / $result->{'rates'}->$up_l;
                             $prices = $rates * $mid;
                             $pf = number_format($prices, 2);
                             $textReplyMessage = "สินค้าชิ้นนี้ มีราคา : ". $pf. " บาทครับ"; 
                          }
                 
                        }else{
                          $up_l = strtoupper($last);
                          $up_lf = strtoupper($lastf);
                          $rate1 = $result->{'rates'}->$up_lf;
                          $rate2 = $result->{'rates'}->$up_l;
                          if( $rate1 == null || $rate2 == null){
                              $textReplyMessage = "คุณระบุ สกุลเงิน ไม่ถูกต้อง โปรดตรวจสอบอีกครั้ง";
                          }else{
                              $rates = $result->{'rates'}->$up_lf / $result->{'rates'}->$up_l;
                              $prices = $rates * $mid;
                              $pf = number_format($prices, 2);
                              //$textReplyMessage = "$up_l Rate". "Today \n 1 $up_m is : " . $rates ." ". $up_l;
                              $textReplyMessage = "สินค้าชินนี้ มีราคา ".$pf." ".$up_lf;
                            }
                        }

                    $replyData = new TextMessageBuilder($textReplyMessage);
                    $sticker_pack = rand(1,2);
                    //for command cal only
                    if($sticker_pack == '1'){
                        $sticker_id_rand = ['2','10','13','106','107','114','116','120','122','132','407','409','410','417','426','428']; 
                    }else{
                        $sticker_id_rand = ['22','28','45','140','144','156','157','158','167','172','176','177','512'];
                    }
                    $sticker_id = $sticker_id_rand[mt_rand(0, count($sticker_id_rand) - 1)];    
                    $stickerID = $sticker_id;
                    $packageID = $sticker_pack;
                    $replyData3 = new StickerMessageBuilder($packageID,$stickerID);
                    break;
                }
                break;
            //Add Calculate Price with exchange rate --END--
                
                case "tel":
                    switch ($mid) {
                      case "" :
                         $textReplyMessage = "กรุณาพิมพ์ tel <ชื่อ> เพื่อสอบถามข้อมูลเบอร์โปรศัพท์";
                         $replyData = new TextMessageBuilder($textReplyMessage);
                         break;
                         
                      default:
                        $url = 'https://www.trswork.com/linebot/tel.php?api=BB-BOT-XYZ&name='.$mid;
                        $data = file_get_contents($url);

                        $textReplyMessage = $data;
                        $replyData = new TextMessageBuilder($textReplyMessage);
                        break;    
                    }
                    break;

                //ADD Data to database use command 'add'
                 case "add":
                    switch ($mid) {
                      case "" || "help":
                         $textReplyMessage = "กรุณาพิมพ์ add <ชื่อข้อมูลที่ต้องการเพิ่ม>";
                         $replyData = new TextMessageBuilder($textReplyMessage);

                         $textReplyMessage = "เช่น add tel 0891234567 ais \n หรือ \n add tel 0891234567 ";
                         $replyData2 = new TextMessageBuilder($textReplyMessage);
                         break;

                    case "tel" :
                        if($last == '' && $lastf == ''){
                            $textReplyMessage = "กรุณาระบุ เบอร์โทรศัพท์ และ เครือข่าย ของท่าน";
                            $replyData = new TextMessageBuilder($textReplyMessage);

                            $textReplyMessage = "เช่น add tel 0891234567 ais เป็นต้น";
                            $replyData2 = new TextMessageBuilder($textReplyMessage);
                        }elseif ($last != '' && $lastf == '') {
                            $textReplyMessage = "กรุณาระบุ เครือข่าย ของท่าน";
                            $replyData = new TextMessageBuilder($textReplyMessage);

                            $textReplyMessage = "เช่น add tel ".$last." ais เป็นต้น";
                            $replyData2 = new TextMessageBuilder($textReplyMessage);
                        }else{
                            //$url = 'https://www.trswork.com/linebot/tel2.php?api=BB-BOT-XYZ&name='.$mid;
                            //$data = file_get_contents($url);
                            //$textReplyMessage = $data;
                            $textReplyMessage = "ระบบ ยังไม่เปิด บริการ เพิ่มหมายเลขโทรศัพท์";
                            $replyData = new TextMessageBuilder($textReplyMessage);
                        }

                         break;
                    }
                    break;

                //Register Line Account to  BB-Bot System
                case "regis":
                    if($mid != '' && $last == ''){
                        switch ($mid) {
                            case "":
                                $textReplyMessage = "กรุณาพิมพ์ regis <ชื่อของคุณ> เพื่อลงทะเบียน";
                                $replyData = new TextMessageBuilder($textReplyMessage);
                                break;

                            case "cancel":
                                $textReplyMessage = "ระบบยกเลิกการ ลงทะเบียน เรียบร้อย";
                                $replyData = new TextMessageBuilder($textReplyMessage);

                                $textReplyMessage = "หากต้องการลงทะเบียนใหม่ กรุณาพิมพ์ regis <ชื่อของคุณ> เพื่อลงทะเบียน";
                                $replyData2 = new TextMessageBuilder($textReplyMessage);
                                break;
                             
                            default:
                                $replyData = new TemplateMessageBuilder('Confirm register with BB-BOT',
                                            new ConfirmTemplateBuilder(
                                                'Confirm Register BB-BOT with name is : '.$mid,
                                                array(
                                                    new MessageTemplateActionBuilder(
                                                        'Yes',
                                                        'regis '.$mid.' Yes'
                                                    ),
                                                    new MessageTemplateActionBuilder(
                                                        'No',
                                                        'regis cancel'
                                                    )
                                                )
                                            )
                                        );
                                break;    
                        }
                    }elseif($mid != '' && $last != ''){

                        switch ($last) {
                            case 'yes':
                                $userId = md5($userId);
                                $url = 'https://www.trswork.com/linebot/regis.php?api=BB-BOT-REGISTER&name='.$mid.'&uid='.$userId;
                                $data = file_get_contents($url);

                                $textReplyMessage = $data;
                                $replyData = new TextMessageBuilder($textReplyMessage);
                                break;

                            default:
                                $textReplyMessage = 'กรุณา กดปุ่มจากหน้าจอ Confirm ผ่านมือถือ เท่านั้น!';
                                $replyData = new TextMessageBuilder($textReplyMessage);
                                break;
                        }
                    }else{
                        $textReplyMessage = 'กรุณาพิมพ์ regis <ชื่อของคุณ> เพื่อลงทะเบียน';
                        $replyData = new TextMessageBuilder($textReplyMessage);
                        break;
                    }
                    break;
                
                //For test new command only
                case "test":
                    switch ($mid) {
                        case "":
                            $textReplyMessage = "Test System";
                            $replyData = new TextMessageBuilder($textReplyMessage);
                            break;

                        case "con": //Confirm Menu
                            $textReplyMessage = "Confirm System";
                            $replyData = new TextMessageBuilder($textReplyMessage);
                            break;
                         
                        default:
                            //$url = 'https://www.trswork.com/linebot/tel2.php?api=BB-BOT-XYZ&name='.$mid;
                            //$data = file_get_contents($url);

                            //$textReplyMessage = $data;
                            $textReplyMessage = 'ยังไม่เปิดให้บริการ';
                            $replyData = new TextMessageBuilder($textReplyMessage);
                            break;    
                    }
                    
                    break;


                case "move":
                    switch ($mid) {
                     case "arm":
                       $textReplyMessage = "ปราจีน บุเรี่ยน";
                       $replyData = new TextMessageBuilder($textReplyMessage);
                       break;
                     case "toy":
                       $textReplyMessage = "อยู่ในเมือง อันแสนโหดร้าย";
                       $replyData = new TextMessageBuilder($textReplyMessage);
                       break;
                      case "tong":
                       $textReplyMessage = "ติดเมียน้อย อยู่";
                       $replyData = new TextMessageBuilder($textReplyMessage);
                       break;
                      case "pum":
                       $textReplyMessage = "กลัวเมีย";
                       $replyData = new TextMessageBuilder($textReplyMessage);
                       break;
                     case "test":
                       $textReplyMessage = "ทดสอบ ทดสอบ และ ทดสอบ";
                       $replyData = new TextMessageBuilder($textReplyMessage);
                       break;
                     case "":
                       $textReplyMessage = "กรุณาพิมพ์ move-<ชื่อ> เพื่อสอบถามข้อมูลที่อยู่";
                       $replyData = new TextMessageBuilder($textReplyMessage);
                       break;
                     default:
                       $textReplyMessage = " ไม่พบข้อมูล ";
                       $replyData = new TextMessageBuilder($textReplyMessage);
                       break;    
                    }
                break;

                /*

                case "s":
                    $stickerID = 22;
                    $packageID = 2;
                    $replyData = new StickerMessageBuilder($packageID,$stickerID);
                    break;

                case "im":
                    $imageMapUrl = 'https://www.mywebsite.com/imgsrc/photos/w/sampleimagemap';
                    $replyData = new ImagemapMessageBuilder(
                        $imageMapUrl,
                        'This is Title',
                        new BaseSizeBuilder(699,1040),
                        array(
                            new ImagemapMessageActionBuilder(
                                'test image map',
                                new AreaBuilder(0,0,520,699)
                                ),
                            new ImagemapUriActionBuilder(
                                'http://www.ninenik.com',
                                new AreaBuilder(520,0,520,699)
                                )
                        )); 
                    break;          
                case "tm":
                    $replyData = new TemplateMessageBuilder('Confirm Template',
                        new ConfirmTemplateBuilder(
                                'Confirm template builder',
                                array(
                                    new MessageTemplateActionBuilder(
                                        'Yes',
                                        'Text Yes'
                                    ),
                                    new MessageTemplateActionBuilder(
                                        'No',
                                        'Text NO'
                                    )
                                )
                        )
                    );
                    break;
                    */
                //default:
                    //$textReplyMessage = "คำสั่งไม่ถูกต้อง กรุณาพิมพ์ HELP เพื่อตรวจสอบคำสั่ง";
                    //$replyData = new TextMessageBuilder($textReplyMessage);         
                    //break;                                      
            }
            break;
        default:
            //$textReplyMessage = json_encode($events);
            //$replyData = new TextMessageBuilder($textReplyMessage);
            //$replyData = new TextMessageBuilder($userId." ".$userType);
            break;  
    }
}
// ส่งข้อความกลับ หลายข้อความ
$message->add( $replyData );
IF($replyData2 <> '') { $message->add( $replyData2 ); }
IF($replyData3 <> '') { $message->add( $replyData3 ); }
IF($replyData4 <> '') { $message->add( $replyData4 ); }
IF($replyData5 <> '') { $message->add( $replyData5 ); }

// ส่วนของคำสั่งตอบกลับข้อความ
$response = $bot->replyMessage($replyToken,$message);

if ($response->isSucceeded()) {
    echo 'Succeeded!';
    return;
}

// Failed
echo $response->getHTTPStatus() . ' ' . $response->getRawBody();

?>
