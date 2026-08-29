@extends('frontend.layouts.user_panel')

@section('panel_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{ translate('booked_pooja') }}</h5>
        </div>
        <div class="card-body">
          <table class="table aiz-table mb-0">
              <thead>
                  <tr>
                      <th>{{ translate('Pooja')}}</th>
                      <th width="40%">{{ translate('Details') }}</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach ($orders as $key => $order_id)
                      @php
                          $order = \App\Models\OrderDetail::find($order_id->id);
                      @endphp
                      <tr>
                          <td><a href="{{ route('pooja', $order->product->slug) }}">{{ $order->product->getTranslation('name') }}</a></td>
                          <td>@if ($order_id->other != null) 
                            <b>{{ translate('name') }} : </b> {{ json_decode($order_id->other)->name }}
                            <br/>
                            <b>{{ translate('email') }} : </b>{{ json_decode($order_id->other)->email }} 
                            <br/>
                            <b>{{ translate('phone') }} : </b>{{ json_decode($order_id->other)->mobile }} 
                            <br/>
                            <b>{{ translate('subject') }} : </b>{{ json_decode($order_id->other)->subject }} 
                            @endif</td>
                      </tr>
                  @endforeach
              </tbody>
          </table>
            {{ $orders->links() }}
        </div>
    </div>
@endsection
