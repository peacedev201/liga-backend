<?php

/**
 * Send success response to the request.
 *
 * @param  array  $data
 * @param  int  $code
 * @param  string  $message
 * @return \Illuminate\Http\JsonResponse
 */
function successResponse($data = null, $code = 200, $message = null)
{
    if (!$message) {
        $message = __('httpmessages.200');
    }

    $response = [
        'status' => [
            'success' => true,
            'code' => $code,
            'message' => $message
        ],
    ];

    if (gettype($data) == 'object' && ("App\Http\Resources\BaseResource" == get_parent_class(get_class($data)) || "Illuminate\Http\Resources\Json\ResourceCollection" == get_parent_class(get_class($data)))) {
        return $data->additional($response);
    } else {
        if ($data) {
            $response['data'] = $data;
        }
        return response()->json($response);
    }
}

/**
 * Send error response to the request.
 *
 * @param  array  $error
 * @param  int  $code
 * @param  string  $message
 * @return \Illuminate\Http\JsonResponse
 */


function errorResponse($code = 200, $message = null, $error = null)
{
    if (!$message) {
        $message = __('httpmessages.500');
    }

    $response = [
        'status' => [
            'success' => false,
            'code' => $code,
            'message' => $message,
        ],
    ];
    if ($error) {
        $response['errors'] = $error;
    }
    
    return response()->json($response);
}

/**
 * Remove all digits after decimal except two.
 *
 * @param  array  $error
 * @param  int  $code
 * @param  string  $message
 * @return \Illuminate\Http\JsonResponse
 */
function removeDigitsAfterDecimalExceptTwo($value)
{
    if ($value) {
        return number_format((float) $value, 2, '.', '');
    }
}

function formatDate($date)
{
    return date("d M Y", strtotime($date));
}

/**
 * Format price.
 *
 * @param  float  $price
 * @return string
 */
function formatPrice($price)
{
    return number_format($price, 2);
}

/**
 * Convert Non Zero Number to Zero.
 *
 * @param  float  $number
 * @return float
 */
function convertNonZeroNumbertoZero($number)
{
    return sprintf("%02d", $number);
}

/**
 * Format timestamp
 *
 * @param  float  $number
 * @return float
 */
function formatTimestamp($date)
{
    return $date;
}
