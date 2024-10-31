<?php

require_once 'http_build_url.php';
require_once 'html_dom.php';
require_once 'xtemplate.class.php';

class RPWidget {

    private $rpWidgetSettings;

    public function __construct($settings) {
        $this->rpWidgetSettings = $settings;
    }

    /**
     * fetch html from readypulse server
     */
    private function fetchHtmlDataFromReadyPulse() {

        $rp_url = $this->createRPWidgetUrl();

        $htmldata = $this->makeCurlRequest($rp_url);
        $html = $this->getCleanHtml($htmldata);
        return $html;
    }

    /**
     * fetch json from readypulse server
     */
    private function fetchJsonDataFromReadyPulse() {

        $rp_url = $this->createRPWidgetUrl();

        $jsondata = $this->makeCurlRequest($rp_url);
        $jsonhtml = $this->getXTemplateForJsonData($jsondata);
        return $jsonhtml;
    }

    /**
     * create widget url according to the perameters
     */
    private function createRPWidgetUrl() {
        $rp_url = $this->rpWidgetSettings['widgeturl'];

        $url = 'http://widgets.readypulse.com/curations/';

        if (substr($rp_url, 0, 4) != 'http' && $rp_url != '') {
            //prepend http:// to beginning
            $rp_url = "http://" . $rp_url;
        }
        if (strpos($rp_url, $url) === false) {
            $rp_url = $url;
        }

        if ($rp_url == $url) {
            $rp_url = $rp_url . $this->rpWidgetSettings['id'] . '/embed/';
        } else {
            $rp_url_arr = explode('/', $rp_url);

            if ($this->rpWidgetSettings['id'])
                $rp_url_arr[4] = $this->rpWidgetSettings['id'];

            $rp_url = implode('/', $rp_url_arr);
        }

        if ($this->rpWidgetSettings['nativelook']) {
            if (strpos($rp_url, 'api.json') === false) {
                $rp_url_arr = explode('/', $rp_url);
                if (empty($rp_url_arr[6]))
                    $rp_url_arr[6] = 'api.json';

                $rp_url = implode('/', $rp_url_arr);
            }


            if (strpos($rp_url, 'api.html')) {
                $rp_url = str_replace('api.html', 'api.json', $rp_url);
            }
        } else {
            if (strpos($rp_url, 'api.html') === false) {
                $rp_url_arr = explode('/', $rp_url);
                if (empty($rp_url_arr[6]))
                    $rp_url_arr[6] = 'api.html';

                $rp_url = implode('/', $rp_url_arr);
            }

            if (strpos($rp_url, 'api.json')) {
                $rp_url = str_replace('api.json', 'api.html', $rp_url);
            }

            if ($this->rpWidgetSettings['height']) {
                $rp_url = addURLParameter($rp_url, 'height', $this->rpWidgetSettings['height']);
            }
            if ($this->rpWidgetSettings['width']) {
                $rp_url = addURLParameter($rp_url, 'width', $this->rpWidgetSettings['width']);
            } else {
                $rp_url = addURLParameter($rp_url, 'width', 'auto');
            }
            if ($this->rpWidgetSettings['type']) {
                $rp_url = addURLParameter($rp_url, 'type', ($this->rpWidgetSettings['type']) ? $this->rpWidgetSettings['type'] : 'feed');
            }
            if ($this->rpWidgetSettings['theme']) {
                $rp_url = addURLParameter($rp_url, 'theme', $this->rpWidgetSettings['theme']);
            }
        }

        if ($this->rpWidgetSettings['scope']) {
            $rp_url = addURLParameter($rp_url, 'scope', $this->rpWidgetSettings['scope']);
        }

        if ($this->rpWidgetSettings['agent']) {
            $rp_url = addURLParameter($rp_url, 'agent', $this->rpWidgetSettings['agent']);
        }

        if ($this->rpWidgetSettings['ref']) {
            $ref = $this->rpWidgetSettings['ref'];
            if ($ref)
                $ref = urlencode($ref);
            $rp_url = addURLParameter($rp_url, 'ref', $ref, false);
        }

        $rp_url = addURLParameter($rp_url, 'src', $this->rpWidgetSettings['src']);
        
        return $rp_url;
    }

    /**
     * get xtemplate for json data
     */
    private function getXTemplateForJsonData($jsondata) {
        if ($jsondata) {
            $json = json_decode($jsondata);

            $xtpl_main = New XTemplate('xtpl/' . $this->rpWidgetSettings['plugintype'] . '/main.xtpl');
            $style_width_height = '';
            if ($this->rpWidgetSettings['width'])
                $style_width_height = 'width:' . $this->rpWidgetSettings['width'] . 'px; ';
            if ($this->rpWidgetSettings['height'])
                $style_width_height .= 'height:' . $this->rpWidgetSettings['height'] . 'px; ';

            if ($style_width_height)
                $style_width_height = ' style="' . $style_width_height . '"';
            $xtpl_main->assign('style_width_height', $style_width_height);

            if ($this->rpWidgetSettings['showheader']) {
                if (!$json->image)
                    $json->image = $json->default_image;

                $xtpl_main->assign('headername', $json->name);
                $xtpl_main->assign('headerimage', $json->image);
                $xtpl_main->assign('headerdescription', $json->description);
                $xtpl_main->assign('headerdatecreated', $this->getDateFormat(str_replace(array('T', 'Z'), ' ', $json->created_at), 'M d, Y h:i A'));
                $xtpl_main->parse('main.header');
            }

            if ($json->curated_list) {
                $curated_list = '';
                foreach ($json->curated_list as $data) {
                    $photo = '';
                    $video = '';

                    $xtpl_main->assign('contributor_url', $data->contributor->url);
                    $xtpl_main->assign('contributor_name', $data->contributor->name);
                    $xtpl_main->assign('contributor_image_url', $data->contributor->image_url);
                    $contributor_text = (!isset($data->details) && ($data->post_type == 'photo' || $data->post_type == 'video' )) ? $data->details : $data->text;
                    $xtpl_main->assign('contributor_text', $this->makeLinkClickableInJson($contributor_text));

                    if ($data->post_type == 'photo') {
                        $photo = $this->getXtplPhotoFromJson($data);
                    } else if ($data->post_type == 'video') {
                        $video = $this->getXtplVideoFromJson($data);
                    } else {
                        if (!empty($data->asset_type)) {

                            $sub_like_text = '';
                            if ($data->asset_type == 'facebook') {

                                $my_id = explode('_', $data->my_id);
								$face_book_url = isset($data->external_conversation_link) ? $data->external_conversation_link : 'http://www.facebook.com/' . $my_id[0] . '/posts/' . $my_id[1];
                                $xtpl_main->assign('facebook_thread_url', $face_book_url);
                                $xtpl_main->parse('main.curated_list.facebook_link');

                                if ($data->attributes->Likes)
                                    $sub_like_text .= $data->attributes->Likes . ' Likes ';
                                if ($data->attributes->Comments)
                                    $sub_like_text .= $data->attributes->Comments . ' Comments ';
                                if ($data->attributes->Shares)
                                    $sub_like_text .= $data->attributes->Shares . ' Shares';
                            } else if ($data->asset_type == 'twitter') {
                                $my_id = explode('_', $data->my_id);
								$twit_book_url = isset($data->external_conversation_link) ? $data->external_conversation_link : $data->contributor->url . '/status/' . $my_id[0];
                                $xtpl_main->assign('twitter_thread_url', $twit_book_url);

                                $xtpl_main->parse('main.curated_list.twitter_link');

                                if ($data->attributes->Replies)
                                    $sub_like_text .= $data->attributes->Replies . ' Replies ';
                                if ($data->attributes->Retweets)
                                    $sub_like_text .= $data->attributes->Retweets . ' Retweets ';
                            }

                            $xtpl_main->assign('sub_like_text', $sub_like_text);
                            $xtpl_main->assign('asset_type', $data->asset_type);
                            $xtpl_main->assign('pubdate', $this->getDateFormat(str_replace(array('T', 'Z'), ' ', $data->timestamp), 'M d, Y h:i A'));
                            $xtpl_main->parse('main.curated_list.asset_type');
                        }
                    }
                    $xtpl_main->assign('photo', $photo);
                    $xtpl_main->assign('video', $video);

                    $xtpl_main->parse('main.curated_list');
                }
            }

            if ($this->rpWidgetSettings['showfooter']) {
                $xtpl_main->parse('main.footer');
            }

            $xtpl_main->parse('main');

            $html = $xtpl_main->text();
        } else {
            $html = '';
        }
        return $html;
    }

    /**
     * fetch xtemplate for photo type feed
     */
    private function getXtplPhotoFromJson($data) {

        $photo = '';

        $xtpl_photo = New XTemplate('xtpl/' . $this->rpWidgetSettings['plugintype'] . '/photo.xtpl');
        $xtpl_photo->assign('photo_url', $data->url);
        $xtpl_photo->assign('photo_pic_url', $data->pic_url);

        $photo_text = (isset($data->details)) ? $data->details : $data->text;
        $xtpl_photo->assign('photo_text', $this->makeLinkClickableInJson($photo_text));

        if ($data->post_type == 'photo' && $data->pic_url) {
            $xtpl_photo->parse('main.sub_img');
        } else {
            $xtpl_photo->parse('main.sub_img_wall');
        }

        if (!empty($data->asset_type)) {

            $sub_like_text = '';
            if ($data->asset_type == 'facebook') {

                $my_id = explode('_', $data->my_id);
                $face_book_url = isset($data->external_conversation_link) ? $data->external_conversation_link : 'http://www.facebook.com/' . $my_id[0] . '/posts/' . $my_id[1];
                $xtpl_photo->assign('facebook_thread_url', $face_book_url);
                $xtpl_photo->parse('main.facebook_link');

                if ($data->attributes->Likes)
                    $sub_like_text .= $data->attributes->Likes . ' Likes ';
                if ($data->attributes->Comments)
                    $sub_like_text .= $data->attributes->Comments . ' Comments ';
                if ($data->attributes->Shares)
                    $sub_like_text .= $data->attributes->Shares . ' Shares';
            } else if ($data->asset_type == 'twitter') {

                $twit_book_url = isset($data->external_conversation_link) ? $data->external_conversation_link : $data->contributor->url . '/status/' . $my_id[0];

                $xtpl_photo->assign('twitter_thread_url', $twit_book_url);
                $xtpl_photo->parse('main.twitter_link');

                if ($data->attributes->Replies)
                    $sub_like_text .= $data->attributes->Replies . ' Replies ';
                if ($data->attributes->Retweets)
                    $sub_like_text .= $data->attributes->Retweets . ' Retweets ';
            }
        }

        $xtpl_photo->assign('sub_like_text', $sub_like_text);
        $xtpl_photo->assign('asset_type', $data->asset_type);
        $xtpl_photo->assign('pubdate', $this->getDateFormat(str_replace(array('T', 'Z'), ' ', $data->timestamp), 'M d, Y h:i A'));

        if (!empty($data->children)) {
            $photo_children = $this->getThreadChildrenFromJson($data->children);
            $xtpl_photo->assign('photo_children', $photo_children);
        }

        $xtpl_photo->parse('main');
        $photo = $xtpl_photo->text();

        return $photo;
    }

    /**
     * fetch xtemplate for video type feed
     */
    private function getXtplVideoFromJson($data) {

        $video = '';

        $xtpl_video = New XTemplate('xtpl/' . $this->rpWidgetSettings['plugintype'] . '/video.xtpl');

        $data->source = $data->source . '&autoplay=0';

        $xtpl_video->assign('video_src', str_replace('autoplay=1', 'autoplay=0', $data->source));

        $xtpl_video->assign('video_url', $data->url);
        $xtpl_video->assign('video_name', $data->name);

        $video_text = (isset($data->details)) ? $data->details : $data->text;
        $xtpl_video->assign('video_text', $this->makeLinkClickableInJson($video_text));

        if (!empty($data->asset_type)) {

            $sub_like_text = '';
            if ($data->asset_type == 'facebook') {

                $my_id = explode('_', $data->my_id);
                $face_book_url = isset($data->external_conversation_link) ? $data->external_conversation_link : 'http://www.facebook.com/' . $my_id[0] . '/posts/' . $my_id[1];
                $xtpl_video->assign('facebook_thread_url', $face_book_url);
                $xtpl_video->parse('main.facebook_link');

                if ($data->attributes->Likes)
                    $sub_like_text .= $data->attributes->Likes . ' Likes ';
                if ($data->attributes->Comments)
                    $sub_like_text .= $data->attributes->Comments . ' Comments ';
                if ($data->attributes->Shares)
                    $sub_like_text .= $data->attributes->Shares . ' Shares';
            } else if ($data->asset_type == 'twitter') {

                $twit_book_url = isset($data->external_conversation_link) ? $data->external_conversation_link : $data->contributor->url . '/status/' . $my_id[0];

                $xtpl_video->assign('twitter_thread_url', $twit_book_url);
                $xtpl_video->parse('main.twitter_link');

                if ($data->attributes->Replies)
                    $sub_like_text .= $data->attributes->Replies . ' Replies ';
                if ($data->attributes->Retweets)
                    $sub_like_text .= $data->attributes->Retweets . ' Retweets ';
            }
        }

        $xtpl_video->assign('sub_like_text', $sub_like_text);
        $xtpl_video->assign('asset_type', $data->asset_type);
        $xtpl_video->assign('pubdate', $this->getDateFormat(str_replace(array('T', 'Z'), ' ', $data->timestamp), 'M d, Y h:i A'));

        if (!empty($data->children)) {
            $video_children = $this->getThreadChildrenFromJson($data->children);
            $xtpl_video->assign('video_children', $video_children);
        }

        $xtpl_video->parse('main');
        $video = $xtpl_video->text();

        return $video;
    }

    /**
     * fetch xtemplate for feed's children
     */
    private function getThreadChildrenFromJson($json) {
        $xtpl_main = New XTemplate('xtpl/' . $this->rpWidgetSettings['plugintype'] . '/children.xtpl');
        foreach ($json as $data) {
            $xtpl_main->assign('contributor_url', $data->contributor->url);
            $xtpl_main->assign('contributor_name', $data->contributor->name);
            $xtpl_main->assign('contributor_image_url', $data->contributor->image_url);
            $contributor_text = ($data->details) ? $data->text : $data->text;
            $xtpl_main->assign('contributor_text', $this->makeLinkClickableInJson($contributor_text));

            if (!empty($data->asset_type)) {

                $sub_like_text = '';
                if ($data->asset_type == 'facebook') {

                    if ($data->attributes->Likes)
                        $sub_like_text .= $data->attributes->Likes . ' Likes ';
                    if ($data->attributes->Comments)
                        $sub_like_text .= $data->attributes->Comments . ' Comments ';
                    if ($data->attributes->Shares)
                        $sub_like_text .= $data->attributes->Shares . ' Shares';
                } else if ($data->asset_type == 'twitter') {

                    if ($data->attributes->Replies)
                        $sub_like_text .= $data->attributes->Replies . ' Replies ';
                    if ($data->attributes->Retweets)
                        $sub_like_text .= $data->attributes->Retweets . ' Retweets ';
                }
            }

            $xtpl_main->assign('sub_like_text', $sub_like_text);
            $xtpl_main->assign('asset_type', $data->asset_type);
            $xtpl_main->assign('pubdate', $this->getDateFormat(str_replace(array('T', 'Z'), ' ', $data->timestamp), 'M d, Y h:i A'));


            $xtpl_main->parse('main.curated_list');
        }

        $xtpl_main->parse('main');
        return $xtpl_main->text();
    }

    /**
     * filter the html recieved from server
     */
    private function getCleanHtml($html_string) {
        $text = '';
        $js = '';
        if (strpos($html_string, '<head') && strpos($html_string, '<body')) {
            $html = str_get_html($html_string);
            foreach ($html->find('head') as $h)
                $js .= $h->innertext;


            foreach ($html->find('body') as $e)
                $text .= $e->innertext;
        } else {
            $text .= $html_string;
        }


        return $js . $text;
    }

    /**
     * make link clickable with in the recieved json data
     */
    private function makeLinkClickableInJson($text) {
        $text = html_entity_decode($text);
        $text = " " . $text;
        $text = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="\\1" target=_blank>\\1</a>', $text);
        $text = eregi_replace('(((f|ht){1}tps://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="\\1" target=_blank>\\1</a>', $text);
        $text = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '\\1<a href="http://\\2" target=_blank>\\2</a>', $text);
        $text = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})', '<a href="mailto:\\1" target=_blank>\\1</a>', $text);
        return $text;
    }

    /**
     * create a curl request to fetch data from API server
     */
    private function makeCurlRequest($rp_url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $rp_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            $output = '';
        }
        return $output;
    }

    /**
     * get date format for feed in the case of json data
     */
    private function getDateFormat($string, $format) {
        preg_match('#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})#', $string, $matches);
        $string_time = gmmktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
        $string_localtime = gmdate($format, $string_time + 3600);
        return $string_localtime;
    }

    /**
     * function to get xTemplate for both html
     */
    public function getXTemplate() {
        $nativelook = $this->rpWidgetSettings['nativelook'];
        if ($nativelook == false) {
            $redypulse_data = $this->fetchHtmlDataFromReadyPulse();
        } else {
            $redypulse_data = $this->fetchJsonDataFromReadyPulse();
        }

        return $redypulse_data;
    }

}