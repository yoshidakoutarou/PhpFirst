<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Service extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function index() {

        // 設定
        $params = array(
            'key' => 'AIzaSyASu1gHJdn7Js8LBshbTxc6fRtZYD9xxMU', // APIキー
            'timestamp' => time(), // タイムスタンプ
            'location' => '35.172564,136.886039', // 位置座標
            'language' => 'en', // 言語
        );

        // GETメソッドで指定がある場合
        foreach (array('location', 'timestamp', 'language') as $val) {
            if (isset($_GET[$val]) && $_GET[$val] != '') {
                $params[$val] = $_GET[$val];
            }
        }

        // リクエストURL
        $request_url = 'https://maps.googleapis.com/maps/api/timezone/json' . '?' . http_build_query($params);

        // cURLでリクエスト
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $request_url);
        curl_setopt($curl, CURLOPT_HEADER, 1);      // ヘッダーを取得する
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);   // 証明書の検証を行わない
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);   // curl_execの結果を文字列で返す
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);     // タイムアウトの秒数
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);   // リダイレクト先を追跡するか？
        curl_setopt($curl, CURLOPT_MAXREDIRS, 5);     // 追跡する回数

        $res1 = curl_exec($curl);

        var_dump($res1);
        $res2 = curl_getinfo($curl);
        curl_close($curl);

        // 取得したデータ
        $json = substr($res1, $res2['header_size']);  // 取得したデータ(JSONなど)
        $header = substr($res1, 0, $res2['header_size']);  // レスポンスヘッダー (検証に利用したい場合にどうぞ)
        // JSONデータをオブジェクト形式に変換する
        $obj = json_decode($json);

        // URLを表示用に整形 (検証用)
        foreach (array('location',) as $key) {
            if (isset($params[$key])) {
                $params[$key] = htmlspecialchars($params[$key], ENT_QUOTES, 'UTF-8');
            }
        }

        // HTML用
        $html = '';

        // 条件の指定
        $html .= '<h2>条件を指定する</h2>';
        $html .= '<p>条件を指定して、タイムゾーンの情報を取得してみて下さい。</p>';
        $html .= '<form>';
        $html .= '<p style="font-size:.9em; font-weight:700;"><label for="location">位置座標 (location)</label></p>';
        $html .= '<p style="margin:0 0 1em;"><input name="location" list="location-list" value="' . $params[$key] . '" placeholder=""></p>';
        $html .= '<datalist id="location-list">';
        $html .= '<option value="35.7780787,139.7972248">';
        $html .= '<option value="0.2136714,16.9848501">';
        $html .= '<option value="37.6,-95.665">';
        $html .= '<option value="55.3632592,-3.4433238">';
        $html .= '<option value="20.46,-157.505">';
        $html .= '</datalist>';
        $html .= '<p><button>検索する</button></p>';
        $html .= '</form>';

        // 実行結果の表示
        $html .= '<h2>実行結果</h2>';
        $html .= '<p>リクエストの実行結果です。</p>';

        // エラー判定
        if (!$obj || !isset($obj->status) || $obj->status != 'OK') {
            $html .= '<p><mark>タイムゾーンの情報を取得できませんでした…。</mark></p>';
        } else {
            // 各データ
            $timeZoneId = $obj->timeZoneId;  // タイムゾーンID
            $timeZoneName = $obj->timeZoneName;  // タイムゾーンの名称
            $rawOffset = $obj->rawOffset;  // UTCからのオフセット
            $dstOffset = $obj->dstOffset;  // サマータイムの場合のオフセット
            // 出力
            $html .= '<dl>';
            $html .= '<dt>ID</dt>';
            $html .= '<dd>' . $timeZoneId . '</dd>';
            $html .= '<dt>名称</dt>';
            $html .= '<dd>' . $timeZoneName . '</dd>';
            $html .= '<dt>UTCからのオフセット</dt>';
            $html .= '<dd>' . number_format($rawOffset) . '秒</dd>';
            $html .= '<dt>サマータイム期間の場合のオフセット</dt>';
            $html .= '<dd>' . number_format($dstOffset) . '秒</dd>';
            $html .= '</dl>';
        }

        // 取得したデータ
        $html .= '<h2>取得したデータ</h2>';
        $html .= '<p>下記のデータを取得できました。</p>';
        $html .= '<h3>JSON</h3>';
        $html .= '<p><textarea>' . $json . '</textarea></p>';
        $html .= '<h3>レスポンスヘッダー</h3>';
        $html .= '<p><textarea>' . $header . '</textarea></p>';

        // ブラウザに[$html]の内容を出力
        // 運用時はHTMLのヘッダーとフッターを付けましょう。
        echo $html;
    }

}
