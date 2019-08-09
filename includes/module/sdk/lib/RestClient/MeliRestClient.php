<?php
/**
 * Class MeliRestClient
 */
class MeliRestClient extends AbstractRestClient
{
    const API_MELI_BASE_URL = 'https://api.mercadolibre.com';

    /**
     * @param $request
     * @param $version
     * @return array|null
     * @throws WC_WooMercadoPago_Exception
     */
    public static function get($request, $version)
    {
        $request['method'] = 'GET';
        return self::execAbs($request, $version, self::API_MELI_BASE_URL);
    }

    /**
     * @param $request
     * @param $version
     * @return array|null
     * @throws WC_WooMercadoPago_Exception
     */
    public static function post($request, $version)
    {
        $request['method'] = 'POST';
        return self::execAbs($request, $version, self::API_MELI_BASE_URL);
    }

    /**
     * @param $request
     * @param $version
     * @return array|null
     * @throws WC_WooMercadoPago_Exception
     */
    public static function put($request, $version)
    {
        $request['method'] = 'PUT';
        return self::execAbs($request, $version, self::API_MELI_BASE_URL);
    }

    /**
     * @param $request
     * @param $version
     * @return array|null
     * @throws WC_WooMercadoPago_Exception
     */
    public static function delete($request, $version)
    {
        $request['method'] = 'DELETE';
        return self::execAbs($request, $version, self::API_MELI_BASE_URL);
    }
}
