<?php

/**
 * Telegram Bot API for PHP
 * @author Saman Zahedi <isamanzahedi@gmail.com>
 * @url http://saman.click
 */

if (file_exists(__DIR__ . 'TelegramErrorLogger.php')) {
    require_once __DIR__ . 'TelegramErrorLogger.php';
}

/**
 * @method array sendMessage(array $content)
 * @method array forwardMessage(array $content)
 * @method array sendPhoto(array $content)
 * @method array sendAudio(array $content)
 * @method array sendVideoNote(array $content)
 * @method array sendVoiceNote(array $content)
 * @method array sendDocument(array $content)
 * @method array sendAnimation(array $content)
 * @method array sendSticker(array $content)
 * @method array sendVideo(array $content)
 * @method array sendVoice(array $content)
 * @method array sendLocation(array $content)
 * @method array editMessageLiveLocation(array $content)
 * @method array stopMessageLiveLocation(array $content)
 * @method array setChatStickerSet(array $content)
 * @method array deleteChatStickerSet(array $content)
 * @method array sendMediaGroup(array $content)
 * @method array sendVenue(array $content)
 * @method array sendContact(array $content)
 * @method array sendChatAction(array $content)
 * @method array getUserProfilePhotos(array $content)
 * @method array kickChatMember(array $content)
 * @method array leaveChat(array $content)
 * @method array unbanChatMember(array $content)
 * @method array getChat(array $content)
 * @method array getChatAdministrators(array $content)
 * @method array getChatMembersCount(array $content)
 * @method array getChatMember(array $content)
 * @method array answerInlineQuery(array $content)
 * @method array setGameScore(array $content)
 * @method array answerCallbackQuery(array $content)
 * @method array editMessageText(array $content)
 * @method array editMessageCaption(array $content)
 * @method array editMessageReplyMarkup(array $content)
 * @method array sendInvoice(array $content)
 * @method array answerShippingQuery(array $content)
 * @method array answerPreCheckoutQuery(array $content)
 * @method array restrictChatMember(array $content)
 * @method array promoteChatMember(array $content)
 * @method array exportChatInviteLink(array $content)
 * @method array setChatPhoto(array $content)
 * @method array deleteChatPhoto(array $content)
 * @method array setChatTitle(array $content)
 * @method array setChatDescription(array $content)
 * @method array pinChatMessage(array $content)
 * @method array editMessageMedia(array $content)
 * @method array unpinChatMessage(array $content)
 * @method array getStickerSet(array $content)
 * @method array uploadStickerFile(array $content)
 * @method array createNewStickerSet(array $content)
 * @method array addStickerToSet(array $content)
 * @method array setStickerPositionInSet(array $content)
 * @method array deleteStickerFromSet(array $content)
 * @method array deleteMessage(array $content)
 * @method array setPassportDataErrors(array $content)
 * @method array sendGame(array $content)
 * @method array setGameHighScores(array $content)
 */
class Telegram
{
    const INLINE_QUERY = 'inline_query';
    const CALLBACK_QUERY = 'callback_query';
    const EDITED_MESSAGE = 'edited_message';
    const EDITED_CHANNEL_POST = 'edited_channel_post';
    const CHOSEN_INLINE_QUERY = 'chosen_inline_query';
    const SHIPPING_QUERY = 'shipping_query';
    const CHANNEL_POST = 'channel_post';
    const PRE_CHECKOUT_QUERY = 'pre_checkout_query';
    const REPLY = 'reply';
    const MESSAGE = 'message';
    const PHOTO = 'photo';
    const VIDEO = 'video';
    const AUDIO = 'audio';
    const VOICE = 'voice';
    const DOCUMENT = 'document';
    const LOCATION = 'location';
    const CONTACT = 'contact';
    const ANIMATION = 'animation';
    const STICKER = 'sticker';
    const VIDEONOTE = 'video_note';
    const TYPE_CHANNEL = 'channel';
    const TYPE_GROUP = 'group';
    const TYPE_SUPERGROUP = 'supergroup';
    const TYPE_PRIVATE = 'private';

    private $bot_token = '';
    private $data = [];
    private $updates = [];
    private $log_errors;
    private $proxy;

    public function __construct($bot_token, $log_errors = true, array $proxy = [])
    {
        $this->bot_token = $bot_token;
        $this->data = $this->getData();
        $this->log_errors = $log_errors;
        $this->proxy = $proxy;
    }

    public function getData()
    {
        if (empty($this->data)) {
            $rawData = file_get_contents('php://input');

            return json_decode($rawData, true);
        } else {
            return $this->data;
        }
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getUpdates($offset = 0, $limit = 100, $timeout = 0, $update = true)
    {
        $content = ['offset' => $offset, 'limit' => $limit, 'timeout' => $timeout];
        $this->updates = $this->endpoint('getUpdates', $content);
        if ($update) {
            if (count($this->updates['result']) >= 1) { //for CLI working.
                $last_element_id = $this->updates['result'][count($this->updates['result']) - 1]['update_id'] + 1;
                $content = ['offset' => $last_element_id, 'limit' => '1', 'timeout' => $timeout];
                $this->endpoint('getUpdates', $content);
            }
        }

        return $this->updates;
    }

    public function endpoint($api, array $content, $post = true)
    {
        $url = 'https://api.telegram.org/bot' . $this->bot_token . '/' . $api;
        if ($post) {
            $reply = $this->sendAPIRequest($url, $content);
        } else {
            $reply = $this->sendAPIRequest($url, [], false);
        }

        return json_decode($reply, true);
    }

    private function sendAPIRequest($url, array $content, $post = true)
    {
        if (isset($content['chat_id'])) {
            $url = $url . '?chat_id=' . $content['chat_id'];
            unset($content['chat_id']);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }
        if (!empty($this->proxy)) {
            if (array_key_exists('type', $this->proxy)) {
                curl_setopt($ch, CURLOPT_PROXYTYPE, $this->proxy['type']);
            }

            if (array_key_exists('auth', $this->proxy)) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxy['auth']);
            }

            if (array_key_exists('url', $this->proxy)) {
                curl_setopt($ch, CURLOPT_PROXY, $this->proxy['url']);
            }

            if (array_key_exists('port', $this->proxy)) {
                curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxy['port']);
            }
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if ($result === false) {
            $result = json_encode(['ok' => false, 'curl_error_code' => curl_errno($ch), 'curl_error' => curl_error($ch)]);
        }
        echo $result;
        curl_close($ch);
        if ($this->log_errors) {
            if (class_exists('TelegramErrorLogger')) {
                $loggerArray = ($this->getData() == null) ? [$content] : [$this->getData(), $content];
                TelegramErrorLogger::log(json_decode($result, true), $loggerArray);
            }
        }

        return $result;
    }

    public function getMe()
    {
        return $this->endpoint('getMe', [], false);
    }

    public function respondSuccess()
    {
        http_response_code(200);

        return json_encode(['status' => 'success']);
    }

    public function getFile($file_id)
    {
        $content = ['file_id' => $file_id];

        return $this->endpoint('getFile', $content)['result'];
    }

    public function downloadFile($telegram_file_path, $local_file_path)
    {
        $file_url = 'https://api.telegram.org/file/bot' . $this->bot_token . '/' . $telegram_file_path;
        $in = fopen($file_url, 'rb');
        $out = fopen($local_file_path, 'wb');

        while ($chunk = fread($in, 8192)) {
            fwrite($out, $chunk, 8192);
        }
        fclose($in);
        fclose($out);
    }

    public function setWebhook($url, $certificate = '')
    {
        if ($certificate == '') {
            $requestBody = ['url' => $url];
        } else {
            $requestBody = ['url' => $url, 'certificate' => "@$certificate"];
        }

        return $this->endpoint('setWebhook', $requestBody, true);
    }

    public function deleteWebhook()
    {
        return $this->endpoint('deleteWebhook', [], false);
    }

    public function getWebhookInfo()
    {
        return $this->endpoint('getWebhookInfo', [], false);
    }

    public function Caption()
    {
        return @$this->data['message']['caption'];
    }

    public function ChatID()
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['message']['chat']['id'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['chat']['id'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['chat']['id'];
        }
        if ($type == self::INLINE_QUERY) {
            return @$this->data['inline_query']['from']['id'];
        }

        return $this->data['message']['chat']['id'];
    }

    public function getUpdateType()
    {
        $update = $this->data;
        if (isset($update['inline_query'])) {
            return self::INLINE_QUERY;
        }
        if (isset($update['callback_query'])) {
            return self::CALLBACK_QUERY;
        }
        if (isset($update['edited_message'])) {
            return self::EDITED_MESSAGE;
        }
        if (isset($update['message']['text'])) {
            return self::MESSAGE;
        }
        if (isset($update['message']['photo'])) {
            return self::PHOTO;
        }
        if (isset($update['message']['video'])) {
            return self::VIDEO;
        }
        if (isset($update['message']['audio'])) {
            return self::AUDIO;
        }
        if (isset($update['message']['voice'])) {
            return self::VOICE;
        }
        if (isset($update['message']['contact'])) {
            return self::CONTACT;
        }
        if (isset($update['message']['location'])) {
            return self::LOCATION;
        }
        if (isset($update['message']['reply_to_message'])) {
            return self::REPLY;
        }
        if (isset($update['message']['animation'])) {
            return self::ANIMATION;
        }
        if (isset($update['message']['document'])) {
            return self::DOCUMENT;
        }
        if (isset($update['channel_post'])) {
            return self::CHANNEL_POST;
        }
        if (isset($update['message']['video_note'])) {
            return self::VIDEONOTE;
        }
        if (isset($update['message']['sticker'])) {
            return self::STICKER;
        }
        if (isset($update['channel_post'])) {
            return self::CHANNEL_POST;
        }
        if (isset($update['edited_channel_post'])) {
            return self::EDITED_CHANNEL_POST;
        }
        if (isset($update['chosen_inline_query'])) {
            return self::CHOSEN_INLINE_QUERY;
        }
        if (isset($update['shipping_query'])) {
            return self::SHIPPING_QUERY;
        }
        if (isset($update['pre_checkout_query'])) {
            return self::PRE_CHECKOUT_QUERY;
        }

        return false;
    }

    public function MessageID()
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['message']['message_id'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['message_id'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['message_id'];
        }
        if ($type == self::INLINE_QUERY) {
            return @$this->data['inline_query']['id'];
        }

        return $this->data['message']['message_id'];
    }

    public function ReplyToMessageID()
    {
        return $this->data['message']['reply_to_message']['message_id'];
    }

    public function ReplyToMessageFromUserID()
    {
        return $this->data['message']['reply_to_message']['forward_from']['id'];
    }

    public function InlineQuery()
    {
        if (isset($this->data['inline_query'])) {
            return @$this->data['inline_query'];
        } else {
            return false;
        }
    }

    public function Callback_Query()
    {
        return $this->data['callback_query'];
    }

    public function Callback_ID()
    {
        return $this->data['callback_query']['id'];
    }

    public function Callback_Data()
    {
        return $this->data['callback_query']['data'];
    }

    public function Callback_Message()
    {
        return $this->data['callback_query']['message'];
    }

    public function Callback_ChatID()
    {
        return $this->data['callback_query']['message']['chat']['id'];
    }

    public function Date()
    {
        return $this->data['message']['date'];
    }

    public function FirstName()
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['from']['first_name'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['from']['first_name'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['from']['first_name'];
        }
        if ($type == self::INLINE_QUERY) {
            return @$this->data['inline_query']['from']['first_name'];
        }

        return @$this->data['message']['from']['first_name'];
    }

    public function LastName()
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['from']['last_name'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['from']['last_name'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['from']['last_name'];
        }
        if ($type == self::INLINE_QUERY) {
            return @$this->data['inline_query']['from']['last_name'];
        }
        if (isset($this->data['message']['from']['last_name'])) {
            return @$this->data['message']['from']['last_name'];
        }

        return '';
    }

    public function Username()
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['from']['username'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['from']['username'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['from']['username'];
        }
        if ($type == self::INLINE_QUERY) {
            return @$this->data['inline_query']['from']['username'];
        }
        if (isset($this->data['message']['from']['username'])) {
            return @$this->data['message']['from']['username'];
        }

        return '';
    }

    public function Location()
    {
        return $this->data['message']['location'];
    }

    public function UpdateID()
    {
        return $this->data['update_id'];
    }

    public function UpdateCount()
    {
        return count($this->updates['result']);
    }

    public function UserID()
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return $this->data['callback_query']['from']['id'];
        }
        if ($type == self::CHANNEL_POST) {
            return $this->data['channel_post']['from']['id'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['from']['id'];
        }
        if ($type == self::INLINE_QUERY) {
            return @$this->data['inline_query']['from']['id'];
        }

        return @$this->data['message']['from']['id'];
    }

    public function FromID()
    {
        return $this->data['message']['forward_from']['id'];
    }

    public function FromChatID()
    {
        return $this->data['message']['forward_from_chat']['id'];
    }

    public function messageFromGroup()
    {
        if ($this->data['message']['chat']['type'] == 'private') {
            return false;
        }

        return true;
    }

    public function messageFromGroupTitle()
    {
        if ($this->data['message']['chat']['type'] != 'private') {
            return $this->data['message']['chat']['title'];
        }
    }

    public function buildKeyboard(array $options, $onetime = false, $resize = true, $selective = true)
    {
        $replyMarkup = [
            'keyboard' => $options,
            'one_time_keyboard' => $onetime,
            'resize_keyboard' => $resize,
            'selective' => $selective,
        ];
        $encodedMarkup = json_encode($replyMarkup, true);

        return $encodedMarkup;
    }

    public function buildInlineKeyboard(array $options, $encode = true)
    {
        $replyMarkup = [
            'inline_keyboard' => $options,
        ];
        if ($encode) {
            $encodedMarkup = json_encode($replyMarkup, true);
        } else {
            $encodedMarkup = $replyMarkup;
        }
        return $encodedMarkup;
    }

    public function InlineKeyboardButton($text, $callback_data = '', $url = '', $switch_inline_query = null, $switch_inline_query_current_chat = null, $callback_game = '', $pay = '')
    {
        $replyMarkup = [
            'text' => $text,
        ];
        if ($url != '') {
            $replyMarkup['url'] = $url;
        } elseif ($callback_data != '') {
            $replyMarkup['callback_data'] = $callback_data;
        } elseif (!is_null($switch_inline_query)) {
            $replyMarkup['switch_inline_query'] = $switch_inline_query;
        } elseif (!is_null($switch_inline_query_current_chat)) {
            $replyMarkup['switch_inline_query_current_chat'] = $switch_inline_query_current_chat;
        } elseif ($callback_game != '') {
            $replyMarkup['callback_game'] = $callback_game;
        } elseif ($pay != '') {
            $replyMarkup['pay'] = $pay;
        }

        return $replyMarkup;
    }

    public function KeyboardButton($text, $request_contact = false, $request_location = false)
    {
        $replyMarkup = [
            'text' => $text,
            'request_contact' => $request_contact,
            'request_location' => $request_location,
        ];

        return $replyMarkup;
    }

    public function RemoveKeyboard($selective = true)
    {
        $replyMarkup = [
            'remove_keyboard' => true,
            'selective' => $selective,
        ];
        $encodedMarkup = json_encode($replyMarkup, true);

        return $encodedMarkup;
    }

    public function buildForceReply($selective = true)
    {
        $replyMarkup = [
            'force_reply' => true,
            'selective' => $selective,
        ];
        $encodedMarkup = json_encode($replyMarkup, true);

        return $encodedMarkup;
    }

    public function serveUpdate($update)
    {
        $this->data = $this->updates['result'][$update];
    }

    public function User()
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['user'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['user'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['user'];
        }
        if ($type == self::INLINE_QUERY) {
            return @$this->data['inline_query']['user'];
        } else {
            if (isset($this->data['user'])) {
                return @$this->data['user'];
            } else {
                return '';
            }
        }
    }

    public function Chat()
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['message']['chat'];
        }
        if ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['message']['chat'];
        }
        if ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['message']['chat'];
        }
        if ($type == self::INLINE_QUERY) {
            return @$this->data['inline_query']['message']['chat'];
        } else {
            if (isset($this->data['message']['chat'])) {
                return @$this->data['message']['chat'];
            } else {
                return '';
            }
        }
    }

    public function Photo()
    {
        $type = $this->getUpdateType();
        if ($type == self::CHANNEL_POST) {
            if ($this->data['channel_post']['photo'])
                return end(@$this->data['channel_post']['photo']);
            else
                return false;
        }
        if ($this->data['message']['photo'])
            return end(@$this->data['message']['photo']);
        else
            return false;
    }

    public function Audio()
    {
        $type = $this->getUpdateType();
        if ($type == self::CHANNEL_POST) {
            if ($this->data['channel_post']['audio'])
                return @$this->data['channel_post']['audio'];
            else
                return false;
        }
        if ($this->data['message']['audio'])
            return @$this->data['message']['audio'];
        else
            return false;
    }

    public function Document()
    {
        $type = $this->getUpdateType();
        if ($type == self::CHANNEL_POST) {
            if ($this->data['channel_post']['document'])
                return @$this->data['channel_post']['document'];
            else
                return false;
        }
        if ($this->data['message']['document'])
            return @$this->data['message']['document'];
        else
            return false;
    }

    public function Video()
    {
        $type = $this->getUpdateType();
        if ($type == self::CHANNEL_POST) {
            if ($this->data['channel_post']['video'])
                return @$this->data['channel_post']['video'];
            else
                return false;
        }
        if ($this->data['message']['video'])
            return @$this->data['message']['video'];
        else
            return false;
    }

    public function Animation()
    {
        $type = $this->getUpdateType();
        if ($type == self::CHANNEL_POST) {
            if ($this->data['channel_post']['animation'])
                return @$this->data['channel_post']['animation'];
            else
                return false;
        }
        if ($this->data['message']['animation'])
            return @$this->data['message']['animation'];
        else
            return false;
    }

    public function Voice()
    {
        $type = $this->getUpdateType();
        if ($type == self::CHANNEL_POST) {
            if ($this->data['channel_post']['voice'])
                return @$this->data['channel_post']['voice'];
            else
                return false;
        }
        if ($this->data['message']['voice'])
            return @$this->data['message']['voice'];
        else
            return false;
    }

    public function VideoNote()
    {
        $type = $this->getUpdateType();
        if ($type == self::CHANNEL_POST) {
            if ($this->data['channel_post']['video_note'])
                return @$this->data['channel_post']['video_note'];
            else
                return false;
        }
        if ($this->data['message']['video_note'])
            return @$this->data['message']['video_note'];
        else
            return false;
    }

    public function Contact()
    {
        $type = $this->getUpdateType();
        if ($type == self::CHANNEL_POST) {
            if ($this->data['channel_post']['contact'])
                return @$this->data['channel_post']['contact'];
            else
                return false;
        }
        if ($this->data['message']['contact'])
            return @$this->data['message']['contact'];
        else
            return false;
    }

    public function Venue()
    {
        return $this->data['message']['venue'];
    }

    // keyboards

    public function CallbackQuery($sub = '')
    {
        if (isset($this->data['callback_query'])) {
            switch ($sub) {
                case '':
                    return @$this->data['callback_query'];
                    break;
                default:
                    return @$this->data['callback_query'][$sub];
            }
        } else {
            return false;
        }
    }

    public function Sticker()
    {
        $type = $this->getUpdateType();
        if ($type == self::CHANNEL_POST) {
            if ($this->data['channel_post']['sticker'])
                return @$this->data['channel_post']['sticker'];
            else
                return false;
        }
        if ($this->data['message']['sticker'])
            return @$this->data['message']['sticker'];
        else
            return false;
    }

    public function ReplyToMessage()
    {
        return $this->data['message']['reply_to_message'];
    }

    public function ForwardFrom()
    {
        return @$this->data['message']['forward_from'];
    }

    public function chatType()
    {
        if ($this->data['message']['chat']['type']) {
            if ($this->data['message']['chat']['type'] == 'private') {
                return self::TYPE_PRIVATE;
            } elseif ($this->data['message']['chat']['type'] == 'group') {
                return self::TYPE_GROUP;
            } elseif ($this->data['message']['chat']['type'] == 'supergroup') {
                return self::TYPE_SUPERGROUP;
            }
        } else {
            if ($this->data['channel_post']['chat']['type']) {
                return self::TYPE_CHANNEL;
            }
        }
        return true;
    }

    public function NewChatMembers()
    {
        if (isset($this->data['message']['new_chat_members'])) {
            return @$this->data['message']['new_chat_members'];
        }
        return false;
    }

    public function hasUrlEntity($except = [])
    {
        if (count($except) > 0) {
            foreach ($except as $exc) {
                if (stripos("." . $this->Text(), $exc['url'])) {
                    return false;
                }
            }
        }
        if (is_array($this->getEntities())) {
            foreach ($this->getEntities() as $Entity) {
                if ($Entity['type'] == 'url') {
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    public function Text()
    {
        $type = $this->getUpdateType();
        if ($type == self::CALLBACK_QUERY) {
            return @$this->data['callback_query']['data'];
        } elseif ($type == self::CHANNEL_POST) {
            return @$this->data['channel_post']['text'];
        } elseif ($type == self::EDITED_MESSAGE) {
            return @$this->data['edited_message']['text'];
        } elseif ($type == self::INLINE_QUERY) {
            return @$this->data['inline_query']['query'];
        } else {
            return @$this->data['message']['text'];
        }
    }

    public function getEntities()
    {
        if (isset($this->data['message']['entities'])) {
            return @$this->data['message']['entities'];
        }
        if (isset($this->data['message']['caption_entities'])) {
            return @$this->data['message']['caption_entities'];
        }

        return false;
    }

    public function isBotAdmin($botUsername, $chatId)
    {
        $admins = $this->getChatAdministrators(['chat_id' => $chatId]);
        $imi = false;
        foreach ($admins as $admin) {
            if ($admin['user']['username'] == $botUsername) {
                return true;
            }
        }
        return false;
    }

    public function __call($name, $arguments)
    {
        $content = $arguments[0];
        $content['text'] = ' ' . $content['text'];
        return $this->endpoint($name, $arguments[0]);
    }
}

if (!function_exists('curl_file_create')) {
    function curl_file_create($filename, $mimetype = '', $postname = '')
    {
        return "@$filename;filename="
            . ($postname ?: basename($filename))
            . ($mimetype ? ";type=$mimetype" : '');
    }
}
