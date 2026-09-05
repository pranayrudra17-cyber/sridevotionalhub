<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AffiliateController;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\ProductDeliveryQrService;
use App\Utility\NotificationUtility;
use App\Utility\SmsUtility;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductDeliveryScanController extends Controller
{
    /**
     * Statuses that cannot be marked delivered.
     *
     * @var array
     */
    protected $blockedStatuses = array('cancelled', 'returned', 'refunded');

    /**
     * Statuses that may transition to delivered.
     *
     * @var array
     */
    protected $eligibleStatuses = array(
        'pending',
        'confirmed',
        'processing',
        'picked_up',
        'on_the_way',
        'out_for_delivery',
        'shipped',
    );

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check()) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(array(
                        'success' => false,
                        'message' => translate('Unauthenticated.'),
                    ), 401);
                }

                return redirect()->route('login');
            }

            $userType = Auth::user()->user_type;
            if ($userType !== 'admin' && $userType !== 'staff') {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(array(
                        'success' => false,
                        'message' => translate('Forbidden.'),
                    ), 403);
                }

                abort(403, translate('Forbidden.'));
            }

            return $next($request);
        });
    }

    public function scanner()
    {
        return view('backend.product.scan_delivery');
    }

    public function scanDelivery(Request $request)
    {
        $validator = Validator::make($request->all(), array(
            'qr_code' => 'required|string|max:255',
        ));

        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'message' => translate('Invalid or malformed request.'),
            ), 422);
        }

        $resolved = ProductDeliveryQrService::resolve($request->input('qr_code'));
        if ($resolved === null) {
            return response()->json(array(
                'success' => false,
                'message' => translate('Invalid product QR code.'),
            ), 404);
        }

        try {
            $result = DB::transaction(function () use ($resolved) {
                $order = Order::where('id', $resolved['order']->id)->lockForUpdate()->first();
                if ($order === null) {
                    return array(
                        'http' => 404,
                        'body' => array(
                            'success' => false,
                            'message' => translate('Invalid product QR code.'),
                        ),
                    );
                }

                if ($resolved['type'] === 'order_detail') {
                    $orderDetail = OrderDetail::where('id', $resolved['order_detail']->id)
                        ->lockForUpdate()
                        ->first();

                    if ($orderDetail === null || (int) $orderDetail->order_id !== (int) $order->id) {
                        return array(
                            'http' => 404,
                            'body' => array(
                                'success' => false,
                                'message' => translate('Product/order item not found.'),
                            ),
                        );
                    }

                    return $this->deliverOrderDetails($order, array($orderDetail), true);
                }

                $orderDetails = OrderDetail::where('order_id', $order->id)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($orderDetails->isEmpty()) {
                    return array(
                        'http' => 404,
                        'body' => array(
                            'success' => false,
                            'message' => translate('Product/order item not found.'),
                        ),
                    );
                }

                return $this->deliverOrderDetails($order, $orderDetails->all(), false);
            });
        } catch (Exception $e) {
            Log::error('QR delivery scan failed', array(
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
            ));

            return response()->json(array(
                'success' => false,
                'message' => translate('Unable to update delivery status. Please try again.'),
            ), 500);
        }

        if (!empty($result['delivered_order_details'])) {
            try {
                foreach ($result['delivered_order_details'] as $deliveredDetail) {
                    NotificationUtility::sendOrderItemDeliveredNotification($result['order'], $deliveredDetail);
                }
                $this->notifyDeliveryIfOrderDelivered($result['order']);
            } catch (Exception $e) {
                Log::error('QR delivery notification failed after status update', array(
                    'error' => $e->getMessage(),
                    'order_id' => isset($result['order']) ? $result['order']->id : null,
                    'admin_id' => Auth::id(),
                ));
            }
        }

        return response()->json($result['body'], $result['http']);
    }

    /**
     * @param \App\Models\Order $order
     * @param array $orderDetails
     * @param bool $singleItem
     * @return array
     */
    protected function deliverOrderDetails(Order $order, array $orderDetails, $singleItem)
    {
        $alreadyDelivered = array();
        $blocked = array();
        $eligible = array();

        foreach ($orderDetails as $orderDetail) {
            $status = strtolower((string) $orderDetail->delivery_status);
            if ($status === 'delivered') {
                $alreadyDelivered[] = $orderDetail;
            } elseif (in_array($status, $this->blockedStatuses, true)) {
                $blocked[] = $orderDetail;
            } elseif (in_array($status, $this->eligibleStatuses, true)) {
                $eligible[] = $orderDetail;
            } else {
                $blocked[] = $orderDetail;
            }
        }

        if (count($eligible) === 0) {
            if (count($alreadyDelivered) === count($orderDetails)) {
                $sample = $alreadyDelivered[0];
                return array(
                    'http' => 409,
                    'body' => array(
                        'success' => false,
                        'message' => translate('This product has already been delivered.'),
                        'status' => 'delivered',
                        'data' => $this->buildResponseData($order, $sample, $sample->delivery_status, false),
                    ),
                );
            }

            $sample = count($blocked) ? $blocked[0] : $orderDetails[0];
            $status = strtolower((string) $sample->delivery_status);
            $message = translate('This product is not eligible for delivery.');
            if ($status === 'cancelled') {
                $message = translate('This product has been cancelled and cannot be marked as delivered.');
            } elseif ($status === 'returned') {
                $message = translate('This product has been returned and cannot be marked as delivered.');
            } elseif ($status === 'refunded') {
                $message = translate('This product has been refunded and cannot be marked as delivered.');
            }

            return array(
                'http' => 422,
                'body' => array(
                    'success' => false,
                    'message' => $message,
                    'status' => $status,
                    'data' => $this->buildResponseData($order, $sample, $status, false),
                ),
            );
        }

        $now = now();
        $adminId = Auth::id();
        $updated = array();

        foreach ($eligible as $orderDetail) {
            $previousStatus = $orderDetail->delivery_status;
            $orderDetail->delivery_status = 'delivered';
            $orderDetail->delivered_at = $now;
            $orderDetail->delivered_by = $adminId;
            $orderDetail->save();
            $updated[] = array(
                'order_detail' => $orderDetail,
                'previous_status' => $previousStatus,
            );

            $this->processAffiliateForDeliveredItem($orderDetail);

            Log::info('product_qr_delivery', array(
                'order_detail_id' => $orderDetail->id,
                'order_id' => $order->id,
                'previous_status' => $previousStatus,
                'new_status' => 'delivered',
                'admin_user_id' => $adminId,
                'delivered_at' => $now->toDateTimeString(),
                'scan_timestamp' => $now->toDateTimeString(),
            ));
        }

        $this->syncParentOrderDeliveryStatus($order);

        $order->refresh();
        $firstUpdated = $updated[0]['order_detail']->fresh();
        $deliveredDetails = array();
        foreach ($updated as $row) {
            $deliveredDetails[] = $row['order_detail']->fresh();
        }

        $message = translate('Product marked as delivered successfully.');
        if (!$singleItem && count($updated) > 1) {
            $message = translate('Products marked as delivered successfully.');
        }

        return array(
            'http' => 200,
            'body' => array(
                'success' => true,
                'message' => $message,
                'status' => 'delivered',
                'data' => $this->buildResponseData(
                    $order,
                    $firstUpdated,
                    $updated[0]['previous_status'],
                    true,
                    count($updated)
                ),
            ),
            'delivered_order_details' => $deliveredDetails,
            'order' => $order,
        );
    }

    /**
     * @param \App\Models\Order $order
     * @return void
     */
    protected function syncParentOrderDeliveryStatus(Order $order)
    {
        $details = OrderDetail::where('order_id', $order->id)->get();
        if ($details->isEmpty()) {
            return;
        }

        $active = $details->filter(function ($detail) {
            return !in_array(strtolower((string) $detail->delivery_status), array('cancelled', 'returned', 'refunded'), true);
        });

        if ($active->isEmpty()) {
            return;
        }

        $allDelivered = $active->every(function ($detail) {
            return strtolower((string) $detail->delivery_status) === 'delivered';
        });

        if ($allDelivered) {
            $order->delivery_status = 'delivered';
            $order->delivery_viewed = '0';
            $order->save();
        }
    }

    /**
     * @param \App\Models\OrderDetail $orderDetail
     * @return void
     */
    protected function processAffiliateForDeliveredItem(OrderDetail $orderDetail)
    {
        if (!addon_is_activated('affiliate_system') || !$orderDetail->product_referral_code) {
            return;
        }

        if (!class_exists(AffiliateController::class)) {
            return;
        }

        $referredByUser = User::where('referral_code', $orderDetail->product_referral_code)->first();
        if ($referredByUser === null) {
            return;
        }

        $affiliateController = new AffiliateController;
        $affiliateController->processAffiliateStats($referredByUser->id, 0, 0, $orderDetail->quantity, 0);
    }

    /**
     * @param \App\Models\Order $order
     * @return void
     */
    protected function notifyDeliveryIfOrderDelivered(Order $order)
    {
        if (strtolower((string) $order->delivery_status) !== 'delivered') {
            return;
        }

        if (addon_is_activated('otp_system')) {
            $template = SmsTemplate::where('identifier', 'delivery_status_change')->first();
            if ($template && $template->status == 1) {
                try {
                    SmsUtility::delivery_status_change(json_decode($order->shipping_address)->phone, $order);
                } catch (Exception $e) {
                    Log::warning('QR delivery SMS failed: ' . $e->getMessage());
                }
            }
        }

        NotificationUtility::sendNotification($order, 'delivered');
        if (get_setting('google_firebase') == 1 && $order->user && $order->user->device_token != null) {
            $firebaseRequest = new Request();
            $firebaseRequest->merge(array(
                'device_token' => $order->user->device_token,
                'title' => 'Order updated !',
                'text' => " Your order {$order->code} has been delivered",
                'type' => 'order',
                'id' => $order->id,
                'user_id' => $order->user->id,
            ));
            NotificationUtility::sendFirebaseNotification($firebaseRequest);
        }
    }

    /**
     * @param \App\Models\Order $order
     * @param \App\Models\OrderDetail $orderDetail
     * @param string $previousStatus
     * @param bool $updated
     * @param int $updatedCount
     * @return array
     */
    protected function buildResponseData(Order $order, OrderDetail $orderDetail, $previousStatus, $updated, $updatedCount = 1)
    {
        $productName = translate('Product Unavailable');
        if ($orderDetail->product) {
            $productName = $orderDetail->product->getTranslation('name');
        }

        $deliveredAt = $orderDetail->delivered_at
            ? Carbon::parse($orderDetail->delivered_at)->timezone(config('app.timezone'))->format('d M Y, h:i A')
            : null;

        return array(
            'product' => $productName,
            'order' => '#' . $order->code,
            'current_status' => $this->statusLabel($previousStatus),
            'new_status' => $updated ? $this->statusLabel('delivered') : $this->statusLabel($orderDetail->delivery_status),
            'delivery_status' => $orderDetail->delivery_status,
            'delivered_at' => $deliveredAt,
            'updated_count' => $updatedCount,
        );
    }

    /**
     * @param string $status
     * @return string
     */
    protected function statusLabel($status)
    {
        return translate(ucwords(str_replace('_', ' ', (string) $status)));
    }
}
