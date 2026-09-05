<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Exception;
use Illuminate\Support\Facades\Log;

class ProductDeliveryQrService
{
    const TOKEN_PREFIX = 'SDHOD';

    /**
     * Signed identifier for a specific ordered product (order_details row).
     *
     * @param int $orderDetailId
     * @return string
     */
    public static function tokenForOrderDetail($orderDetailId)
    {
        return self::TOKEN_PREFIX . '-' . (int) $orderDetailId . '-' . self::signature((int) $orderDetailId);
    }

    /**
     * @param int $orderDetailId
     * @return string
     */
    protected static function signature($orderDetailId)
    {
        return substr(hash_hmac('sha256', 'order_detail:' . $orderDetailId, (string) config('app.key')), 0, 20);
    }

    /**
     * Resolve scanned QR data to an order and optional single order detail.
     * Existing invoice QRs contain orders.code. Item QRs use a signed token.
     *
     * @param string $qrCode
     * @return array|null
     */
    public static function resolve($qrCode)
    {
        $qrCode = trim(strip_tags((string) $qrCode));
        if ($qrCode === '') {
            return null;
        }

        if (preg_match('/^' . self::TOKEN_PREFIX . '-(\d+)-([a-f0-9]{20})$/i', $qrCode, $matches)) {
            $orderDetailId = (int) $matches[1];
            if (!hash_equals(self::signature($orderDetailId), strtolower($matches[2]))) {
                return null;
            }

            $orderDetail = OrderDetail::find($orderDetailId);
            if ($orderDetail === null || $orderDetail->order === null) {
                return null;
            }

            return array(
                'type' => 'order_detail',
                'order' => $orderDetail->order,
                'order_detail' => $orderDetail,
            );
        }

        $order = Order::where('code', $qrCode)->first();
        if ($order === null) {
            return null;
        }

        return array(
            'type' => 'order',
            'order' => $order,
            'order_detail' => null,
        );
    }

    /**
     * PNG binary for embedding in customer emails (CID). Uses GD so email
     * clients receive a real image without a public filesystem URL.
     *
     * @param string $payload
     * @param int $size
     * @return string|null
     */
    public static function pngBinary($payload, $size = 200)
    {
        $payload = (string) $payload;
        if ($payload === '' || !function_exists('imagecreatetruecolor')) {
            return null;
        }

        try {
            $qrCode = Encoder::encode($payload, ErrorCorrectionLevel::M());
            $matrix = $qrCode->getMatrix();
            $moduleCount = $matrix->getWidth();
            $quiet = 2;
            $modules = $moduleCount + ($quiet * 2);
            $scale = max(1, (int) floor(((int) $size) / $modules));
            $pixel = $modules * $scale;

            $image = imagecreatetruecolor($pixel, $pixel);
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            imagefill($image, 0, 0, $white);

            for ($y = 0; $y < $moduleCount; $y++) {
                for ($x = 0; $x < $moduleCount; $x++) {
                    if ((int) $matrix->get($x, $y) === 1) {
                        imagefilledrectangle(
                            $image,
                            ($x + $quiet) * $scale,
                            ($y + $quiet) * $scale,
                            (($x + $quiet + 1) * $scale) - 1,
                            (($y + $quiet + 1) * $scale) - 1,
                            $black
                        );
                    }
                }
            }

            ob_start();
            imagepng($image);
            $png = ob_get_clean();
            imagedestroy($image);

            return ($png !== false && $png !== '') ? $png : null;
        } catch (Exception $e) {
            Log::error('Delivery QR PNG generation failed', array(
                'error' => $e->getMessage(),
            ));

            return null;
        }
    }
}
