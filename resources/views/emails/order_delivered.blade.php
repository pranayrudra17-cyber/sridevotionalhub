<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ translate('Your Order Has Been Successfully Delivered') }}</title>
    <meta http-equiv="Content-Type" content="text/html;"/>
    <meta charset="UTF-8">
	<style media="all">
		@font-face {
            font-family: 'Roboto';
            src: url("{{ static_asset('fonts/Roboto-Regular.ttf') }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        *{
            margin: 0;
            padding: 0;
            line-height: 1.3;
            font-family: 'Roboto';
            color: #333542;
        }
		body{
			font-size: .875rem;
		}
		.gry-color *,
		.gry-color{
			color:#878f9c;
		}
		table{
			width: 100%;
		}
		table th{
			font-weight: normal;
		}
		.text-left{
			text-align:left;
		}
		.text-right{
			text-align:right;
		}
		.small{
			font-size: .85rem;
		}
	</style>
</head>
<body>
	<div>
		@php
			$logo = get_setting('header_logo');
			$shipping_address = json_decode($order->shipping_address);
			$customerName = $shipping_address && !empty($shipping_address->name)
				? $shipping_address->name
				: ($order->user ? $order->user->name : '');
			$productName = $orderDetail->product
				? $orderDetail->product->getTranslation('name')
				: translate('Product Unavailable');
			$deliveredAt = $orderDetail->delivered_at
				? \Carbon\Carbon::parse($orderDetail->delivered_at)->timezone(config('app.timezone'))->format('d M Y, h:i A')
				: translate('Just now');
		@endphp
		<div style="background: #eceff4;padding: 1.5rem;">
			<table>
				<tr>
					<td>
						@if($logo != null)
							<img loading="lazy"  src="{{ uploaded_asset($logo) }}" height="40" style="display:inline-block;">
						@else
							<img loading="lazy"  src="{{ static_asset('assets/img/logo.png') }}" height="40" style="display:inline-block;">
						@endif
					</td>
				</tr>
			</table>
			<table>
				<tr>
					<td style="font-size: 1.2rem;" class="strong">{{ get_setting('site_name') }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">{{ get_setting('contact_address') }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">{{  translate('Email') }}: {{ get_setting('contact_email') }}</td>
					<td class="text-right small"><span class="gry-color small">{{  translate('Order ID') }}:</span> <span class="strong">{{ $order->code }}</span></td>
				</tr>
				<tr>
					<td class="gry-color small">{{  translate('Phone') }}: {{ get_setting('contact_phone') }}</td>
					<td class="text-right small"><span class="gry-color small">{{  translate('Order Date') }}:</span> <span class=" strong">{{ date('d-m-Y', $order->date) }}</span></td>
				</tr>
			</table>
		</div>

		<div style="padding: 1.5rem;">
			<p style="margin-bottom: 0.75rem;">{{ translate('Hello') }}{{ $customerName ? ' ' . $customerName : '' }},</p>
			<p style="margin-bottom: 1rem;">{{ translate('Your order') }} #{{ $order->code }} {{ translate('has been successfully delivered.') }}</p>

			<p class="strong" style="margin-bottom: 0.5rem;">{{ translate('Order Details') }}</p>
			<table class="small">
				<tr>
					<td class="gry-color" style="padding: 0.35rem 0; width: 40%;">{{ translate('Product') }}</td>
					<td style="padding: 0.35rem 0;">{{ $productName }}@if($orderDetail->variation != null) ({{ $orderDetail->variation }}) @endif</td>
				</tr>
				<tr>
					<td class="gry-color" style="padding: 0.35rem 0;">{{ translate('Quantity') }}</td>
					<td style="padding: 0.35rem 0;">{{ $orderDetail->quantity }}</td>
				</tr>
				<tr>
					<td class="gry-color" style="padding: 0.35rem 0;">{{ translate('Delivery Status') }}</td>
					<td style="padding: 0.35rem 0;">{{ translate('Delivered') }}</td>
				</tr>
				<tr>
					<td class="gry-color" style="padding: 0.35rem 0;">{{ translate('Delivered At') }}</td>
					<td style="padding: 0.35rem 0;">{{ $deliveredAt }}</td>
				</tr>
			</table>

			<p style="margin-top: 1.25rem;">{{ translate('Thank you for shopping with us.') }}</p>
		</div>
	</div>
</body>
</html>
