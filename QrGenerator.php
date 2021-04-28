<?php

namespace SoV;

class QrGenerator
{
    const CACHE_GROUP = 'qrcode';
    private static string $googleQr = 'https://chart.googleapis.com/chart?cht=qr&chs=%d&chl=%s&chld=M';

    /**
     * Generate qr image. Using googleapis
     *
     * returns uri to generated file
     *
     * @param string $uri - uri in qr image
     * @param int $size - image size
     * @param null|string $fileName - name of file
     *
     * @return string
     */
    public static function getQrCodeImage($uri, $size = 200, $fileName = null)
    {
        if (!$uri) {
            return '';
        }
        if (!$fileName) {
            $fileName = 'qr-' . md5($uri);
        }

        $data = wp_cache_get($fileName, self::CACHE_GROUP);

        if (isset($_GET['regenerate_qr'])) {
            $data = false;
        }

        if (!$data || $data['uri'] != $uri) {
            self::prepareData($data, $uri);
            self::beforeUpload($data['file']);

            $qrContent = file_get_contents(sprintf(self::$googleQr, $size, $uri));

            if (!$qrContent) {
                return '';
            }

            $result    = wp_upload_bits($fileName . '.png', null, $qrContent);

            $data['file_uri'] = $result['url'];
            $data['file']     = $result['file'];
            wp_cache_set($fileName, $data, self::CACHE_GROUP);
        }
        return $data['file_uri'];
    }

    /**
     * Prepare data and convert data to array
     *
     * @param mixed $data
     *          [
     *          'uri'      => uri in qr file
     *          'file'     => file path
     *          'file_uri' => file uri
     *          ]
     * @param string $uri
    */
    private static function prepareData(&$data, $uri)
    {
        if (!is_array($data)) {
            $data = [
                'uri'      => '',
                'file'     => '',
                'file_uri' => ''
            ];
        }
        $data['uri'] = $uri;
    }

    /**
     * Checks if the old qr file exists and delete it
     *
     * @param string $file - file path
    */
    private static function beforeUpload($file)
    {
        if ($file && file_exists($file)) {
            wp_delete_file($file);
        }
    }
}
