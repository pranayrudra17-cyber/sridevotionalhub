@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('Booked Astrology') }}</h5>
        </div>
        <div class="card-body">
          <table class="table aiz-table mb-0">
              <thead>
                  <tr>
                      <th>{{ translate('Astrology')}}</th>
                      <th width="40%">{{ translate('Date & Time')}}</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach ($orders as $key => $order_id)
                      @php
                          $order = \App\Models\OrderDetail::find($order_id->id);
                      @endphp
                      <tr>
                          <td><a href="{{ route('astrology', $order->product->slug) }}">{{ $order->product->getTranslation('name') }}</a></td>
                          <td>@if ($order_id->other != null) {{ json_decode($order_id->other)->preferred_date }} & {{ json_decode($order_id->other)->preferred_time }} @endif</td>
                      </tr>
                  @endforeach
              </tbody>
          </table>
            {{ $orders->links() }}
        </div>
    </div>
@endsection
