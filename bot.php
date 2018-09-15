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
    list($first, $mid, $last) = explode('-', $userMessage);
    switch ($typeMessage){
        case 'text':
            switch ($first) {
                case "t":
                    $textReplyMessage = "TR Autobot";
                    $replyData = new TextMessageBuilder($textReplyMessage);
                    break;
                case "help":
                    $textReplyMessage = "คำสั่งเบื้องต้น     I=>Image     Tel=>ตรวจเบอร์     Move=>เช็คตำแหน่ง";
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
                case "tel":
              /*
                  IF ($mid <> ''){
                    $telsql = "SELECT * FROM telephone where nickname = '.$mid.'";
                    $telquery = mysql_query($telsql)or die("Can't Query ".mysql_error() . " Actual query: " . $telsql);
                    $telchknum = mysql_num_rows($telquery);
                    IF ($telchknum > 0){
                      $telResult = mysql_fetch_array($telquery);
                      $lv_tel_data = "F";
                    }else{
                     $telsql = "SELECT * FROM telephone where name = '.$mid.'";
                     $telquery = mysql_query($telsql)or die("Can't Query ".mysql_error() . " Actual query: " . $telsql);
                     $telchknum2 = mysql_num_rows($telquery);
                     IF ($telchknum2 > 0){
                      $telResult = mysql_fetch_array($telquery);
                      $lv_tel_data = "F";
                     }else{
                      $lv_tel_data = "NF";
                     }
                    }
                    
                    switch ($lv_tel_data){
                     case "F":
                      $textReplyMessage = $telResult["name"]."(".$telResult["nickname"].") : ".$telResult["tel_no"];
                      $replyData = new TextMessageBuilder($textReplyMessage);
                      break;
                     case "NF":
                      $textReplyMessage = " ไม่พบข้อมูล ";
                      $replyData = new TextMessageBuilder($textReplyMessage);
                      break;
                    }
                  }else{
                   $textReplyMessage = "กรุณาพิมพ์ tel-<ชื่อ> เพื่อสอบถามข้อมูลเบอร์โปรศัพท์";
                   $replyData = new TextMessageBuilder($textReplyMessage);
                   break;
                  }
                  */
                    
                    switch ($mid) {
                     case "arm":
                       $textReplyMessage = "เบอร์ติดต่อ เหยิน : 080-646-6594 , 083-090-8433";
                       $replyData = new TextMessageBuilder($textReplyMessage);
                       break;
                     case "toy":
                         $textReplyMessage = "เบอร์ติดต่อ ทอย: 089-245-2436";
                         $replyData = new TextMessageBuilder($textReplyMessage);
                         break;
                      case "test":
                         $textReplyMessage = "เบอร์ติดต่อ ทดสอบ : 089-xxx-test";
                         $replyData = new TextMessageBuilder($textReplyMessage);
                         break;
                      case "pum":
                         $textReplyMessage = "เบอร์ติดต่อ ปุ้ม : 097-220-2000";
                         $replyData = new TextMessageBuilder($textReplyMessage);
                         break;
                      case "list":
                         $textReplyMessage = "คุณสามารถพิมพ์รายชื่อ ได้ตามด้านล่าง Arm | Toy | Pum | Test";
                         $replyData = new TextMessageBuilder($textReplyMessage);
                         break;
                      case "":
                         $textReplyMessage = "กรุณาพิมพ์ tel-<ชื่อ> เพื่อสอบถามข้อมูลเบอร์โปรศัพท์";
                         $replyData = new TextMessageBuilder($textReplyMessage);
                         break;
                      default:
                        $textReplyMessage = " ไม่พบข้อมูล ";
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
                //default:
                    //$textReplyMessage = "คำสั่งไม่ถูกต้อง กรุณาพิมพ์ HELP เพื่อตรวจสอบคำสั่ง";
                    //$replyData = new TextMessageBuilder($textReplyMessage);         
                    //break;                                      
            }
            break;
        default:
            $textReplyMessage = json_encode($events);
            $replyData = new TextMessageBuilder($textReplyMessage);         
            break;  
    }
}
//l ส่วนของคำสั่งตอบกลับข้อความ
$response = $bot->replyMessage($replyToken,$replyData);
if ($response->isSucceeded()) {
    echo 'Succeeded!';
    return;
}
 
// Failed
echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
?>
